<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function getProvinces()
    {
        $response = Http::get('http://provinces.open-api.vn/api/p/');
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json([], 500);
    }

    public function getDistricts($provinceCode)
    {
        $response = Http::get("http://provinces.open-api.vn/api/p/{$provinceCode}?depth=2");
        if ($response->successful()) {
            $data = $response->json();
            return response()->json($data['districts'] ?? []);
        }
        return response()->json([], 500);
    }

    public function getWards($districtCode)
    {
        $response = Http::get("http://provinces.open-api.vn/api/d/{$districtCode}?depth=2");
        if ($response->successful()) {
            $data = $response->json();
            return response()->json($data['wards'] ?? []);
        }
        return response()->json([], 500);
    }

    public function index(Request $request)
    {
        $addresses = Address::all();
        return response()->json(['data' => $addresses]);
    }

    public function getMyAddress()
    {
        // $user = Auth::user()->id;
        // $address = Address::where('user_id', $user->id)->get();

        $user_id = Auth::id();    // int
        $address = Address::where('user_id', $user_id)->get();

        return response()->json($address);
    }

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
        // $validated['user_id'] = $request->user_id ?? 1;
        $validated['user_id'] = Auth::id();
        $address = Address::create($validated);
        return response()->json($address, 201);
    }

    public function destroy(Request $request, $id)
    {
        $address = Address::findOrFail($id);
        $address->delete();
        return response()->json(['message' => 'Đã xóa địa chỉ']);
    }

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
