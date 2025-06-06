<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Address;

class AddressController extends Controller
{
    // Lấy danh sách tỉnh/thành phố từ API thật
    public function getProvinces()
    {
        $response = Http::get('https://provinces.open-api.vn/api/p/');
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json([], 500);
    }

    // Lấy danh sách quận/huyện theo tỉnh/thành phố từ API thật
    public function getDistricts($provinceCode)
    {
        $response = Http::get("https://provinces.open-api.vn/api/p/{$provinceCode}?depth=2");
        if ($response->successful()) {
            $data = $response->json();
            return response()->json($data['districts'] ?? []);
        }
        return response()->json([], 500);
    }

    // Lấy danh sách phường/xã theo quận/huyện từ API thật
    public function getWards($districtCode)
    {
        $response = Http::get("https://provinces.open-api.vn/api/d/{$districtCode}?depth=2");
        if ($response->successful()) {
            $data = $response->json();
            return response()->json($data['wards'] ?? []);
        }
        return response()->json([], 500);
    }

    // Lấy danh sách địa chỉ (không cần user_id)
    public function index(Request $request)
    {
        $addresses = Address::all();
        return response()->json(['data' => $addresses]);
    }

    // Thêm địa chỉ mới (không cần user_id)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'phone' => [
                'required',
                'regex:/^(0|\+84)[1-9][0-9]{8,9}$/'
            ],
            'province' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'ward' => 'required|string|max:100',
            'street' => 'required|string|max:100',
        ]);
        // Thêm user_id mặc định nếu chưa có
        $validated['user_id'] = $request->user_id ?? 1;
        $address = Address::create($validated);
        return response()->json($address, 201);
    }

    // Xóa địa chỉ (không cần user_id)
    public function destroy(Request $request, $id)
    {
        $address = Address::findOrFail($id);
        $address->delete();
        return response()->json(['message' => 'Đã xóa địa chỉ']);
    }

    // Sửa địa chỉ (không cần user_id)
    public function update(Request $request, $id)
    {
        $address = Address::findOrFail($id);
        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:100',
            'phone' => [
                'sometimes',
                'regex:/^(0|\+84)[1-9][0-9]{8,9}$/'
            ],
            'province' => 'sometimes|string|max:100',
            'district' => 'sometimes|string|max:100',
            'ward' => 'sometimes|string|max:100',
            'street' => 'sometimes|string|max:100',
        ]);
        $address->update($validated);
        return response()->json($address);
    }
}
