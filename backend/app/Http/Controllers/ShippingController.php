<?php

namespace App\Http\Controllers;

use App\Services\GHNService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class ShippingController extends Controller
{
    protected $ghnService;

    public function __construct(GHNService $ghnService)
    {
        $this->ghnService = $ghnService;
    }

    public function calculateShippingFee(Request $request): JsonResponse
    {
        $data = $request->all();

        $validation = $this->ghnService->validateShippingData($data);
        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validation['errors']
            ], 400);
        }

        $result = $this->ghnService->calculateOrderShippingFee($data);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
    }


    public function calculateCartShippingFee(Request $request): JsonResponse
    {
        $request->validate([
            'to_district_id' => 'required|integer',
            'to_ward_code' => 'required|string',
            'cart_items' => 'required|array',
            'cart_items.*.product_id' => 'required|exists:products,id',
            'cart_items.*.quantity' => 'required|integer|min:1',
            'service_type_id' => 'nullable|in:2,5',
        ]);

        $totalWeight = 0;
        $totalValue = 0;
        $items = [];

        foreach ($request->cart_items as $item) {
            $product = \App\Models\Products::find($item['product_id']);
            if ($product) {
                $weight = $product->weight ?? 500;
                $quantity = $item['quantity'];

                $totalWeight += $weight * $quantity;
                $totalValue += $product->price * $quantity;

                if (($request->service_type_id ?? 2) === 5) {
                    $items[] = [
                        'name' => $product->name,
                        'quantity' => $quantity,
                        'length' => $product->length ?? 20,
                        'width' => $product->width ?? 20,
                        'height' => $product->height ?? 20,
                        'weight' => $weight,
                    ];
                }
            }
        }

        $shippingData = [
            'service_type_id' => $request->service_type_id ?? 2,
            'to_district_id' => $request->to_district_id,
            'to_ward_code' => $request->to_ward_code,
            'weight' => $totalWeight,
            'insurance_value' => $totalValue,
        ];

        if (($request->service_type_id ?? 2) === 2) {
            $shippingData['length'] = $request->length ?? 30;
            $shippingData['width'] = $request->width ?? 40;
            $shippingData['height'] = $request->height ?? 20;
        }

        if (($request->service_type_id ?? 2) === 5) {
            $shippingData['items'] = $items;
        }

        $result = $this->ghnService->calculateOrderShippingFee($shippingData);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Tính phí thành công',
                'data' => [
                    'shipping_fee' => $result['data'],
                    'total_weight' => $totalWeight,
                    'total_value' => $totalValue,
                    'estimated_delivery' => $this->getEstimatedDelivery($result['data']),
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
    }


    private function getEstimatedDelivery(array $shippingData): array
    {
        $total = $shippingData['total'] ?? 0;

        if ($total <= 20000) {
            return [
                'min_days' => 1,
                'max_days' => 2,
                'description' => 'Giao hàng trong 1-2 ngày'
            ];
        } elseif ($total <= 50000) {
            return [
                'min_days' => 2,
                'max_days' => 4,
                'description' => 'Giao hàng trong 2-4 ngày'
            ];
        } else {
            return [
                'min_days' => 3,
                'max_days' => 7,
                'description' => 'Giao hàng trong 3-7 ngày'
            ];
        }
    }


    public function getShopInfo(): JsonResponse
    {
        try {
            $shopId = config('services.ghn.shop_id');

            if (!$shopId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thiếu shop_id trong cấu hình'
                ], 400);
            }

            $response = Http::withHeaders([
                'Token' => config('services.ghn.api_token'),
                'ShopId' => $shopId,
            ])->get(config('services.ghn.base_url') . '/shop/detail');

            if ($response->successful()) {
                $data = $response->json();
                if ($data['code'] === 200) {
                    return response()->json([
                        'success' => true,
                        'data' => $data['data']
                    ]);
                }
            }

            $response2 = Http::withHeaders([
                'Token' => config('services.ghn.api_token'),
            ])->get(config('services.ghn.base_url') . '/shop', [
                'id' => $shopId
            ]);

            if ($response2->successful()) {
                $data2 = $response2->json();
                if ($data2['code'] === 200) {
                    return response()->json([
                        'success' => true,
                        'data' => $data2['data']
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy thông tin shop từ GHN: ' . $response->body()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getConfig(): JsonResponse
    {
        try {
            $shopInfo = $this->getShopInfoFromGHN();

            return response()->json([
                'success' => true,
                'data' => [
                    'base_url' => config('services.ghn.base_url'),
                    'token' => config('services.ghn.api_token'),
                    'shop_id' => config('services.ghn.shop_id'),
                    'shop_info' => $shopInfo
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy cấu hình: ' . $e->getMessage()
            ], 500);
        }
    }


    private function getShopInfoFromGHN(): ?array
    {
        try {
            $shopId = config('services.ghn.shop_id');

            $response = Http::withHeaders([
                'Token' => config('services.ghn.api_token'),
                'ShopId' => $shopId,
            ])->get(config('services.ghn.base_url') . '/shop/detail');

            if ($response->successful()) {
                $data = $response->json();
                if ($data['code'] === 200) {
                    return $data['data'];
                }
            }

            $response2 = Http::withHeaders([
                'Token' => config('services.ghn.api_token'),
            ])->get(config('services.ghn.base_url') . '/shop', [
                'id' => $shopId
            ]);

            if ($response2->successful()) {
                $data2 = $response2->json();
                if ($data2['code'] === 200) {
                    return $data2['data'];
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }


    public function getProvinces(): JsonResponse
    {
        try {
            $response = Http::withHeaders([
                'Token' => config('services.ghn.api_token'),
            ])->get('https://online-gateway.ghn.vn/shiip/public-api/master-data/province');

            if ($response->successful()) {
                $data = $response->json();
                if ($data['code'] === 200) {
                    return response()->json([
                        'success' => true,
                        'data' => $data['data']
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách tỉnh từ GHN: ' . $response->body()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getDistricts(Request $request): JsonResponse
    {
        try {
            $provinceId = $request->input('province_id');

            if (!$provinceId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thiếu province_id'
                ], 400);
            }

            $response = Http::withHeaders([
                'Token' => config('services.ghn.api_token'),
            ])->get('https://online-gateway.ghn.vn/shiip/public-api/master-data/district', [
                'province_id' => $provinceId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['code'] === 200) {
                    return response()->json([
                        'success' => true,
                        'data' => $data['data']
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách huyện từ GHN: ' . $response->body()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getWards(Request $request): JsonResponse
    {
        try {
            $districtId = $request->input('district_id');

            if (!$districtId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thiếu district_id'
                ], 400);
            }

            $response = Http::withHeaders([
                'Token' => config('services.ghn.api_token'),
            ])->get('https://online-gateway.ghn.vn/shiip/public-api/master-data/ward', [
                'district_id' => $districtId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['code'] === 200) {
                    return response()->json([
                        'success' => true,
                        'data' => $data['data']
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách xã từ GHN: ' . $response->body()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage()
            ], 500);
        }
    }
}
