<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\Orders_detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{

    public function index()
    {
        $orders = Orders::with(['user', 'address', 'orderDetails.variant'])->paginate(10);
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|string',
            'coupon_id' => 'nullable|exists:coupons,id',
            'items' => 'required|array',
            'items.*.variant_id' => 'required|exists:variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|integer|min:0',
            'note' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $userId = Auth::user()->id;

            $total = collect($validated['items'])->sum(fn($i) => $i['quantity'] * $i['price']);

            $order = Orders::create([
                'user_id' => $userId,
                'address_id' => $validated['address_id'],
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'unpaid',
                'total_price' => $total,
                'discount_price' => 0,
                'final_price' => $total,
                'coupon_id' => $validated['coupon_id'] ?? null,
                'status' => 'pending',
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                Orders_detail::create([
                    'order_id' => $order->id,
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total_price' => $item['quantity'] * $item['price'],
                ]);
            }

            return response()->json($order->load('orderDetails'), 201);
        });
    }

    public function show(Orders $orders)
    {
        $order = $orders->load(['user', 'address', 'orderDetails.variant']);
        return response()->json($order);
    }
}
