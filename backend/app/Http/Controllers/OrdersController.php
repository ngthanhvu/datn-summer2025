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

class OrdersController extends Controller
{

    public function index()
    {
        if (Auth::user()->role !== 'admin') {
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
        try {
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
            ]);

            DB::beginTransaction();

            $order = Orders::create([
                'user_id' => Auth::user()->id,
                'address_id' => $validated['address_id'],
                'payment_method' => $validated['payment_method'],
                'coupon_id' => $validated['coupon_id'],
                'note' => $validated['note'] ?? '',
                'total_price' => $validated['total_price'],
                'shipping_fee' => $validated['shipping_fee'],
                'discount_price' => $validated['discount_price'],
                'final_price' => $validated['final_price'],
                'status' => 'pending',
                'payment_status' => $validated['payment_method'] === 'cod' ? 'pending' : 'paid',
                'tracking_code' => $this->generateUniqueTrackingCode(),
            ]);

            foreach ($validated['items'] as $item) {
                $variant = Variants::find($item['variant_id']);
                if (!$variant) {
                    throw new \Exception("Không tìm thấy biến thể: variant_id = {$item['variant_id']}");
                }

                Orders_detail::create([
                    'order_id' => $order->id,
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'], 
                    'original_price' => $variant->price,
                    'total_price' => $item['quantity'] * $item['price']
                ]);
            }

            // Tạo phiếu xuất kho cho đơn hàng
            $stockMovement = StockMovement::create([
                'type' => 'export',
                'user_id' => Auth::id(),
                'note' => 'Xuất kho khi đặt hàng #' . $order->id,
            ]);

            foreach ($validated['items'] as $item) {
                $variant = Variants::find($item['variant_id']);
                $finalPrice = ($variant && $variant->discount_price && $variant->discount_price > 0)
                    ? $variant->discount_price
                    : ($variant ? $variant->price : $item['price']);
                $stockMovementItem = StockMovementItem::create([
                    'stock_movement_id' => $stockMovement->id,
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $finalPrice,
                ]);

                // Trừ kho
                $inventory = Inventory::where('variant_id', $item['variant_id'])->first();
                if ($inventory) {
                    if ($inventory->quantity < $item['quantity']) {
                        throw new \Exception("Số lượng tồn kho không đủ cho biến thể: {$item['variant_id']}");
                    }
                    $inventory->quantity -= $item['quantity'];
                    $inventory->save();
                } else {
                    throw new \Exception("Không tìm thấy tồn kho cho biến thể: {$item['variant_id']}");
                }
            }

            if ($validated['payment_method'] === 'cod') {
                DB::table('carts')
                    ->where('user_id', Auth::user()->id)
                    ->delete();

                $user = Auth::user();
                if ($user && !empty($user->email)) {
                    Mail::to($user->email)->send(new PaymentConfirmation($order));
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Đặt hàng thành công',
                'order' => $order->load('orderDetails.variant.product')
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Có lỗi xảy ra khi đặt hàng',
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
            return response()->json(['message' => 'Order not found'], 404);
        }

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

    public function cancel(Request $request, $id)
    {
        try {
            $order = Orders::findOrFail($id);

            if ($order->user_id !== Auth::user()->id) {
                return response()->json([
                    'message' => 'Bạn không có quyền hủy đơn hàng này'
                ], 403);
            }

            $request->validate([
                'cancel_reason' => 'required|string|max:255'
            ]);

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

            if (in_array($order->payment_method, ['momo', 'vnpay', 'paypal'])) {
                $order->payment_status = 'refunded';
            } else {
                $order->payment_status = 'canceled';
            }

            $order->status = 'cancelled';
            $order->cancel_reason = $request->cancel_reason; 
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
        if ($order->status !== 'cancelled' && !empty($order->cancel_reason)) {
            $order->cancel_reason = null;
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

        $order->return_status = 'requested';
        $order->save();

        return response()->json(['message' => 'Yêu cầu hoàn hàng đã được gửi thành công']);
    }

    public function approveReturn($id)
    {
        $order = Orders::findOrFail($id);
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Bạn không có quyền duyệt hoàn hàng'], 403);
        }
        if ($order->return_status !== 'requested') {
            return response()->json(['message' => 'Đơn hàng chưa có yêu cầu hoàn hàng'], 400);
        }
        $order->return_status = 'approved';
        $order->save();

        // Trước khi gửi mail, nạp quan hệ và xử lý đường dẫn ảnh
        $order->load('orderDetails.variant.product.mainImage');
        foreach ($order->orderDetails as $orderDetail) {
            if ($orderDetail->variant && $orderDetail->variant->product && $orderDetail->variant->product->mainImage) {
                $imagePath = $orderDetail->variant->product->mainImage->image_path;
                $orderDetail->variant->product->mainImage->image_path = url('storage/' . ltrim($imagePath, '/'));
            }
        }

        if ($order->user && $order->user->email) {
            Mail::to($order->user->email)->send(new \App\Mail\ReturnApproved($order));
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

        // Trước khi gửi mail, nạp quan hệ và xử lý đường dẫn ảnh
        $order->load('orderDetails.variant.product.mainImage');
        foreach ($order->orderDetails as $orderDetail) {
            if ($orderDetail->variant && $orderDetail->variant->product && $orderDetail->variant->product->mainImage) {
                $imagePath = $orderDetail->variant->product->mainImage->image_path;
                $orderDetail->variant->product->mainImage->image_path = url('storage/' . ltrim($imagePath, '/'));
            }
        }

        if ($order->user && $order->user->email) {
            Mail::to($order->user->email)->send(new \App\Mail\ReturnRejected($order, $request->reject_reason));
        }

        return response()->json(['message' => 'Đã từ chối hoàn hàng']);
    }
}
