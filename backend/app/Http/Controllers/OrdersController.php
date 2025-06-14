<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\Orders_detail;
use App\Mail\PaymentConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class OrdersController extends Controller
{

    public function index()
    {
        $orders = Orders::with(['user', 'address', 'orderDetails.variant'])->paginate(10);
        return response()->json($orders);
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

            // Tạo đơn hàng
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
                'payment_status' => $validated['payment_method'] === 'cod' ? 'pending' : 'paid'
            ]);

            // Tạo chi tiết đơn hàng
            foreach ($validated['items'] as $item) {
                Orders_detail::create([
                    'order_id' => $order->id,
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total_price' => $item['quantity'] * $item['price']
                ]);

                // Kiểm tra và cập nhật số lượng trong kho
                $inventory = DB::table('inventories')
                    ->where('variant_id', $item['variant_id'])
                    ->first();

                if (!$inventory) {
                    throw new \Exception("Không tìm thấy sản phẩm trong kho: variant_id = {$item['variant_id']}");
                }

                if ($inventory->quantity < $item['quantity']) {
                    throw new \Exception("Số lượng sản phẩm trong kho không đủ: variant_id = {$item['variant_id']}");
                }

                DB::table('inventories')
                    ->where('variant_id', $item['variant_id'])
                    ->update([
                        'quantity' => DB::raw('quantity - ' . $item['quantity'])
                    ]);
            }

            // Xóa giỏ hàng sau khi tạo đơn hàng thành công
            DB::table('carts')
                ->where('user_id', Auth::user()->id)
                ->delete();

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
            \Log::error('Lỗi khi tạo đơn hàng: ' . $e->getMessage(), [
                'user_id' => Auth::user()->id,
                'request_data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Có lỗi xảy ra khi đặt hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Orders $orders)
    {
        $order = $orders->load(['user', 'address', 'orderDetails.variant.product.main_image']);

        // Ensure image_path is a full URL
        if ($order->orderDetails) {
            foreach ($order->orderDetails as $orderDetail) {
                if ($orderDetail->variant && $orderDetail->variant->product && $orderDetail->variant->product->main_image) {
                    $orderDetail->variant->product->main_image->image_path = url('storage/' . $orderDetail->variant->product->main_image->image_path);
                }
            }
        }

        // Ensure price fields are numeric
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

            // Kiểm tra quyền hủy đơn hàng
            if ($order->user_id !== Auth::user()->id) {
                return response()->json([
                    'message' => 'Bạn không có quyền hủy đơn hàng này'
                ], 403);
            }

            // Kiểm tra trạng thái đơn hàng
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

            // Cập nhật trạng thái đơn hàng và thanh toán
            $order->status = 'cancelled';
            $order->payment_status = 'canceled';
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
}
