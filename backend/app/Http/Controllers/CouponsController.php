<?php

namespace App\Http\Controllers;

use App\Models\Coupons;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponsController extends Controller
{
    public function index()
    {
        $coupons = Coupons::orderBy('created_at', 'desc')->get();
        return response()->json(['coupons' => $coupons]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'code' => 'required|unique:coupons',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount_value' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $coupon = Coupons::create($request->all());
        return response()->json(['coupon' => $coupon, 'message' => 'Mã giảm giá đã được tạo thành công'], 201);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupons::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'code' => 'required|unique:coupons,code,' . $id,
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount_value' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $coupon->update($request->all());
        return response()->json(['coupon' => $coupon, 'message' => 'Mã giảm giá đã được cập nhật thành công']);
    }

    public function validate_coupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|exists:coupons,code',
            'total_amount' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $coupon = Coupons::where('code', $request->code)->first();

        if (!$coupon->isValid()) {
            return response()->json(['error' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn'], 400);
        }

        if ($request->total_amount < $coupon->min_order_value) {
            return response()->json([
                'error' => 'Giá trị đơn hàng phải từ ' . $coupon->min_order_value . ' trở lên'
            ], 400);
        }

        $discount = $coupon->type === 'percent'
            ? min(($request->total_amount * $coupon->value / 100), $coupon->max_discount_value ?? PHP_FLOAT_MAX)
            : $coupon->value;

        return response()->json([
            'discount' => $discount,
            'message' => 'Mã giảm giá hợp lệ'
        ]);
    }
}
