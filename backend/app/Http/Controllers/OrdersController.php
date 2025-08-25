<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\Orders_detail;
use App\Mail\PaymentConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\Inventory;
use App\Models\Variants;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\User;
use App\Mail\ReturnRejected;
use App\Models\Products;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use Illuminate\Support\Facades\Cache;
use App\Notifications\NewOrderNotification;
use App\Models\Coupons;

class OrdersController extends Controller
{
    private static function applyFlashSaleDeduction(int $variantId, int $unitPrice, int $quantity): void
    {
        try {
            $variant = Variants::find($variantId);
            if (!$variant) return;
            $productId = $variant->product_id;
            if (!$productId) return;

            $activeFlashSale = FlashSale::whereHas('products', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            })
                ->where('active', true)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->first();
            if (!$activeFlashSale) return;

            $fsp = FlashSaleProduct::where('flash_sale_id', $activeFlashSale->id)
                ->where('product_id', $productId)
                ->first();
            if (!$fsp) return;

            $expected = null;
            if ($variant && $variant->product && $variant->product->price) {
                $percent = round(100 - ((int)$fsp->flash_price / $variant->product->price) * 100);
                if ($percent > 0) {
                    $expected = (int) round($variant->price * (1 - $percent / 100));
                }
            }
            if (!is_null($expected) && $unitPrice == $expected) {
                $deduct = min((int)$quantity, max(0, (int)$fsp->quantity));
                if ($deduct > 0) {
                    $fsp->quantity = max(0, (int)$fsp->quantity - $deduct);
                }
                $fsp->sold = (int)$fsp->sold + (int)$quantity;
                $fsp->save();
                Cache::tags(['flash_sales'])->flush();
            }
        } catch (\Throwable $e) {
        }
    }
    public function index()
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'master_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $query = Orders::with([
            'user:id,username,email,phone,avatar',
            'address:id,full_name,phone,province,district,ward,street',
            'orderDetails:id,order_id,variant_id,quantity,price,total_price',
            'orderDetails.variant:id,color,size,price,sku,product_id',
            'orderDetails.variant.product:id,name,price,description,slug',
            'orderDetails.variant.product.mainImage:id,image_path,is_main,product_id'
        ])
            ->select([
                'id',
                'user_id',
                'address_id',
                'status',
                'payment_method',
                'payment_status',
                'total_price',
                'discount_price',
                'final_price',
                'coupon_id',
                'note',
                'created_at',
                'updated_at',
                'tracking_code',
                'cancel_reason',
                'return_status',
                'return_reason',
                'reject_reason'
            ]);
        $orders = $query->orderBy('created_at', 'desc')->paginate(10);
        $orders->getCollection()->transform(function ($order) {
            if ($order->orderDetails) {
                foreach ($order->orderDetails as $orderDetail) {
                    if ($orderDetail->variant && $orderDetail->variant->product && $orderDetail->variant->product->mainImage) {
                        $imagePath = $orderDetail->variant->product->mainImage->image_path;
                        $imagePath = preg_replace('/^https?:\/\/[^\/]+\/storage\/?/', '', $imagePath);
                        $imagePath = ltrim($imagePath, '/');
                        $orderDetail->variant->product->mainImage->image_path = url('storage/' . $imagePath);
                    }
                }
            }
            return $order;
        });
        return response()->json($orders);
    }

    public function userOrders()
    {
        $query = Orders::with([
            'user:id,username,email,phone,avatar',
            'address:id,full_name,phone,province,district,ward,street',
            'orderDetails:id,order_id,variant_id,quantity,price,total_price',
            'orderDetails.variant:id,color,size,price,sku,product_id',
            'orderDetails.variant.product:id,name,price,description,slug',
            'orderDetails.variant.product.mainImage:id,image_path,is_main,product_id'
        ])
            ->select([
                'id',
                'user_id',
                'address_id',
                'status',
                'payment_method',
                'payment_status',
                'total_price',
                'discount_price',
                'final_price',
                'coupon_id',
                'note',
                'created_at',
                'updated_at',
                'tracking_code',
                'cancel_reason',
                'return_status',
                'return_reason',
                'reject_reason'
            ])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $query->getCollection()->transform(function ($order) {
            if ($order->orderDetails) {
                foreach ($order->orderDetails as $orderDetail) {
                    if ($orderDetail->variant && $orderDetail->variant->product && $orderDetail->variant->product->mainImage) {
                        $imagePath = $orderDetail->variant->product->mainImage->image_path;
                        $imagePath = preg_replace('/^https?:\/\/[^\/]+\/storage\/?/', '', $imagePath);
                        $imagePath = ltrim($imagePath, '/');
                        $orderDetail->variant->product->mainImage->image_path = url('storage/' . $imagePath);
                    }
                }
            }
            return $order;
        });
        return response()->json($query);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|in:cod,vnpay,momo,paypal',
            'coupon_id' => 'nullable|exists:coupons,id',
            'items' => 'required|array',
            'items.*.variant_id' => 'required|exists:variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|integer|min:0',
            'note' => 'nullable|string',
            'total_price' => 'required|integer|min:0',
            'shipping_fee' => 'required|integer|min:0',
            'discount_price' => 'required|integer|min:0',
            'final_price' => 'required|integer|min:0',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $allowsZeroShipping = false;
        if (!empty($validated['coupon_id'])) {
            $coupon = Coupons::find($validated['coupon_id']);

            if ($coupon && $coupon->type === 'shipping') {
                $allowsZeroShipping = true;
            }
        }

        // Allow zero shipping fee only when a valid free shipping coupon is applied
        if (($validated['shipping_fee'] ?? 0) <= 0 && !$allowsZeroShipping) {
            return response()->json([
                'message' => 'Vui lòng tính phí vận chuyển trước khi thanh toán',
            ], 422);
        }

        if ($validated['payment_method'] === 'cod') {
            DB::beginTransaction();
            try {
                $order = self::createOrderFromData($validated, Auth::user()->id, 'pending'); // COD: pending
                $stockMovement = StockMovement::create([
                    'type' => 'export',
                    'user_id' => Auth::id(),
                    'note' => 'Xuất kho khi đặt hàng #' . $order->id,
                ]);
                $hasFlashSale = false;
                foreach ($validated['items'] as $item) {
                    $variant = Variants::with('product')->find($item['variant_id']);
                    $finalPrice = ($variant && $variant->discount_price && $variant->discount_price > 0)
                        ? $variant->discount_price
                        : ($variant ? $variant->price : $item['price']);

                    if ($variant && $variant->product) {
                        $activeFlashSale = FlashSale::whereHas('products', function ($q) use ($variant) {
                            $q->where('product_id', $variant->product_id);
                        })
                            ->where('active', true)
                            ->where('start_time', '<=', now())
                            ->where('end_time', '>=', now())
                            ->first();
                        if ($activeFlashSale) {
                            $fsp = FlashSaleProduct::where('flash_sale_id', $activeFlashSale->id)
                                ->where('product_id', $variant->product_id)
                                ->first();
                            if ($fsp && $variant->product->price) {
                                $percent = round(100 - ((int)$fsp->flash_price / $variant->product->price) * 100);
                                if ($percent > 0) {
                                    $expected = (int) round($variant->price * (1 - $percent / 100));
                                    if ($item['price'] == $expected) {
                                        $finalPrice = $expected;
                                        $hasFlashSale = true;
                                    }
                                }
                            }
                        }
                    }
                    StockMovementItem::create([
                        'stock_movement_id' => $stockMovement->id,
                        'variant_id' => $item['variant_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $finalPrice,
                    ]);
                    $inventory = Inventory::where('variant_id', $item['variant_id'])->first();
                    if ($inventory) {
                        if ($inventory->quantity < $item['quantity']) {
                            throw new \Exception("Số lượng tồn kho không đủ cho biến thể: {$item['variant_id']}");
                        }
                        $inventory->quantity -= $item['quantity'];
                        $inventory->save();
                        if ($variant && $variant->product_id) {
                            Products::where('id', $variant->product_id)->increment('sold_count', $item['quantity']);
                        }
                    } else {
                        throw new \Exception("Không tìm thấy tồn kho cho biến thể: {$item['variant_id']}");
                    }

                    self::applyFlashSaleDeduction($item['variant_id'], (int)$item['price'], (int)$item['quantity']);
                }
                if ($hasFlashSale) {
                    $stockMovement->note = 'Xuất kho khi đặt hàng #' . $order->id . ' (sản phẩm sale)';
                    $stockMovement->save();
                }

                // Xóa giỏ hàng sau khi đặt hàng thành công
                if (Auth::check()) {
                    // Xóa giỏ hàng của user đã đăng nhập
                    DB::table('carts')
                        ->where('user_id', Auth::user()->id)
                        ->delete();
                } else {
                    // Xóa giỏ hàng của guest user (session)
                    $sessionId = $request->header('X-Session-Id');
                    if ($sessionId) {
                        DB::table('carts')
                            ->where('session_id', $sessionId)
                            ->delete();
                    }
                }
                $user = Auth::user();
                if ($user && !empty($user->email)) {
                    Mail::to($user->email)->queue(new PaymentConfirmation($order));
                }
                $admin = \App\Models\User::where('role', 'admin')->first();
                if ($admin) {
                    $admin->notify(new NewOrderNotification($order));
                }
                DB::commit();
                return response()->json([
                    'message' => 'Đặt hàng thành công',
                    'order' => $order->load('orderDetails.variant.product')
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Có lỗi xảy ra khi đặt hàng',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            $validated['user_id'] = Auth::user()->id;
            return response()->json([
                'message' => 'Redirect to payment gateway',
                'payment_method' => $validated['payment_method'],
                'data' => $validated
            ]);
        }
    }

    /**
     * Xóa giỏ hàng sau khi thanh toán thành công
     */
    public function clearCartAfterPayment(Request $request)
    {
        try {
            if (Auth::check()) {
                // Xóa giỏ hàng của user đã đăng nhập
                DB::table('carts')
                    ->where('user_id', Auth::user()->id)
                    ->delete();
            } else {
                // Xóa giỏ hàng của guest user (session)
                $sessionId = $request->header('X-Session-Id');
                if ($sessionId) {
                    DB::table('carts')
                        ->where('session_id', $sessionId)
                        ->delete();
                }
            }

            return response()->json([
                'message' => 'Giỏ hàng đã được xóa sau khi thanh toán thành công',
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa giỏ hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $order = Orders::with(['user', 'address', 'orderDetails.variant.product.mainImage'])->findOrFail($id);

        if ($order->orderDetails) {
            foreach ($order->orderDetails as $orderDetail) {
                if ($orderDetail->variant && $orderDetail->variant->product && $orderDetail->variant->product->mainImage) {
                    $orderDetail->variant->product->mainImage->image_path = url('storage/' . $orderDetail->variant->product->mainImage->image_path);
                }
            }
        }

        $order->total_price = (int) $order->total_price;
        $order->shipping_fee = (int) $order->shipping_fee;
        $order->discount_price = (int) $order->discount_price;
        $order->final_price = (int) $order->final_price;

        return response()->json($order);
    }

    public function getOrderByTrackingCode($tracking_code)
    {
        $order = Orders::with(['user', 'address', 'orderDetails.variant.product.mainImage'])
            ->where('tracking_code', $tracking_code)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng'
            ], 404);
        }

        if ($order->orderDetails) {
            foreach ($order->orderDetails as $orderDetail) {
                if ($orderDetail->variant && $orderDetail->variant->product && $orderDetail->variant->product->mainImage) {
                    $imagePath = $orderDetail->variant->product->mainImage->image_path;
                    // Đảm bảo đường dẫn bắt đầu với storage/
                    if (!str_starts_with($imagePath, 'storage/')) {
                        $imagePath = 'storage/' . ltrim($imagePath, '/');
                    }
                    $orderDetail->variant->product->mainImage->image_url = url($imagePath);
                }
            }
        }

        $order->total_price = (int) $order->total_price;
        $order->shipping_fee = (int) $order->shipping_fee;
        $order->discount_price = (int) $order->discount_price;
        $order->final_price = (int) $order->final_price;

        return response()->json([
            'success' => true,
            'order' => $order
        ]);
    }

    public function cancel(Request $request, $id)
    {
        try {
            $order = Orders::findOrFail($id);

            if ($order->user_id !== Auth::user()->id) {
                return response()->json([
                    'message' => 'Bạn không có quyền hủy đơn hàng này'
                ], 403);
            }

            if ($order->status === 'cancelled') {
                return response()->json([
                    'message' => 'Đơn hàng đã bị hủy trước đó'
                ], 400);
            }

            if ($order->status === 'completed') {
                return response()->json([
                    'message' => 'Không thể hủy đơn hàng đã hoàn thành'
                ], 400);
            }

            if (!in_array($order->status, ['pending', 'processing'])) {
                return response()->json([
                    'message' => 'Chỉ có thể hủy đơn hàng ở trạng thái chờ xác nhận hoặc đang xử lý'
                ], 400);
            }

            $createdAt = $order->created_at;
            $now = now();
            if ($now->diffInHours($createdAt) > 24) {
                return response()->json([
                    'message' => 'Chỉ có thể hủy đơn hàng trong vòng 24 giờ kể từ khi đặt hàng'
                ], 400);
            }

            $cancelReason = $request->input('cancel_reason');
            if ($cancelReason) {
                $order->cancel_reason = $cancelReason;
            }

            if (in_array($order->payment_method, ['momo', 'vnpay', 'paypal'])) {
                $order->payment_status = 'refunded';
            } else {
                $order->payment_status = 'canceled';
            }

            $order->status = 'cancelled';
            $order->save();

            return response()->json([
                'message' => 'Hủy đơn hàng thành công',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra khi hủy đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipping,completed,cancelled',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded,canceled'
        ]);

        $order = Orders::findOrFail($id);
        $order->status = $request->status;
        if ($request->has('payment_status')) {
            $order->payment_status = $request->payment_status;
        }
        $order->save();

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công',
            'order' => $order
        ]);
    }

    private function generateUniqueTrackingCode()
    {
        $trackingCode = null;
        do {
            $randomNumber = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $trackingCode = 'DG' . $randomNumber;
        } while (Orders::where('tracking_code', $trackingCode)->exists());

        return $trackingCode;
    }

    public static function createOrderFromData($data, $userId, $paymentStatus = 'pending')
    {
        $order = Orders::create([
            'user_id' => $userId,
            'address_id' => $data['address_id'],
            'payment_method' => $data['payment_method'],
            'coupon_id' => $data['coupon_id'] ?? null,
            'note' => $data['note'] ?? '',
            'total_price' => $data['total_price'],
            'shipping_fee' => $data['shipping_fee'],
            'discount_price' => $data['discount_price'],
            'final_price' => $data['final_price'],
            'status' => 'pending',
            'payment_status' => $paymentStatus,
            'tracking_code' => (new self)->generateUniqueTrackingCode(),
        ]);
        foreach ($data['items'] as $item) {
            $variant = Variants::find($item['variant_id']);
            Orders_detail::create([
                'order_id' => $order->id,
                'variant_id' => $item['variant_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'original_price' => $variant ? $variant->price : $item['price'],
                'total_price' => $item['quantity'] * $item['price']
            ]);
        }
        return $order;
    }

    public static function createOrderFromDataWithProcessing($data, $userId)
    {
        DB::beginTransaction();
        try {
            $paymentStatus = 'pending';
            if (in_array($data['payment_method'], ['vnpay', 'momo', 'paypal'])) {
                $paymentStatus = 'paid';
            }

            $order = Orders::create([
                'user_id' => $userId,
                'address_id' => $data['address_id'],
                'payment_method' => $data['payment_method'],
                'coupon_id' => $data['coupon_id'] ?? null,
                'note' => $data['note'] ?? '',
                'total_price' => $data['total_price'],
                'shipping_fee' => $data['shipping_fee'],
                'discount_price' => $data['discount_price'],
                'final_price' => $data['final_price'],
                'status' => 'pending',
                'payment_status' => $paymentStatus,
                'tracking_code' => (new self)->generateUniqueTrackingCode(),
            ]);

            foreach ($data['items'] as $item) {
                $variant = Variants::find($item['variant_id']);
                Orders_detail::create([
                    'order_id' => $order->id,
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'original_price' => $variant ? $variant->price : $item['price'],
                    'total_price' => $item['quantity'] * $item['price']
                ]);
            }

            $stockMovement = StockMovement::create([
                'type' => 'export',
                'user_id' => $userId,
                'note' => 'Xuất kho khi đặt hàng #' . $order->id,
            ]);
            $hasFlashSale = false;

            foreach ($data['items'] as $item) {
                $variant = Variants::find($item['variant_id']);
                $finalPrice = ($variant && $variant->discount_price && $variant->discount_price > 0)
                    ? $variant->discount_price
                    : ($variant ? $variant->price : $item['price']);

                if ($variant) {
                    $activeFlashSale = FlashSale::whereHas('products', function ($q) use ($variant) {
                        $q->where('product_id', $variant->product_id);
                    })
                        ->where('active', true)
                        ->where('start_time', '<=', now())
                        ->where('end_time', '>=', now())
                        ->first();
                    if ($activeFlashSale) {
                        $fsp = FlashSaleProduct::where('flash_sale_id', $activeFlashSale->id)
                            ->where('product_id', $variant->product_id)
                            ->first();
                        if ($fsp && $variant->product && $variant->product->price) {
                            $percent = round(100 - ((int)$fsp->flash_price / $variant->product->price) * 100);
                            if ($percent > 0) {
                                $expected = (int) round($variant->price * (1 - $percent / 100));
                                if ($item['price'] == $expected) {
                                    $finalPrice = $expected;
                                    $hasFlashSale = true;
                                }
                            }
                        }
                    }
                }

                StockMovementItem::create([
                    'stock_movement_id' => $stockMovement->id,
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $finalPrice,
                ]);

                $inventory = Inventory::where('variant_id', $item['variant_id'])->first();
                if ($inventory) {
                    if ($inventory->quantity < $item['quantity']) {
                        throw new \Exception("Số lượng tồn kho không đủ cho biến thể: {$item['variant_id']}");
                    }
                    $inventory->quantity -= $item['quantity'];
                    $inventory->save();
                    if ($variant && $variant->product_id) {
                        Products::where('id', $variant->product_id)->increment('sold_count', $item['quantity']);
                    }
                } else {
                    throw new \Exception("Không tìm thấy tồn kho cho biến thể: {$item['variant_id']}");
                }
                self::applyFlashSaleDeduction($item['variant_id'], (int)$item['price'], (int)$item['quantity']);
            }

            if ($hasFlashSale) {
                $stockMovement->note = 'Xuất kho khi đặt hàng #' . $order->id . ' (sản phẩm sale)';
                $stockMovement->save();
            }

            DB::table('carts')->where('user_id', $userId)->delete();

            $user = User::find($userId);
            if ($user && !empty($user->email)) {
                Mail::to($user->email)->queue(new PaymentConfirmation($order));
            }

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function reorder($id)
    {
        try {
            $originalOrder = Orders::with('orderDetails')->findOrFail($id);

            if ($originalOrder->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Bạn không có quyền mua lại đơn hàng này'
                ], 403);
            }

            foreach ($originalOrder->orderDetails as $detail) {
                $existing = DB::table('carts')->where([
                    'user_id' => Auth::id(),
                    'variant_id' => $detail->variant_id
                ])->first();

                if ($existing) {
                    DB::table('carts')->where('id', $existing->id)->update([
                        'quantity' => $existing->quantity + $detail->quantity,
                        'price' => $detail->price,
                        'updated_at' => now()
                    ]);
                } else {
                    DB::table('carts')->insert([
                        'user_id' => Auth::id(),
                        'variant_id' => $detail->variant_id,
                        'quantity' => $detail->quantity,
                        'price' => $detail->price,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            return response()->json([
                'message' => 'Các sản phẩm trong đơn hàng đã được thêm vào giỏ hàng'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra khi mua lại đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function requestReturn(Request $request, $id)
    {
        $order = Orders::findOrFail($id);

        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Bạn không có quyền yêu cầu hoàn hàng cho đơn này'], 403);
        }

        if (!in_array($order->status, ['cancelled', 'completed'])) {
            return response()->json(['message' => 'Chỉ có thể hoàn hàng cho đơn đã hủy hoặc đã hoàn thành'], 400);
        }

        if ($order->return_status === 'requested') {
            return response()->json(['message' => 'Bạn đã gửi yêu cầu hoàn hàng cho đơn này rồi'], 400);
        }

        $completedOrCancelledAt = $order->updated_at;
        $now = now();
        if ($now->diffInDays($completedOrCancelledAt) > 3) {
            return response()->json(['message' => 'Chỉ có thể hoàn hàng trong vòng 3 ngày kể từ khi đơn hoàn thành hoặc bị hủy'], 400);
        }

        $returnReason = $request->input('return_reason');
        if (!$returnReason) {
            return response()->json(['message' => 'Vui lòng cung cấp lý do hoàn hàng'], 400);
        }

        $order->return_status = 'requested';
        $order->return_reason = $returnReason;
        $order->save();

        return response()->json(['message' => 'Yêu cầu hoàn hàng đã được gửi thành công']);
    }

    public function approveReturn($id)
    {
        $order = Orders::findOrFail($id);
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'master_admin') {
            return response()->json(['message' => 'Bạn không có quyền duyệt hoàn hàng'], 403);
        }
        if ($order->return_status !== 'requested') {
            return response()->json(['message' => 'Đơn hàng chưa có yêu cầu hoàn hàng'], 400);
        }

        $order->return_status = 'approved';

        $order->status = 'refunded';

        if ($order->payment_status === 'paid') {
            $order->payment_status = 'refunded';
        }

        $order->save();

        $order->load('orderDetails.variant.product.mainImage');
        foreach ($order->orderDetails as $orderDetail) {
            if ($orderDetail->variant && $orderDetail->variant->product && $orderDetail->variant->product->mainImage) {
                $imagePath = $orderDetail->variant->product->mainImage->image_path;
                $orderDetail->variant->product->mainImage->image_path = url('storage/' . ltrim($imagePath, '/'));
            }
        }

        if ($order->user && $order->user->email) {
            Mail::to($order->user->email)->queue(new \App\Mail\ReturnApproved($order));
        }

        return response()->json(['message' => 'Đã duyệt hoàn hàng']);
    }

    public function rejectReturn(Request $request, $id)
    {
        $order = Orders::findOrFail($id);
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Bạn không có quyền từ chối hoàn hàng'], 403);
        }
        if ($order->return_status !== 'requested') {
            return response()->json(['message' => 'Đơn hàng chưa có yêu cầu hoàn hàng'], 400);
        }
        $request->validate([
            'reject_reason' => 'required|string|max:255'
        ]);
        $order->return_status = 'rejected';
        $order->reject_reason = $request->reject_reason;
        $order->save();

        $order->load('orderDetails.variant.product.mainImage');
        foreach ($order->orderDetails as $orderDetail) {
            if ($orderDetail->variant && $orderDetail->variant->product && $orderDetail->variant->product->mainImage) {
                $imagePath = $orderDetail->variant->product->mainImage->image_path;
                $orderDetail->variant->product->mainImage->image_path = url('storage/' . ltrim($imagePath, '/'));
            }
        }

        if ($order->user && $order->user->email) {
            // Mail::to($order->user->email)->send(new \App\Mail\ReturnRejected($order, $request->reject_reason));
            Mail::to($order->user->email)->queue(new ReturnRejected($order, $request->reject_reason));
        }

        return response()->json(['message' => 'Đã từ chối hoàn hàng']);
    }
}
