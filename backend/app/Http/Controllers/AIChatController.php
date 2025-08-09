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

            $contextHints = $request->input('context', []);
            $relevantContext = $this->getRelevantContext($userMessage, $contextHints);

            $hasProducts = isset($relevantContext['products']) && $relevantContext['products'] && $relevantContext['products']->count() > 0;
            $hasCoupons = isset($relevantContext['coupons']) && $relevantContext['coupons'] && $relevantContext['coupons']->count() > 0;
            $hasFlashSales = isset($relevantContext['flash_sales']) && $relevantContext['flash_sales'] && $relevantContext['flash_sales']->count() > 0;

            if ($hasProducts || $hasCoupons || $hasFlashSales) {
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
            
            // Phân loại loại câu hỏi để lọc context gửi ra frontend
            $lowerMsg = strtolower($userMessage);
            $flashSaleKeywords = ['flash sale', 'flashsale', 'khuyến mãi', 'sale', 'giảm giá', 'khuyến mãi gì', 'có sale không', 'có khuyến mãi không', 'flash sale nào', 'sale gì'];
            $couponKeywords = ['mã giảm', 'coupon', 'mã khuyến mãi', 'mã giảm giá', 'có mã giảm không', 'mã giảm nào', 'coupon nào', 'mã khuyến mãi nào', 'giảm giá gì'];
            $stockKeywords = ['còn hàng', 'có hàng', 'tồn kho', 'số lượng', 'bao nhiêu cái'];
            $generalKeywords = ['tìm sản phẩm', 'mua sản phẩm', 'có gì bán', 'sản phẩm nào', 'mua gì', 'tìm gì', 'có gì'];

            $isFlashSaleQuestion = false; foreach ($flashSaleKeywords as $kw) { if (strpos($lowerMsg, $kw) !== false) { $isFlashSaleQuestion = true; break; } }
            $isCouponQuestion = false; foreach ($couponKeywords as $kw) { if (strpos($lowerMsg, $kw) !== false) { $isCouponQuestion = true; break; } }
            $isStockQuestion = false; foreach ($stockKeywords as $kw) { if (strpos($lowerMsg, $kw) !== false) { $isStockQuestion = true; break; } }
            $isGeneralProductQuestion = false; foreach ($generalKeywords as $kw) { if (strpos($lowerMsg, $kw) !== false) { $isGeneralProductQuestion = true; break; } }

            $prompt = $this->buildPrompt($userMessage, $context);
            $response = $this->callGeminiAPI($prompt);
            $aiResponse = $this->processAIResponse($response, $userMessage);
            
            $finalContext = ($hasProducts || $hasCoupons || $hasFlashSales) ? $relevantContext : $context;

            if ($isFlashSaleQuestion) {
                unset($finalContext['products']);
                unset($finalContext['coupons']);
            } elseif ($isCouponQuestion) {
                unset($finalContext['products']);
                unset($finalContext['flash_sales']);
            } else {
                unset($finalContext['coupons']);
                unset($finalContext['flash_sales']);
            }
            $this->processProductImages($finalContext);
            
            return response()->json([
                'success' => true,
                'message' => $aiResponse,
                'context' => $finalContext
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

**QUAN TRỌNG VỀ LOGIC HIỂN THỊ:**

**KHI KHÁCH HÀNG HỎI VỀ FLASH SALE/KHUYẾN MÃI:**
- CHỈ hiển thị thông tin về flash sale và khuyến mãi
- KHÔNG hiển thị sản phẩm không liên quan
- Tập trung vào thông tin sale, thời gian, mô tả

**KHI KHÁCH HÀNG HỎI VỀ MÃ GIẢM GIÁ:**
- CHỈ hiển thị thông tin về mã giảm giá
- KHÔNG hiển thị sản phẩm hoặc flash sale
- Tập trung vào mã code, giá trị giảm, điều kiện sử dụng

**KHI KHÁCH HÀNG HỎI VỀ SẢN PHẨM:**
- CHỈ hiển thị thông tin sản phẩm phù hợp với yêu cầu
- Nếu hỏi về sản phẩm nam: CHỈ hiển thị sản phẩm nam (áo nam, quần nam, giày nam...)
- Nếu hỏi về sản phẩm nữ: CHỈ hiển thị sản phẩm nữ (váy, đầm, áo nữ, quần nữ...)
- KHÔNG hiển thị flash sale hoặc mã giảm giá
- Tập trung vào thông tin sản phẩm: tên, giá, size, màu, tồn kho

**QUAN TRỌNG VỀ TÌM KIẾM SẢN PHẨM:**

1. **Khi khách hàng hỏi chung chung như 'tìm sản phẩm', 'mua sản phẩm', 'có gì bán'**: 
   - **NGAY LẬP TỨC** cung cấp thông tin chi tiết về các sản phẩm có sẵn
   - Liệt kê 2-3 sản phẩm ngẫu nhiên với thông tin đầy đủ
   - Mỗi sản phẩm bao gồm: tên, giá, danh mục, size, màu sắc (nếu có)
   - Không hỏi thêm câu hỏi chung chung

2. **Khi khách hàng nói cụ thể như 'áo polo vải mềm', 'áo khoác nam', 'quần jean nữ'**: 
   - **CHỈ HIỂN THỊ** sản phẩm phù hợp chính xác với yêu cầu
   - Nếu khách hàng nói 'áo polo vải mềm' thì CHỈ hiển thị sản phẩm có tên chứa cả 'áo polo' VÀ 'vải mềm'
   - Nếu khách hàng nói 'áo polo nam' thì CHỈ hiển thị sản phẩm có tên chứa cả 'áo polo' VÀ 'nam'
   - **KHÔNG** hiển thị tất cả sản phẩm cùng loại
   - Cung cấp thông tin chi tiết về sản phẩm cụ thể đó

3. **Khi khách hàng nói chung như 'áo khoác', 'áo polo', 'quần jean'**: 
   - Hiển thị 2-3 sản phẩm tiêu biểu trong danh mục đó
   - **QUAN TRỌNG**: Nếu khách hàng hỏi về giới tính cụ thể (nam/nữ), CHỈ hiển thị sản phẩm phù hợp với giới tính đó
   - Cung cấp thông tin bao gồm:
     - Tên sản phẩm và mô tả
     - Giá gốc và giá khuyến mãi (nếu có)
     - Danh mục sản phẩm
     - **Size có sẵn** (nếu có thông tin)
     - **Màu sắc có sẵn** (nếu có thông tin)
     - **Hình ảnh sản phẩm** (nếu có)
     - Thông tin về chất liệu và đặc điểm sản phẩm

4. **Khi khách hàng hỏi về danh mục cụ thể**: 
   - Cung cấp thông tin chi tiết về các sản phẩm trong danh mục đó
   - Liệt kê 2-3 sản phẩm tiêu biểu với thông tin đầy đủ

5. **LUÔN LUÔN cung cấp thông tin chi tiết thay vì hỏi lại**:
   - Nếu có sản phẩm phù hợp, hãy trả lời ngay với thông tin chi tiết
   - Không bao giờ hỏi 'bạn muốn tìm gì?' nếu đã có thông tin sẵn
   - Luôn cung cấp giá cả, size, màu sắc nếu có

6. **XỬ LÝ CÂU HỎI VỀ GIỚI TÍNH**:
   - Khi khách hàng hỏi 'có đồ nam không', 'có quần áo nam không', 'có váy nữ không':
     + CHỈ hiển thị sản phẩm phù hợp với giới tính được yêu cầu
     + KHÔNG hiển thị sản phẩm của giới tính khác
     + Nếu hỏi về nam: chỉ hiển thị áo nam, quần nam, giày nam...
     + Nếu hỏi về nữ: chỉ hiển thị váy, đầm, áo nữ, quần nữ...

7. **Cách trả lời mẫu cho sản phẩm**:
   'Tôi tìm thấy [số lượng] sản phẩm phù hợp với yêu cầu của bạn:

   📦 [Tên sản phẩm]
   💰 Giá: [Giá gốc] VNĐ
   🏷️ Giảm giá: [Giá khuyến mãi] VNĐ (nếu có)
   📂 Danh mục: [Tên danh mục]
   📏 Size: [Các size có sẵn]
   🎨 Màu: [Các màu có sẵn]
   📦 Tình trạng: [Còn hàng/Hết hàng] ([Số lượng] sản phẩm)
   📊 Chi tiết tồn kho: [Size (Màu): Số lượng] (nếu có)
   📝 Mô tả: [Mô tả ngắn gọn]'

7. **QUAN TRỌNG VỀ HÌNH ẢNH**:
   - KHÔNG hiển thị URL hình ảnh trong text trả lời
   - Hình ảnh sẽ được hiển thị tự động bên dưới thông qua ProductCard
   - Chỉ cung cấp thông tin sản phẩm, không cần đề cập đến hình ảnh

8. **LƯU Ý QUAN TRỌNG**:
   - KHÔNG BAO GIỜ hiển thị URL hình ảnh trong text
   - Chỉ hiển thị thông tin sản phẩm: tên, giá, danh mục, size, màu, mô tả
   - Hình ảnh sẽ tự động xuất hiện bên dưới thông qua giao diện ProductCard

9. **XỬ LÝ CÂU HỎI VỀ TỒN KHO**:
   - Khi khách hàng hỏi 'còn hàng không', 'có hàng không' (câu hỏi đơn giản):
     + Trả lời ngắn gọn: 'Còn hàng' hoặc 'Hết hàng'
     + KHÔNG hiển thị chi tiết tồn kho, size, màu, giá cả
     + Chỉ xác nhận tình trạng còn hàng
   - Khi khách hàng hỏi 'tồn kho bao nhiêu', 'còn bao nhiêu cái' (câu hỏi chi tiết):
     + Hiển thị thông tin tồn kho chi tiết
     + Hiển thị số lượng theo từng size và màu (nếu có)
     + Thông báo rõ ràng: 'Còn hàng' hoặc 'Hết hàng'
     + Nếu còn hàng: hiển thị số lượng cụ thể
     + Nếu hết hàng: thông báo 'Hết hàng' và có thể gợi ý sản phẩm tương tự
   - **QUAN TRỌNG**: Khi hỏi về tồn kho, CHỈ hiển thị sản phẩm được đề cập, KHÔNG hiển thị tất cả sản phẩm khác";

        $contextData = $this->formatContextForPrompt($context, $userMessage);
        
        return $systemPrompt . "\n\n" . $contextData . "\n\nKhách hàng: " . $userMessage . "\n\nTrợ lý AI:";
    }

    private function formatContextForPrompt($context, $userMessage = '')
    {
        $formatted = "THÔNG TIN CỬA HÀNG:\n\n";
        
        if (isset($context['products']) && 
            ((is_object($context['products']) && $context['products']->count() > 0) || 
             (is_array($context['products']) && count($context['products']) > 0))) {
            $formatted .= "SẢN PHẨM:\n";
            foreach ($context['products'] as $product) {
                $name = is_object($product) ? $product->name : $product['name'];
                $price = is_object($product) ? $product->price : $product['price'];
                $discountPrice = is_object($product) ? $product->discount_price : ($product['discount_price'] ?? null);
                
                $formatted .= "📦 {$name}\n";
                $formatted .= "💰 Giá gốc: " . number_format($price) . " VNĐ\n";
                
                if ($discountPrice) {
                    $formatted .= "🏷️ Giảm giá: " . number_format($discountPrice) . " VNĐ\n";
                    $discountPercent = round((($price - $discountPrice) / $price) * 100);
                    $formatted .= "📊 Tiết kiệm: {$discountPercent}%\n";
                }
                
                if (is_object($product)) {
                    if ($product->categories) {
                        $formatted .= "📂 Danh mục: {$product->categories->name}\n";
                    }
                } else {
                    if (isset($product['categories']) && isset($product['categories']['name'])) {
                        $formatted .= "📂 Danh mục: {$product['categories']['name']}\n";
                    }
                }
                
                if (is_object($product)) {
                    if ($product->variants && $product->variants->count() > 0) {
                        $sizes = $product->variants->pluck('size')->unique()->implode(', ');
                        $colors = $product->variants->pluck('color')->unique()->implode(', ');
                        if ($sizes) {
                            $formatted .= "📏 Size có sẵn: {$sizes}\n";
                        }
                        if ($colors) {
                            $formatted .= "🎨 Màu sắc: {$colors}\n";
                        }
                        
                        $totalStock = 0;
                        $stockDetails = [];
                        foreach ($product->variants as $variant) {
                            if ($variant->inventory) {
                                $stock = $variant->inventory->quantity ?? 0;
                                $totalStock += $stock;
                                if ($stock > 0) {
                                    $stockDetails[] = "{$variant->size} ({$variant->color}): {$stock}";
                                }
                            }
                        }
                        
                        $isSimpleStockQuestion = $this->isSimpleStockQuestion($userMessage);
                        
                        if ($totalStock > 0) {
                            if ($isSimpleStockQuestion) {
                                $formatted .= "📦 Tình trạng: Còn hàng\n";
                            } else {
                                $formatted .= "📦 Tình trạng: Còn hàng ({$totalStock} sản phẩm)\n";
                                if (!empty($stockDetails)) {
                                    $formatted .= "📊 Chi tiết tồn kho: " . implode(', ', $stockDetails) . "\n";
                                }
                            }
                        } else {
                            $formatted .= "📦 Tình trạng: Hết hàng\n";
                        }
                    }
                } else {
                    if (isset($product['variants']) && count($product['variants']) > 0) {
                        $sizes = collect($product['variants'])->pluck('size')->unique()->implode(', ');
                        $colors = collect($product['variants'])->pluck('color')->unique()->implode(', ');
                        if ($sizes) {
                            $formatted .= "📏 Size có sẵn: {$sizes}\n";
                        }
                        if ($colors) {
                            $formatted .= "🎨 Màu sắc: {$colors}\n";
                        }
                        
                        $totalStock = 0;
                        $stockDetails = [];
                        foreach ($product['variants'] as $variant) {
                            if (isset($variant['inventory'])) {
                                $stock = $variant['inventory']['quantity'] ?? 0;
                                $totalStock += $stock;
                                if ($stock > 0) {
                                    $stockDetails[] = "{$variant['size']} ({$variant['color']}): {$stock}";
                                }
                            }
                        }
                        
                        $isSimpleStockQuestion = $this->isSimpleStockQuestion($userMessage);
                        
                        if ($totalStock > 0) {
                            if ($isSimpleStockQuestion) {
                                $formatted .= "📦 Tình trạng: Còn hàng\n";
                            } else {
                                $formatted .= "📦 Tình trạng: Còn hàng ({$totalStock} sản phẩm)\n";
                                if (!empty($stockDetails)) {
                                    $formatted .= "📊 Chi tiết tồn kho: " . implode(', ', $stockDetails) . "\n";
                                }
                            }
                        } else {
                            $formatted .= "📦 Tình trạng: Hết hàng\n";
                        }
                    }
                }
                
                if (is_object($product)) {
                    if ($product->brand) {
                        $formatted .= "🏢 Thương hiệu: {$product->brand->name}\n";
                    }
                } else {
                    if (isset($product['brand']) && isset($product['brand']['name'])) {
                        $formatted .= "🏢 Thương hiệu: {$product['brand']['name']}\n";
                    }
                }
                
                
                $description = is_object($product) ? $product->description : ($product['description'] ?? null);
                if ($description) {
                    $shortDesc = substr($description, 0, 100);
                    $formatted .= "📝 Mô tả: {$shortDesc}...\n";
                }
                
                $formatted .= "---\n";
            }
        }
        
        if (isset($context['coupons']) && $context['coupons']->count() > 0) {
            $formatted .= "🎫 MÃ GIẢM GIÁ HIỆN CÓ:\n";
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
            $formatted .= "⚡ FLASH SALE ĐANG DIỄN RA:\n";
            foreach ($context['flash_sales'] as $flashSale) {
                $formatted .= "• {$flashSale->name}\n";
                $formatted .= "  Thời gian: {$flashSale->start_time} - {$flashSale->end_time}\n";
                if ($flashSale->description) {
                    $formatted .= "  Mô tả: {$flashSale->description}\n";
                }
                $formatted .= "---\n";
            }
        }
        
        if (isset($context['products']) && 
            ((is_object($context['products']) && $context['products']->count() > 0) || 
             (is_array($context['products']) && count($context['products']) > 0))) {
            
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
        
        $flashSaleKeywords = [
            'flash sale', 'flashsale', 'khuyến mãi', 'sale', 'giảm giá', 'khuyến mãi gì', 
            'có sale không', 'có khuyến mãi không', 'flash sale nào', 'sale gì'
        ];
        $isFlashSaleQuestion = false;
        foreach ($flashSaleKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $isFlashSaleQuestion = true;
                break;
            }
        }
        
        $couponKeywords = [
            'mã giảm', 'coupon', 'mã khuyến mãi', 'mã giảm giá', 'có mã giảm không',
            'mã giảm nào', 'coupon nào', 'mã khuyến mãi nào', 'giảm giá gì'
        ];
        $isCouponQuestion = false;
        foreach ($couponKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $isCouponQuestion = true;
                break;
            }
        }
        
        $generalKeywords = ['tìm sản phẩm', 'mua sản phẩm', 'có gì bán', 'sản phẩm nào', 'mua gì', 'tìm gì', 'có gì'];
        $isGeneralSearch = false;
        foreach ($generalKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $isGeneralSearch = true;
                break;
            }
        }
        
        $stockKeywords = ['còn hàng', 'có hàng', 'tồn kho', 'số lượng', 'bao nhiêu cái'];
        $isStockQuestion = false;
        foreach ($stockKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $isStockQuestion = true;
                break;
            }
        }
        
        
        if ($isFlashSaleQuestion && (strlen($response) < 100 || strpos($response, 'không biết') !== false)) {
            $response = "⚡ Flash Sale đang diễn ra:\n\n";
            $response .= "🔥 Nhiều khuyến mãi hấp dẫn\n";
            $response .= "⏰ Thời gian có hạn\n";
            $response .= "💰 Giá cả cực tốt\n\n";
            $response .= "Thông tin chi tiết đã được hiển thị bên dưới!";
        }
        
        if ($isCouponQuestion && (strlen($response) < 100 || strpos($response, 'không biết') !== false)) {
            $response = "🎫 Mã giảm giá hiện có:\n\n";
            $response .= "💎 Nhiều mã giảm giá hấp dẫn\n";
            $response .= "💰 Tiết kiệm đáng kể\n";
            $response .= "📱 Dễ dàng sử dụng\n\n";
            $response .= "Thông tin chi tiết đã được hiển thị bên dưới!";
        }
        
        if ($isStockQuestion && (strlen($response) < 100 || strpos($response, 'không biết') !== false)) {
            $response = "Tôi đã kiểm tra tình trạng tồn kho cho bạn:\n\n";
            $response .= "📦 Thông tin tồn kho đã được hiển thị bên dưới\n";
            $response .= "📊 Bạn có thể xem chi tiết số lượng theo từng size và màu\n";
            $response .= "✅ Nếu còn hàng: sẽ hiển thị số lượng cụ thể\n";
            $response .= "❌ Nếu hết hàng: sẽ hiển thị 'Hết hàng'";
        }
        
        if ($isGeneralSearch && (strlen($response) < 100 || strpos($response, 'bạn muốn tìm gì') !== false)) {
            $response = "Tôi tìm thấy một số sản phẩm tiêu biểu trong cửa hàng:\n\n";
            $response .= "📦 Sản phẩm chất lượng cao\n";
            $response .= "💰 Giá cả cạnh tranh\n";
            $response .= "🏷️ Nhiều khuyến mãi hấp dẫn\n\n";
            $response .= "Thông tin chi tiết đã được hiển thị bên dưới!";
        }
        
        if (strlen($response) < 50) {
            if ($isFlashSaleQuestion) {
                $response .= "\n\nBạn có thể hỏi tôi về:\n- Flash sale cụ thể\n- Thời gian khuyến mãi\n- Mô tả chi tiết";
            } elseif ($isCouponQuestion) {
                $response .= "\n\nBạn có thể hỏi tôi về:\n- Mã giảm giá cụ thể\n- Điều kiện sử dụng\n- Giá trị giảm";
            } else {
                $response .= "\n\nBạn có thể hỏi tôi về:\n- Sản phẩm cụ thể\n- Mã giảm giá\n- Flash sale\n- Quy trình thanh toán\n- Danh mục sản phẩm\n- Tình trạng tồn kho";
            }
        }
        
        return $response;
    }

    private function getRelevantContext($userMessage, $contextHints = [])
    {
        try {
            $context = [];
            $message = strtolower($userMessage);
            
            $productQuery = Products::with(['categories', 'brand', 'mainImage', 'variants.inventory', 'images'])
                ->where('is_active', true);
            
            $flashSaleKeywords = [
                'flash sale', 'flashsale', 'khuyến mãi', 'sale', 'giảm giá', 'khuyến mãi gì', 
                'có sale không', 'có khuyến mãi không', 'flash sale nào', 'sale gì'
            ];
            $isFlashSaleQuestion = false;
            foreach ($flashSaleKeywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $isFlashSaleQuestion = true;
                    break;
                }
            }
            
            if ($isFlashSaleQuestion) {
                $context['flash_sales'] = FlashSale::with(['products.product'])
                    ->where('active', true)
                    ->where('end_time', '>', now())
                    ->take(5)->get();
                return $context; 
            }
            
            $couponKeywords = [
                'mã giảm', 'coupon', 'mã khuyến mãi', 'mã giảm giá', 'có mã giảm không',
                'mã giảm nào', 'coupon nào', 'mã khuyến mãi nào', 'giảm giá gì'
            ];
            $isCouponQuestion = false;
            foreach ($couponKeywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $isCouponQuestion = true;
                    break;
                }
            }
            
            if ($isCouponQuestion) {
                $context['coupons'] = Coupons::where('is_active', true)
                    ->where('end_date', '>', now())
                    ->take(5)->get();
                return $context; 
            }
            
            // Kiểm tra câu hỏi về giới tính cụ thể
            $genderKeywords = [
                'nam' => ['nam', 'đàn ông', 'con trai', 'anh', 'chàng'],
                'nữ' => ['nữ', 'đàn bà', 'con gái', 'chị', 'cô', 'váy', 'đầm']
            ];
            
            $targetGender = null;
            foreach ($genderKeywords as $gender => $keywords) {
                foreach ($keywords as $keyword) {
                    if (strpos($message, $keyword) !== false) {
                        $targetGender = $gender;
                        break 2;
                    }
                }
            }
            
            // Nếu có yêu cầu về giới tính cụ thể, lọc sản phẩm theo giới tính
            if ($targetGender) {
                $filteredQuery = clone $productQuery;
                if ($targetGender === 'nam') {
                    $filteredQuery->where('name', 'like', '%nam%')
                                  ->orWhere('name', 'like', '%áo%')
                                  ->orWhere('name', 'like', '%quần%')
                                  ->orWhere('name', 'like', '%giày%');
                } elseif ($targetGender === 'nữ') {
                    $filteredQuery->where('name', 'like', '%nữ%')
                                  ->orWhere('name', 'like', '%váy%')
                                  ->orWhere('name', 'like', '%đầm%')
                                  ->orWhere('name', 'like', '%áo%')
                                  ->orWhere('name', 'like', '%quần%');
                }
                
                $genderProducts = $filteredQuery->take(3)->get();
                if ($genderProducts->count() > 0) {
                    $context['products'] = $genderProducts;
                    return $context;
                }
            }
            
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
            
            if (strpos($message, 'còn hàng') !== false || strpos($message, 'có hàng') !== false || 
                strpos($message, 'tồn kho') !== false || strpos($message, 'số lượng') !== false) {
                
                if (!empty($contextHints) && isset($contextHints['product_ids']) && is_array($contextHints['product_ids'])) {
                    $ids = array_filter($contextHints['product_ids']);
                    if (!empty($ids)) {
                        $productsByIds = (clone $productQuery)->whereIn('id', $ids)->get();
                        if ($productsByIds->count() > 0) {
                            $context['products'] = $productsByIds;
                            return $context;
                        }
                    }
                }

                $specificProductSearch = $this->searchBySpecificProduct($message, (clone $productQuery));
                if ($specificProductSearch) {
                    $context['products'] = $specificProductSearch;
                    $context['products']->each(function ($product) {
                        if ($product->variants && $product->variants->count() > 0) {
                            $totalStock = 0;
                            $stockDetails = [];
                            
                            foreach ($product->variants as $variant) {
                                if ($variant->inventory) {
                                    $stock = $variant->inventory->quantity ?? 0;
                                    $totalStock += $stock;
                                    if ($stock > 0) {
                                        $stockDetails[] = [
                                            'size' => $variant->size,
                                            'color' => $variant->color,
                                            'quantity' => $stock
                                        ];
                                    }
                                }
                            }
                            
                            $product->total_stock = $totalStock;
                            $product->stock_details = $stockDetails;
                            $product->in_stock = $totalStock > 0;
                        }
                    });
                    return $context;
                }
                
                $categoryProducts = $this->searchByCategory($message, (clone $productQuery));
                if ($categoryProducts) {
                    $context['products'] = $categoryProducts;
                    $context['products']->each(function ($product) {
                        if ($product->variants && $product->variants->count() > 0) {
                            $totalStock = 0;
                            $stockDetails = [];
                            
                            foreach ($product->variants as $variant) {
                                if ($variant->inventory) {
                                    $stock = $variant->inventory->quantity ?? 0;
                                    $totalStock += $stock;
                                    if ($stock > 0) {
                                        $stockDetails[] = [
                                            'size' => $variant->size,
                                            'color' => $variant->color,
                                            'quantity' => $stock
                                        ];
                                    }
                                }
                            }
                            
                            $product->total_stock = $totalStock;
                            $product->stock_details = $stockDetails;
                            $product->in_stock = $totalStock > 0;
                        }
                    });
                    return $context;
                }
            }
            
            $specificProductSearch = $this->searchBySpecificProduct($message, (clone $productQuery));
            if ($specificProductSearch) {
                $context['products'] = $specificProductSearch;
                return $context;
            }
            
            $categoryProducts = $this->searchByCategory($message, (clone $productQuery));
            if ($categoryProducts) {
                $context['products'] = $categoryProducts;
            }
            
            return $context;
        } catch (\Exception $e) {
            \Log::error('getRelevantContext Error: ' . $e->getMessage());
            return [];
        }
    }

    private function isSimpleStockQuestion($userMessage)
    {
        $message = strtolower($userMessage);
        $simpleStockKeywords = ['còn hàng không', 'có hàng không', 'còn hàng ạ', 'có hàng ạ'];
        
        foreach ($simpleStockKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    private function processProductImages(&$context)
    {
        if (isset($context['products']) && $context['products']->count() > 0) {
            $context['products']->each(function ($product) {
                if ($product->mainImage && $product->mainImage->image_path) {
                    $imagePath = $product->mainImage->image_path;
                    if (!str_starts_with($imagePath, 'storage/')) {
                        $imagePath = 'storage/' . ltrim($imagePath, '/');
                    }
                    $product->mainImage->image_url = url($imagePath);
                }
            });
            
            $context['products'] = $context['products']->map(function ($product) {
                $productArray = $product->toArray();
                if (isset($productArray['main_image'])) {
                    $productArray['mainImage'] = $productArray['main_image'];
                    unset($productArray['main_image']);
                }
                return $productArray;
            });
        }
    }

    private function searchBySpecificProduct($message, $productQuery)
    {
        $words = explode(' ', $message);
        
        $stopWords = ['tôi', 'muốn', 'mua', 'cần', 'tìm', 'có', 'ạ', 'à', 'và', 'hoặc', 'này', 'đó', 'kia'];
        $keywords = array_diff($words, $stopWords);
        
        if (strpos($message, 'còn hàng') !== false || strpos($message, 'có hàng') !== false || 
            strpos($message, 'tồn kho') !== false || strpos($message, 'số lượng') !== false) {
            
            if (count($keywords) <= 1) {
                $commonProductTypes = ['áo', 'quần', 'giày', 'túi', 'váy', 'đầm'];
                foreach ($commonProductTypes as $type) {
                    if (strpos($message, $type) !== false) {
                        $productQuery->where('name', 'like', "%{$type}%");
                        break;
                    }
                }
                
                if (count($keywords) == 0) {
                    $products = $productQuery->orderBy('created_at', 'desc')->take(1)->get();
                    if ($products->count() > 0) {
                        return $products;
                    }
                }
            }
        }
        
        if (empty($keywords)) {
            return null;
        }
        
        foreach ($keywords as $keyword) {
            $keyword = trim($keyword);
            if (strlen($keyword) >= 2) { 
                $productQuery->where('name', 'like', "%{$keyword}%");
            }
        }
        
        $products = $productQuery->get();
        
        if ($products->count() > 0) {
            return $products;
        }
        
        return null;
    }

    private function searchByCategory($message, $productQuery)
    {
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
        } else {
            return null;
        }
        
        return $productQuery->inRandomOrder()->take(3)->get();
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
