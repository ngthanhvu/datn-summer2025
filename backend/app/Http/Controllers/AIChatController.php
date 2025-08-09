<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Products;
use App\Models\Coupons;
use App\Models\FlashSale;
use App\Models\Categories;
use App\Models\Brands;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class AIChatController extends Controller
{
    private $geminiApiKey;
    private $geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct()
    {
        $this->geminiApiKey = env('GEMINI_API_KEY');
    }

    public function chat(Request $request)
    {
        try {
            $userMessage = $request->input('message');
            $userId = $request->user() ? $request->user()->id : null;

            // Lấy context liên quan trước
            $relevantContext = $this->getRelevantContext($userMessage);
            
            if (isset($relevantContext['products']) && $relevantContext['products']->count() > 0) {
                $context = $relevantContext;
            } else {
                try {
                    $context = $this->getDatabaseContext();
                } catch (\Exception $e) {
                    $context = [
                        'products' => collect([]),
                        'coupons' => collect([]),
                        'flash_sales' => collect([]),
                        'categories' => collect([]),
                        'brands' => collect([])
                    ];
                }
            }
            
            $prompt = $this->buildPrompt($userMessage, $context);
            $response = $this->callGeminiAPI($prompt);
            $aiResponse = $this->processAIResponse($response, $userMessage);
            
            // Xử lý hình ảnh cho sản phẩm
            if (isset($relevantContext['products'])) {
                $relevantContext['products']->each(function ($product) {
                    if ($product->mainImage && $product->mainImage->image_path) {
                        // Đảm bảo đường dẫn bắt đầu với storage/
                        $imagePath = $product->mainImage->image_path;
                        if (!str_starts_with($imagePath, 'storage/')) {
                            $imagePath = 'storage/' . ltrim($imagePath, '/');
                        }
                        $product->mainImage->image_url = url($imagePath);
                    }
                });
                
                $relevantContext['products'] = $relevantContext['products']->map(function ($product) {
                    $productArray = $product->toArray();
                    if (isset($productArray['main_image'])) {
                        $productArray['mainImage'] = $productArray['main_image'];
                        unset($productArray['main_image']);
                    }
                    return $productArray;
                });
            }
            
            return response()->json([
                'success' => true,
                'message' => $aiResponse,
                'context' => $relevantContext
            ]);
        } catch (\Exception $e) {
            \Log::error('AI Chat Error: ' . $e->getMessage(), [
                'user_message' => $userMessage ?? 'null',
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Xin lỗi, tôi đang gặp sự cố. Vui lòng thử lại sau.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getDatabaseContext()
    {
        return Cache::remember('ai_chat_context', 300, function () {
            $products = Products::with(['categories', 'brand', 'mainImage', 'variants.inventory', 'images'])
                ->where('is_active', true)
                ->take(50)
                ->get();

            $coupons = Coupons::where('is_active', true)
                ->where('end_date', '>', now())
                ->take(20)
                ->get();

            $flashSales = FlashSale::with(['products.product'])
                ->where('active', true)
                ->where('end_time', '>', now())
                ->take(10)
                ->get();

            $categories = Categories::where('is_active', true)->take(20)->get();
            $brands = Brands::where('is_active', true)->take(20)->get();

            $settings = Setting::first();

            return [
                'products' => $products,
                'coupons' => $coupons,
                'flash_sales' => $flashSales,
                'categories' => $categories,
                'brands' => $brands,
                'settings' => $settings
            ];
        });
    }

    private function buildPrompt($userMessage, $context)
    {
        $systemPrompt = "Bạn là một trợ lý AI thông minh cho một cửa hàng trực tuyến. Bạn có thể:

1. Tìm kiếm và tư vấn sản phẩm
2. Thông tin về mã giảm giá và khuyến mãi
3. Hướng dẫn quy trình thanh toán
4. Thông tin về flash sale
5. Tư vấn về danh mục sản phẩm và thương hiệu
6. Hỗ trợ khách hàng

Hãy trả lời bằng tiếng Việt một cách thân thiện và hữu ích. 

**QUAN TRỌNG VỀ TÌM KIẾM SẢN PHẨM:**

1. **Khi khách hàng hỏi chung chung như 'tìm sản phẩm', 'mua sản phẩm', 'có gì bán'**: 
   - **NGAY LẬP TỨC** cung cấp thông tin chi tiết về các sản phẩm có sẵn
   - Liệt kê 2-3 sản phẩm ngẫu nhiên với thông tin đầy đủ
   - Mỗi sản phẩm bao gồm: tên, giá, danh mục, size, màu sắc (nếu có)
   - Không hỏi thêm câu hỏi chung chung

2. **Khi khách hàng nói cụ thể như 'áo khoác', 'áo polo', 'quần jean'**: 
   - **NGAY LẬP TỨC** cung cấp thông tin chi tiết về sản phẩm đó
   - Không hỏi thêm câu hỏi chung chung
   - Cung cấp thông tin bao gồm:
     - Tên sản phẩm và mô tả
     - Giá gốc và giá khuyến mãi (nếu có)
     - Danh mục sản phẩm
     - **Size có sẵn** (nếu có thông tin)
     - **Màu sắc có sẵn** (nếu có thông tin)
     - **Hình ảnh sản phẩm** (nếu có)
     - Thông tin về chất liệu và đặc điểm sản phẩm

3. **Khi khách hàng hỏi về danh mục cụ thể**: 
   - Cung cấp thông tin chi tiết về các sản phẩm trong danh mục đó
   - Liệt kê 2-3 sản phẩm tiêu biểu với thông tin đầy đủ

4. **LUÔN LUÔN cung cấp thông tin chi tiết thay vì hỏi lại**:
   - Nếu có sản phẩm phù hợp, hãy trả lời ngay với thông tin chi tiết
   - Không bao giờ hỏi 'bạn muốn tìm gì?' nếu đã có thông tin sẵn
   - Luôn cung cấp giá cả, size, màu sắc nếu có

5. **Cách trả lời mẫu cho sản phẩm**:
   'Tôi tìm thấy [số lượng] sản phẩm phù hợp với yêu cầu của bạn:

   📦 [Tên sản phẩm]
   💰 Giá: [Giá gốc] VNĐ
   🏷️ Giảm giá: [Giá khuyến mãi] VNĐ (nếu có)
   📂 Danh mục: [Tên danh mục]
   📏 Size: [Các size có sẵn]
   🎨 Màu: [Các màu có sẵn]
   📝 Mô tả: [Mô tả ngắn gọn]'";

        $contextData = $this->formatContextForPrompt($context);
        
        return $systemPrompt . "\n\n" . $contextData . "\n\nKhách hàng: " . $userMessage . "\n\nTrợ lý AI:";
    }

    private function formatContextForPrompt($context)
    {
        $formatted = "THÔNG TIN CỬA HÀNG:\n\n";
        
        if (isset($context['products']) && $context['products']->count() > 0) {
            $formatted .= "SẢN PHẨM:\n";
            foreach ($context['products'] as $product) {
                $formatted .= "📦 {$product->name}\n";
                $formatted .= "💰 Giá gốc: " . number_format($product->price) . " VNĐ\n";
                
                if ($product->discount_price) {
                    $formatted .= "🏷️ Giảm giá: " . number_format($product->discount_price) . " VNĐ\n";
                    $discountPercent = round((($product->price - $product->discount_price) / $product->price) * 100);
                    $formatted .= "📊 Tiết kiệm: {$discountPercent}%\n";
                }
                
                if ($product->categories) {
                    $formatted .= "📂 Danh mục: {$product->categories->name}\n";
                }
                
                if ($product->variants && $product->variants->count() > 0) {
                    $sizes = $product->variants->pluck('size')->unique()->implode(', ');
                    $colors = $product->variants->pluck('color')->unique()->implode(', ');
                    if ($sizes) {
                        $formatted .= "📏 Size có sẵn: {$sizes}\n";
                    }
                    if ($colors) {
                        $formatted .= "🎨 Màu sắc: {$colors}\n";
                    }
                }
                
                if ($product->brand) {
                    $formatted .= "🏢 Thương hiệu: {$product->brand->name}\n";
                }
                
                if ($product->description) {
                    $shortDesc = substr($product->description, 0, 100);
                    $formatted .= "📝 Mô tả: {$shortDesc}...\n";
                }
                
                $formatted .= "---\n";
            }
        }
        
        if (isset($context['coupons']) && $context['coupons']->count() > 0) {
            $formatted .= "\n🎫 MÃ GIẢM GIÁ HIỆN CÓ:\n";
            foreach ($context['coupons'] as $coupon) {
                $formatted .= "• {$coupon->name}\n";
                $formatted .= "  Mã: {$coupon->code}\n";
                $formatted .= "  Giảm: {$coupon->value}";
                if ($coupon->type === 'percent') {
                    $formatted .= "% (Tối đa: " . number_format($coupon->max_discount_value) . " VNĐ)";
                } else {
                    $formatted .= " VNĐ";
                }
                $formatted .= "\n  Đơn tối thiểu: " . number_format($coupon->min_order_value) . " VNĐ\n";
                if ($coupon->description) {
                    $formatted .= "  Mô tả: {$coupon->description}\n";
                }
                $formatted .= "---\n";
            }
        }
        
        if (isset($context['flash_sales']) && $context['flash_sales']->count() > 0) {
            $formatted .= "\n⚡ FLASH SALE ĐANG DIỄN RA:\n";
            foreach ($context['flash_sales'] as $flashSale) {
                $formatted .= "• {$flashSale->name}\n";
                $formatted .= "  Thời gian: {$flashSale->start_time} - {$flashSale->end_time}\n";
                if ($flashSale->description) {
                    $formatted .= "  Mô tả: {$flashSale->description}\n";
                }
                $formatted .= "---\n";
            }
        }
        
        if (isset($context['categories']) && $context['categories']->count() > 0) {
            $formatted .= "\n📂 DANH MỤC SẢN PHẨM:\n";
            foreach ($context['categories'] as $category) {
                $formatted .= "• {$category->name}\n";
            }
        }
        
        if (isset($context['brands']) && $context['brands']->count() > 0) {
            $formatted .= "\n🏢 THƯƠNG HIỆU:\n";
            foreach ($context['brands'] as $brand) {
                $formatted .= "• {$brand->name}\n";
            }
        }
        
        return $formatted;
    }

    private function callGeminiAPI($prompt)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-goog-api-key' => $this->geminiApiKey
        ])->post($this->geminiApiUrl, [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        }

        throw new \Exception('Gemini API error: ' . $response->body());
    }

    private function processAIResponse($aiResponse, $userMessage)
    {
        $response = trim($aiResponse);
        
        $message = strtolower($userMessage);
        $generalKeywords = ['tìm sản phẩm', 'mua sản phẩm', 'có gì bán', 'sản phẩm nào', 'mua gì', 'tìm gì', 'có gì'];
        
        $isGeneralSearch = false;
        foreach ($generalKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $isGeneralSearch = true;
                break;
            }
        }
        
        if ($isGeneralSearch && (strlen($response) < 100 || strpos($response, 'bạn muốn tìm gì') !== false)) {
            $response = "Tôi tìm thấy một số sản phẩm tiêu biểu trong cửa hàng:\n\n";
            $response .= "📦 Sản phẩm chất lượng cao\n";
            $response .= "💰 Giá cả cạnh tranh\n";
            $response .= "🏷️ Nhiều khuyến mãi hấp dẫn\n\n";
            $response .= "Bạn có thể hỏi tôi về:\n";
            $response .= "• Sản phẩm cụ thể (áo khoác, quần jean, giày...)\n";
            $response .= "• Mã giảm giá\n";
            $response .= "• Flash sale";
        }
        
        if (strlen($response) < 50) {
            $response .= "\n\nBạn có thể hỏi tôi về:\n- Sản phẩm cụ thể\n- Mã giảm giá\n- Flash sale\n- Quy trình thanh toán\n- Danh mục sản phẩm";
        }
        
        return $response;
    }

    private function getRelevantContext($userMessage)
    {
        try {
            $context = [];
            $message = strtolower($userMessage);
            
            $productQuery = Products::with(['categories', 'brand', 'mainImage', 'variants.inventory', 'images'])
                ->where('is_active', true);
            
            $generalSearchKeywords = [
                'tìm sản phẩm', 'mua sản phẩm', 'có gì bán', 'sản phẩm nào', 
                'mua gì', 'tìm gì', 'có gì', 'bán gì', 'shop có gì'
            ];
            
            $isGeneralSearch = false;
            foreach ($generalSearchKeywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $isGeneralSearch = true;
                    break;
                }
            }
            
            if ($isGeneralSearch) {
                $context['products'] = $productQuery->inRandomOrder()->take(3)->get();
                return $context;
            }
            
            if (strpos($message, 'áo khoác') !== false) {
                $productQuery->whereHas('categories', function($q) {
                    $q->where('name', 'like', '%áo khoác%');
                });
            } elseif (strpos($message, 'áo polo') !== false) {
                $productQuery->where('name', 'like', '%áo polo%');
            } elseif (strpos($message, 'sơ mi') !== false) {
                $productQuery->where('name', 'like', '%sơ mi%')
                    ->orWhereHas('categories', function($q) {
                        $q->where('name', 'like', '%sơ mi%');
                    });
            } elseif (strpos($message, 'váy') !== false) {
                $productQuery->whereHas('categories', function($q) {
                    $q->where('name', 'like', '%váy%');
                });
            } elseif (strpos($message, 'đầm') !== false) {
                $productQuery->where('name', 'like', '%đầm%');
            } elseif (strpos($message, 'quần') !== false) {
                $productQuery->whereHas('categories', function($q) {
                    $q->where('name', 'like', '%quần%');
                });
            } elseif (strpos($message, 'giày') !== false) {
                $productQuery->whereHas('categories', function($q) {
                    $q->where('name', 'like', '%giày%');
                });
            } elseif (strpos($message, 'dép') !== false) {
                $productQuery->whereHas('categories', function($q) {
                    $q->where('name', 'like', '%dép%');
                });
            } elseif (strpos($message, 'túi') !== false) {
                $productQuery->whereHas('categories', function($q) {
                    $q->where('name', 'like', '%túi%');
                });
            } elseif (strpos($message, 'áo') !== false && !strpos($message, 'áo khoác') && !strpos($message, 'áo polo') && !strpos($message, 'sơ mi')) {
                $productQuery->whereHas('categories', function($q) {
                    $q->where('name', 'like', '%áo%');
                });
            }
            
            if (strpos($message, 'áo khoác') !== false || strpos($message, 'áo polo') !== false || 
                strpos($message, 'sơ mi') !== false || strpos($message, 'váy') !== false || 
                strpos($message, 'đầm') !== false || strpos($message, 'quần') !== false || 
                strpos($message, 'giày') !== false || strpos($message, 'dép') !== false || 
                strpos($message, 'túi') !== false || strpos($message, 'áo') !== false) {
                $context['products'] = $productQuery->inRandomOrder()->take(3)->get();
            }
            
            if (strpos($message, 'mã giảm') !== false || strpos($message, 'coupon') !== false || 
                strpos($message, 'giảm giá') !== false) {
                $context['coupons'] = Coupons::where('is_active', true)
                    ->where('end_date', '>', now())
                    ->take(5)
                    ->get();
            }
            
            if (strpos($message, 'flash') !== false || strpos($message, 'khuyến mãi') !== false) {
                $context['flash_sales'] = FlashSale::with(['products.product'])
                    ->where('active', true)
                    ->where('end_time', '>', now())
                    ->take(5)
                    ->get();
            }
            
            return $context;
        } catch (\Exception $e) {
            \Log::error('getRelevantContext Error: ' . $e->getMessage());
            return [];
        }
    }

    public function searchProducts(Request $request)
    {
        $query = $request->input('query');
        
        $products = Products::with(['categories', 'brand', 'mainImage'])
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->where('is_active', true)
            ->take(10)
            ->get();
            
        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

    public function getAvailableCoupons()
    {
        $coupons = Coupons::where('is_active', true)
            ->where('end_date', '>', now())
            ->where(function($query) {
                $query->whereNull('usage_limit')
                      ->orWhereRaw('used_count < usage_limit');
            })
            ->get();
            
        return response()->json([
            'success' => true,
            'coupons' => $coupons
        ]);
    }

    public function getActiveFlashSales()
    {
        $flashSales = FlashSale::with(['products.product'])
            ->where('active', true)
            ->where('end_time', '>', now())
            ->get();
            
        return response()->json([
            'success' => true,
            'flash_sales' => $flashSales
        ]);
    }


}
