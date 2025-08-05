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

    /**
     * Tính phí vận chuyển
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function calculateShippingFee(Request $request): JsonResponse
    {
        $data = $request->all();

        // Validate dữ liệu đầu vào
        $validation = $this->ghnService->validateShippingData($data);
        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validation['errors']
            ], 400);
        }

        // Tính phí vận chuyển
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

    /**
     * Tính phí vận chuyển từ giỏ hàng
     *
     * @param Request $request
     * @return JsonResponse
     */
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

        // Tính tổng cân nặng và giá trị từ giỏ hàng
        $totalWeight = 0;
        $totalValue = 0;
        $items = [];

        foreach ($request->cart_items as $item) {
            $product = \App\Models\Products::find($item['product_id']);
            if ($product) {
                $weight = $product->weight ?? 500; // Mặc định 500g nếu không có
                $quantity = $item['quantity'];
                
                $totalWeight += $weight * $quantity;
                $totalValue += $product->price * $quantity;

                // Nếu là hàng nặng, thêm thông tin items
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

        // Chuẩn bị dữ liệu cho API
        $shippingData = [
            'service_type_id' => $request->service_type_id ?? 2,
            'to_district_id' => $request->to_district_id,
            'to_ward_code' => $request->to_ward_code,
            'weight' => $totalWeight,
            'insurance_value' => $totalValue,
        ];

        // Thêm kích thước cho hàng nhẹ
        if (($request->service_type_id ?? 2) === 2) {
            $shippingData['length'] = $request->length ?? 30;
            $shippingData['width'] = $request->width ?? 40;
            $shippingData['height'] = $request->height ?? 20;
        }

        // Thêm items cho hàng nặng
        if (($request->service_type_id ?? 2) === 5) {
            $shippingData['items'] = $items;
        }

        // Tính phí vận chuyển bằng GHN API thực tế
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

    /**
     * Lấy thông tin ước tính thời gian giao hàng
     *
     * @param array $shippingData
     * @return array
     */
    private function getEstimatedDelivery(array $shippingData): array
    {
        $total = $shippingData['total'] ?? 0;
        
        // Ước tính thời gian dựa trên phí vận chuyển
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

    /**
     * Lấy thông tin shop từ GHN API
     *
     * @return JsonResponse
     */
    public function getShopInfo(): JsonResponse
    {
        try {
            $shopId = config('services.ghn.shop_id'); // Lấy từ .env
            
            if (!$shopId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thiếu shop_id trong cấu hình'
                ], 400);
            }

            // Gọi API lấy thông tin shop
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

            // Nếu API shop/detail không hoạt động, thử API khác
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

    /**
     * Lấy cấu hình GHN và thông tin shop
     *
     * @return JsonResponse
     */
    public function getConfig(): JsonResponse
    {
        try {
            // Lấy thông tin shop từ GHN API
            $shopInfo = $this->getShopInfoFromGHN();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'base_url' => config('services.ghn.base_url'),
                    'token' => config('services.ghn.api_token'),
                    'shop_id' => config('services.ghn.shop_id'), // Lấy từ .env
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

    /**
     * Lấy thông tin shop từ GHN API (private method)
     *
     * @return array|null
     */
    private function getShopInfoFromGHN(): ?array
    {
        try {
            $shopId = config('services.ghn.shop_id'); // Lấy từ .env
            
            // Thử API shop/detail trước
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

            // Thử API shop với query parameter
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

            // Nếu không lấy được từ API, trả về null
            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Lấy danh sách tỉnh từ GHN API
     *
     * @return JsonResponse
     */
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

    /**
     * Lấy danh sách huyện từ GHN API
     *
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * Lấy danh sách xã từ GHN API
     *
     * @param Request $request
     * @return JsonResponse
     */
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