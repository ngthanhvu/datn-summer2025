<?php

namespace App\Http\Controllers;

use App\Models\Coupons;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CouponsController extends Controller
{
    public function index()
    {
        $cacheKey = 'coupons_index';
        $cacheTTL = 3600; // 1 giờ

        $coupons = Cache::tags(['coupons'])->remember($cacheKey, $cacheTTL, function () {
            return Coupons::orderBy('created_at', 'desc')
                ->get()
                ->makeHidden(['created_at', 'updated_at']);
        });

        return response()->json($coupons);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'code' => 'required|unique:coupons',
            'type' => 'required|in:percent,fixed,shipping',
            'value' => 'required|numeric|min:0',
            'description' => 'nullable|string',
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
        Cache::tags(['coupons'])->flush();
        return response()->json(['coupon' => $coupon, 'message' => 'Mã giảm giá đã được tạo thành công'], 201);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupons::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'code' => 'required|unique:coupons,code,' . $id,
            'type' => 'required|in:percent,fixed,shipping',
            'value' => 'required|numeric|min:0',
            'description' => 'nullable|string',
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
        Cache::tags(['coupons'])->flush();
        return response()->json(['coupon' => $coupon, 'message' => 'Mã giảm giá đã được cập nhật thành công']);
    }

    public function show($id)
    {
        $cacheKey = "coupon_show_{$id}";
        $cacheTTL = 3600;

        $coupon = Cache::tags(['coupons'])->remember($cacheKey, $cacheTTL, function () use ($id) {
            return Coupons::find($id);
        });

        if (!$coupon) {
            return response()->json(['error' => 'Coupon not found'], 404);
        }

        return response()->json($coupon);
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

        if ($coupon->type === 'shipping') {
            $discount = 0;
        } else {
            $discount = $coupon->type === 'percent'
                ? min(($request->total_amount * $coupon->value / 100), $coupon->max_discount_value ?? PHP_FLOAT_MAX)
                : $coupon->value;
        }
        Cache::tags(['coupons'])->flush();

        return response()->json([
            'discount' => $discount,
            'message' => 'Mã giảm giá hợp lệ',
            'free_shipping' => $coupon->type === 'shipping',
            'coupon' => $coupon
        ]);
    }

    public function destroy($id)
    {
        $coupon = Coupons::findOrFail($id);
        $coupon->delete();
        Cache::tags(['coupons'])->flush();
        return response()->json(['message' => 'Mã giảm giá đã được xóa thành công']);
    }

    public function claim($couponId)
    {
        $user = Auth::user();
        $coupon = Coupons::findOrFail($couponId);

        // Kiểm tra nếu đã claim rồi thì không cho claim lại
        if ($user->coupons()->where('coupon_id', $couponId)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn đã lưu mã này rồi.'
            ], 409);
        }

        // Lưu coupon
        $user->coupons()->attach($couponId, [
            'claimed_at' => now(),
            'status' => 'claimed'
        ]);
        Cache::tags(['coupons'])->flush();

        return response()->json([
            'status' => true,
            'message' => 'Đã lưu mã thành công.'
        ]);
    }

    /**
     * User sử dụng coupon đã lưu (Use Coupon)
     */
    public function use($couponId)
    {
        $user = Auth::user();

        $coupon = $user->coupons()->where('coupon_id', $couponId)->first();

        if (!$coupon) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn chưa lưu mã này.'
            ], 404);
        }

        if ($coupon->pivot->status === 'used') {
            return response()->json([
                'status' => false,
                'message' => 'Mã này đã được sử dụng.'
            ], 409);
        }

        // Kiểm tra hạn sử dụng và trạng thái coupon
        if (!$coupon->is_active || $coupon->start_date > now() || $coupon->end_date < now()) {
            return response()->json([
                'status' => false,
                'message' => 'Mã này không còn hiệu lực.'
            ], 400);
        }

        // Đánh dấu đã sử dụng
        $user->coupons()->updateExistingPivot($couponId, [
            'used_at' => now(),
            'status' => 'used'
        ]);
        Cache::tags(['coupons'])->flush();

        return response()->json([
            'status' => true,
            'message' => 'Áp dụng mã thành công.',
            'coupon' => [
                'code' => $coupon->code,
                'value' => $coupon->value,
                'type' => $coupon->type
            ]
        ]);
    }

    /**
     * Lấy danh sách coupon đã lưu của user
     */
    public function myCoupons()
    {
        $user = Auth::user();

        $coupons = $user->coupons()->withPivot('status', 'claimed_at', 'used_at')->get();
        Cache::tags(['coupons'])->flush();

        return response()->json([
            'status' => true,
            'coupons' => $coupons
        ]);
    }
}
