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

            // === XỬ LÝ LOGIC HIỂN THỊ DỰA TRÊN LOẠI CÂU HỎI ===
            $lowerMsg = strtolower($userMessage);
            
            // Kiểm tra câu hỏi chung chung (KHÔNG liên quan đến sản phẩm)
            $generalInfoKeywords = [
                'cách thanh toán', 'thanh toán', 'payment', 'hướng dẫn', 'hướng dẫn mua hàng',
                'quy trình', 'quy trình mua hàng', 'mua hàng như thế nào', 'đặt hàng',
                'thông tin shop', 'thông tin cửa hàng', 'về shop', 'về cửa hàng',
                'chính sách', 'chính sách đổi trả', 'đổi trả', 'hoàn tiền',
                'vận chuyển', 'shipping', 'phí vận chuyển', 'thời gian giao hàng',
                'liên hệ', 'hotline', 'email', 'địa chỉ', 'giờ làm việc',
                'bảo mật', 'quyền riêng tư', 'điều khoản', 'điều kiện sử dụng'
            ];
            
            $isGeneralInfoQuestion = false;
            foreach ($generalInfoKeywords as $keyword) {
                if (strpos($lowerMsg, $keyword) !== false) {
                    $isGeneralInfoQuestion = true;
                    break;
                }
            }

            // Nếu là câu hỏi thông tin chung, KHÔNG hiển thị sản phẩm
            if ($isGeneralInfoQuestion) {
                $filteredContext = [
                    'products' => collect([]),
                    'coupons' => collect([]),
                    'flash_sales' => collect([]),
                    'categories' => collect([]),
                    'brands' => collect([])
                ];
            } else {
                // Xử lý các loại câu hỏi khác
                $flashSaleKeywords = ['flash sale', 'flashsale', 'khuyến mãi', 'sale', 'khuyến mãi gì', 'có sale không', 'có khuyến mãi không', 'flash sale nào', 'sale gì'];
                $couponKeywords = ['mã giảm', 'coupon', 'mã khuyến mãi', 'mã giảm giá', 'có mã giảm không', 'mã giảm nào', 'coupon nào', 'mã khuyến mãi nào'];
                $productKeywords = ['tìm sản phẩm', 'mua sản phẩm', 'có gì bán', 'sản phẩm nào', 'mua gì', 'tìm gì', 'có gì', 'bán gì', 'shop có gì', 'áo', 'quần', 'váy', 'giày', 'túi', 'đầm', 'áo polo', 'áo khoác', 'áo sơ mi', 'quần jean', 'quần tây', 'váy đầm', 'giày nam', 'giày nữ', 'túi xách'];

                $isFlashSaleQuestion = false;
                foreach ($flashSaleKeywords as $kw) {
                    if (strpos($lowerMsg, $kw) !== false) {
                        $isFlashSaleQuestion = true;
                        break;
                    }
                }
                $isCouponQuestion = false;
                foreach ($couponKeywords as $kw) {
                    if (strpos($lowerMsg, $kw) !== false) {
                        $isCouponQuestion = true;
                        break;
                    }
                }
                $isProductQuestion = false;
                foreach ($productKeywords as $kw) {
                    if (strpos($lowerMsg, $kw) !== false) {
                        $isProductQuestion = true;
                        break;
                    }
                }

                // Lọc context dựa trên loại câu hỏi
                $filteredContext = [];

                if ($isFlashSaleQuestion) {
                    // Chỉ hiển thị flash sale khi hỏi về flash sale
                    if (isset($relevantContext['flash_sales'])) {
                        $filteredContext['flash_sales'] = $relevantContext['flash_sales'];
                    }
                    // Đảm bảo KHÔNG có coupons và products trong context khi hỏi về flash sale
                    $filteredContext['coupons'] = collect([]);
                    $filteredContext['products'] = collect([]);
                } elseif ($isCouponQuestion) {
                    // Chỉ hiển thị mã giảm giá khi hỏi về mã giảm giá
                    if (isset($relevantContext['coupons'])) {
                        $filteredContext['coupons'] = $relevantContext['coupons'];
                    }
                    // Đảm bảo KHÔNG có flash_sales và products trong context khi hỏi về mã giảm giá
                    $filteredContext['flash_sales'] = collect([]);
                    $filteredContext['products'] = collect([]);
                } elseif ($isProductQuestion) {
                    // Chỉ hiển thị sản phẩm khi hỏi về sản phẩm
                    if (isset($relevantContext['products'])) {
                        $filteredContext['products'] = $relevantContext['products'];
                    }
                    // Đảm bảo KHÔNG có flash_sales và coupons trong context khi hỏi về sản phẩm
                    $filteredContext['flash_sales'] = collect([]);
                    $filteredContext['coupons'] = collect([]);
                } else {
                    // Nếu không phải câu hỏi cụ thể, KHÔNG hiển thị gì
                    $filteredContext['products'] = collect([]);
                    $filteredContext['coupons'] = collect([]);
                    $filteredContext['flash_sales'] = collect([]);
                }
            }

            // Gọi AI với context đã được lọc
            $prompt = $this->buildPrompt($userMessage, $filteredContext);
            $response = $this->callGeminiAPI($prompt);
            $aiResponse = $this->processAIResponse($response, $userMessage);

            // Sử dụng context đã lọc cho frontend
            $finalContext = $filteredContext;
            
            // Đảm bảo chỉ hiển thị sản phẩm thực sự có trong database
            if (isset($finalContext['products']) && $finalContext['products']->count() > 0) {
                // Kiểm tra xem sản phẩm có thực sự tồn tại trong database không
                $validProductIds = $finalContext['products']->pluck('id')->toArray();
                $validProducts = Products::whereIn('id', $validProductIds)
                    ->where('is_active', true)
                    ->with(['categories', 'brand', 'mainImage', 'variants.inventory', 'images'])
                    ->get();
                
                if ($validProducts->count() > 0) {
                    $finalContext['products'] = $validProducts;
                } else {
                    // Nếu không có sản phẩm hợp lệ, xóa context sản phẩm
                    $finalContext['products'] = collect([]);
                }
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
                ->orderBy('created_at', 'desc')
                ->take(50)
                ->get();

            $coupons = Coupons::where('is_active', true)
                ->where('end_date', '>', now())
                ->where(function ($query) {
                    $query->whereNull('usage_limit')
                        ->orWhereRaw('used_count < usage_limit');
                })
                ->orderBy('created_at', 'desc')
                ->take(20)
                ->get();

            $flashSales = FlashSale::with(['products.product'])
                ->where('active', true)
                ->where('end_time', '>', now())
                ->orderBy('created_at', 'desc')
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

**KHI KHÁCH HÀNG HỎI VỀ THÔNG TIN CHUNG (KHÔNG LIÊN QUAN ĐẾN SẢN PHẨM):**
- **KHÔNG BAO GIỜ hiển thị sản phẩm, mã giảm giá, hoặc flash sale**
- Các câu hỏi này bao gồm:
  + Cách thanh toán, quy trình mua hàng, hướng dẫn
  + Thông tin shop, chính sách, đổi trả
  + Vận chuyển, phí ship, thời gian giao hàng
  + Liên hệ, hotline, địa chỉ, giờ làm việc
  + Bảo mật, điều khoản, quyền riêng tư
- Chỉ cung cấp thông tin hướng dẫn, KHÔNG hiển thị sản phẩm

**KHI KHÁCH HÀNG HỎI VỀ FLASH SALE/KHUYẾN MÃI:**
- CHỈ hiển thị thông tin về flash sale và khuyến mãi có sẵn trong database
- KHÔNG hiển thị sản phẩm hoặc mã giảm giá
- KHÔNG BAO GIỜ tự bịa ra thông tin flash sale không có trong database
- Tập trung vào thông tin sale, thời gian, mô tả
- Nếu không có flash sale nào, hãy nói: \"Hiện tại cửa hàng chưa có chương trình flash sale nào.\"

**KHI KHÁCH HÀNG HỎI VỀ MÃ GIẢM GIÁ:**
- CHỈ hiển thị thông tin về mã giảm giá (coupons) có sẵn trong database
- KHÔNG hiển thị sản phẩm hoặc flash sale
- KHÔNG BAO GIỜ tự bịa ra mã giảm giá không có trong database
- Tập trung vào mã code, giá trị giảm, điều kiện sử dụng
- Trả lời: \"Chào bạn! Hiện tại cửa hàng đang có các mã giảm giá sau:\" rồi liệt kê CHÍNH XÁC các mã giảm giá có sẵn trong database của cửa hàng.
- Nếu không có mã giảm giá nào, hãy nói: \"Hiện tại cửa hàng chưa có mã giảm giá nào.\" 

**KHI KHÁCH HÀNG HỎI VỀ SẢN PHẨM:**
- **QUAN TRỌNG NHẤT: CHỈ hiển thị sản phẩm có sẵn trong context, KHÔNG BAO GIỜ tự bịa ra thông tin sản phẩm**
- Nếu context KHÔNG có sản phẩm, hãy nói: \"Xin lỗi, hiện tại cửa hàng chưa có sản phẩm này.\"
- Nếu context có sản phẩm, chỉ hiển thị thông tin CHÍNH XÁC từ database
- KHÔNG hiển thị flash sale hoặc mã giảm giá
- Tập trung vào thông tin sản phẩm: tên, giá, size, màu, tồn kho

**QUAN TRỌNG VỀ TÌM KIẾM SẢN PHẨM:**

1. **Khi khách hàng hỏi chung chung như 'tìm sản phẩm', 'mua sản phẩm', 'có gì bán'**: 
   - **CHỈ hiển thị sản phẩm có sẵn trong context**
   - Nếu context không có sản phẩm, hãy nói: \"Hiện tại cửa hàng chưa có sản phẩm nào.\"
   - KHÔNG tự bịa ra thông tin sản phẩm

2. **Khi khách hàng nói cụ thể như 'áo polo vải mềm', 'áo khoác nam', 'quần jean nữ'**: 
   - **CHỈ HIỂN THỊ** sản phẩm có sẵn trong context phù hợp với yêu cầu
   - Nếu context không có sản phẩm phù hợp, hãy nói: \"Xin lỗi, hiện tại cửa hàng chưa có sản phẩm này.\"
   - **KHÔNG** hiển thị sản phẩm không có trong context

3. **Khi khách hàng nói chung như 'áo khoác', 'áo polo', 'quần jean'**: 
   - **CHỈ hiển thị sản phẩm có sẵn trong context**
   - Nếu context không có sản phẩm, hãy nói: \"Xin lỗi, hiện tại cửa hàng chưa có sản phẩm này.\"
   - KHÔNG tự bịa ra thông tin sản phẩm

4. **QUAN TRỌNG VỀ HIỂN THỊ SẢN PHẨM**:
   - **CHỈ hiển thị sản phẩm có sẵn trong context**
   - **KHÔNG BAO GIỜ tự bịa ra tên sản phẩm, giá cả, size, màu sắc**
   - Nếu context trống hoặc không có sản phẩm, hãy nói rõ ràng: \"Hiện tại cửa hàng chưa có sản phẩm này.\"
   - Nếu context có sản phẩm, chỉ hiển thị thông tin CHÍNH XÁC từ database

5. **Cách trả lời mẫu cho sản phẩm**:
   'Tôi tìm thấy [số lượng] sản phẩm phù hợp với yêu cầu của bạn:

   📦 [Tên sản phẩm - CHÍNH XÁC từ database]
   💰 Giá: [Giá gốc - CHÍNH XÁC từ database] VNĐ
   🏷️ Giảm giá: [Giá khuyến mãi - CHÍNH XÁC từ database] VNĐ (nếu có)
   📂 Danh mục: [Tên danh mục - CHÍNH XÁC từ database]
   📏 Size: [Các size có sẵn - CHÍNH XÁC từ database]
   🎨 Màu: [Các màu có sẵn - CHÍNH XÁC từ database]
   📦 Tình trạng: [Còn hàng/Hết hàng] ([Số lượng - CHÍNH XÁC từ database] sản phẩm)
   📊 Chi tiết tồn kho: [Size (Màu): Số lượng - CHÍNH XÁC từ database] (nếu có)
   📝 Mô tả: [Mô tả - CHÍNH XÁC từ database]'

6. **LƯU Ý QUAN TRỌNG**:
   - **KHÔNG BAO GIỜ tự bịa ra thông tin sản phẩm không có trong context**
   - **CHỈ sử dụng thông tin có sẵn trong context**
   - Nếu context trống, hãy nói rõ ràng: \"Hiện tại cửa hàng chưa có sản phẩm này.\"
   - **KHÔNG hiển thị URL hình ảnh trong text trả lời**
   - Hình ảnh sẽ được hiển thị tự động bên dưới thông qua ProductCard

7. **XỬ LÝ TRƯỜNG HỢP KHÔNG CÓ SẢN PHẨM**:
   - Nếu khách hàng hỏi về sản phẩm cụ thể mà context không có:
     + Hãy nói: \"Xin lỗi, hiện tại cửa hàng chưa có sản phẩm này.\"
     + KHÔNG tự bịa ra thông tin sản phẩm
     + Có thể gợi ý: \"Bạn có thể xem các sản phẩm khác có sẵn hoặc liên hệ với chúng tôi để được tư vấn thêm.\"";

        $contextData = $this->formatContextForPrompt($context, $userMessage);

        $contextInstruction = "\n\n**HƯỚNG DẪN SỬ DỤNG CONTEXT:**\n";
        $contextInstruction .= "Bạn CHỈ được phép sử dụng thông tin có sẵn trong context bên dưới.\n";
        $contextInstruction .= "KHÔNG BAO GIỜ tự bịa ra thông tin không có trong context.\n";
        $contextInstruction .= "Nếu context trống hoặc không có thông tin phù hợp, hãy nói rõ điều đó.\n";
        $contextInstruction .= "Đặc biệt: Khi hỏi về mã giảm giá, CHỈ liệt kê các mã có sẵn trong context, không tự tạo mã mới.\n";
        $contextInstruction .= "Khi hỏi về flash sale, CHỈ liệt kê các chương trình có sẵn trong context, không tự tạo thông tin mới.\n";
        $contextInstruction .= "Nếu context không có thông tin về mã giảm giá hoặc flash sale, hãy nói rõ ràng: 'Hiện tại cửa hàng chưa có...'\n";
        $contextInstruction .= "KHÔNG BAO GIỜ tự bịa ra mã giảm giá, tên sản phẩm, hoặc thông tin khuyến mãi không có trong context.\n";
        $contextInstruction .= "Ví dụ: Nếu context chỉ có mã 'GIAM10', thì CHỈ liệt kê mã 'GIAM10', không tự tạo mã 'NEWBIE50' hoặc 'FREESHIP20'.\n";
        $contextInstruction .= "Ví dụ: Nếu context chỉ có flash sale 'Sale vui tháng 8', thì CHỈ liệt kê 'Sale vui tháng 8', không tự tạo flash sale khác.\n";
        $contextInstruction .= "\n**QUAN TRỌNG VỀ HIỂN THỊ SẢN PHẨM:**\n";
        $contextInstruction .= "Khi khách hàng hỏi về sản phẩm cụ thể (ví dụ: 'váy', 'áo polo'), CHỈ hiển thị sản phẩm thực sự liên quan.\n";
        $contextInstruction .= "Nếu hỏi về 'váy', CHỈ hiển thị váy, không hiển thị áo, quần, giày.\n";
        $contextInstruction .= "Nếu hỏi về 'áo', CHỈ hiển thị áo, không hiển thị váy, quần, giày.\n";
        $contextInstruction .= "KHÔNG BAO GIỜ hiển thị sản phẩm không liên quan đến câu hỏi của khách hàng.\n";
        $contextInstruction .= "Nếu context không có sản phẩm phù hợp, hãy nói: 'Xin lỗi, hiện tại cửa hàng chưa có sản phẩm này.'\n";

        return $systemPrompt . "\n\n" . $contextInstruction . $contextData . "\n\nKhách hàng: " . $userMessage . "\n\nTrợ lý AI:";
    }

    private function formatContextForPrompt($context, $userMessage = '')
    {
        $formatted = "THÔNG TIN CỬA HÀNG:\n\n";

        if (isset($context['products']) && $context['products']->count() > 0) {
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

        if (isset($context['products']) && $context['products']->count() > 0) {

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
        $prompt = iconv('UTF-8', 'UTF-8//IGNORE', $prompt) ?: '';

        $payload = [
            'contents' => [[
                'parts' => [['text' => $prompt]]
            ]],
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-goog-api-key' => $this->geminiApiKey
        ])->post($this->geminiApiUrl, $payload);

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
            'flash sale',
            'flashsale',
            'khuyến mãi',
            'sale',
            'khuyến mãi gì',
            'có sale không',
            'có khuyến mãi không',
            'flash sale nào',
            'sale gì'
        ];
        $isFlashSaleQuestion = false;
        foreach ($flashSaleKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $isFlashSaleQuestion = true;
                break;
            }
        }

        $couponKeywords = [
            'mã giảm',
            'coupon',
            'mã khuyến mãi',
            'mã giảm giá',
            'có mã giảm không',
            'mã giảm nào',
            'coupon nào',
            'mã khuyến mãi nào',
            'giảm giá gì'
        ];
        $isCouponQuestion = false;
        foreach ($couponKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $isCouponQuestion = true;
                break;
            }
        }

        $productKeywords = [
            'tìm sản phẩm', 'mua sản phẩm', 'có gì bán', 'sản phẩm nào', 'mua gì', 'tìm gì', 'có gì',
            'bán gì', 'shop có gì', 'áo', 'quần', 'váy', 'giày', 'túi', 'đầm', 'áo polo', 'áo khoác',
            'áo sơ mi', 'quần jean', 'quần tây', 'váy đầm', 'giày nam', 'giày nữ', 'túi xách'
        ];
        $isProductQuestion = false;
        foreach ($productKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $isProductQuestion = true;
                break;
            }
        }

        // Kiểm tra xem AI có tự bịa ra thông tin sản phẩm không
        if ($isProductQuestion) {
            // Nếu AI trả lời quá ngắn hoặc có vẻ không chính xác, thêm cảnh báo
            if (strlen($response) < 100) {
                $response .= "\n\n**Lưu ý:** Tôi chỉ hiển thị thông tin sản phẩm có sẵn trong cơ sở dữ liệu. Nếu không có sản phẩm nào phù hợp, điều đó có nghĩa là cửa hàng hiện tại chưa có sản phẩm này.";
            }
            
            // Kiểm tra xem AI có tự bịa ra thông tin không
            $suspiciousPatterns = [
                'áo polo nam cotton usa basic',
                'áo polo nữ form rộng basic', 
                'áo polo thể thao nam coolmax',
                '399,000 vnđ',
                '299,000 vnđ',
                '349,000 vnđ',
                '449,000 vnđ'
            ];
            
            $hasSuspiciousInfo = false;
            foreach ($suspiciousPatterns as $pattern) {
                if (stripos($response, $pattern) !== false) {
                    $hasSuspiciousInfo = true;
                    break;
                }
            }
            
            if ($hasSuspiciousInfo) {
                $response = "Xin lỗi, tôi không thể tìm thấy sản phẩm áo polo phù hợp với yêu cầu của bạn trong cơ sở dữ liệu hiện tại.\n\n";
                $response .= "**Lưu ý quan trọng:** Tôi chỉ hiển thị thông tin sản phẩm thực sự có trong cơ sở dữ liệu của cửa hàng. Tôi không tự bịa ra thông tin sản phẩm.\n\n";
                $response .= "Bạn có thể:\n";
                $response .= "• Xem các sản phẩm khác có sẵn\n";
                $response .= "• Liên hệ với chúng tôi để được tư vấn thêm\n";
                $response .= "• Kiểm tra lại sau khi cửa hàng cập nhật sản phẩm mới";
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

            // === XỬ LÝ CÁC CÂU HỎI CHUNG CHUNG - KHÔNG LIÊN QUAN ĐẾN SẢN PHẨM ===
            $generalInfoKeywords = [
                'cách thanh toán', 'thanh toán', 'payment', 'hướng dẫn', 'hướng dẫn mua hàng',
                'quy trình', 'quy trình mua hàng', 'mua hàng như thế nào', 'đặt hàng',
                'thông tin shop', 'thông tin cửa hàng', 'về shop', 'về cửa hàng',
                'chính sách', 'chính sách đổi trả', 'đổi trả', 'hoàn tiền',
                'vận chuyển', 'shipping', 'phí vận chuyển', 'thời gian giao hàng',
                'liên hệ', 'hotline', 'email', 'địa chỉ', 'giờ làm việc',
                'bảo mật', 'quyền riêng tư', 'điều khoản', 'điều kiện sử dụng'
            ];
            
            $isGeneralInfoQuestion = false;
            foreach ($generalInfoKeywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $isGeneralInfoQuestion = true;
                    break;
                }
            }

            if ($isGeneralInfoQuestion) {
                // KHÔNG hiển thị sản phẩm, coupons, flash sales khi hỏi thông tin chung
                $context['products'] = collect([]);
                $context['coupons'] = collect([]);
                $context['flash_sales'] = collect([]);
                return $context;
            }

            // === XỬ LÝ FLASH SALE ===
            $flashSaleKeywords = [
                'flash sale',
                'flashsale',
                'khuyến mãi',
                'sale',
                'khuyến mãi gì',
                'có sale không',
                'có khuyến mãi không',
                'flash sale nào',
                'sale gì'
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
                    ->orderBy('created_at', 'desc')
                    ->take(5)->get();
                // Đảm bảo KHÔNG có coupons và products trong context khi hỏi về flash sale
                $context['coupons'] = collect([]);
                $context['products'] = collect([]);
                return $context;
            }

            // === XỬ LÝ COUPON ===
            $couponKeywords = [
                'mã giảm',
                'coupon',
                'mã khuyến mãi',
                'mã giảm giá',
                'có mã giảm không',
                'mã giảm nào',
                'coupon nào',
                'mã khuyến mãi nào',
                'giảm giá gì'
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
                    ->where(function ($query) {
                        $query->whereNull('usage_limit')
                            ->orWhereRaw('used_count < usage_limit');
                    })
                    ->orderBy('created_at', 'desc')
                    ->take(5)->get();
                // Đảm bảo KHÔNG có flash_sales và products trong context khi hỏi về mã giảm giá
                $context['flash_sales'] = collect([]);
                $context['products'] = collect([]);
                return $context;
            }

            // === XỬ LÝ CÂU HỎI VỀ SẢN PHẨM ===
            $productKeywords = [
                'tìm sản phẩm', 'mua sản phẩm', 'có gì bán', 'sản phẩm nào', 'mua gì', 'tìm gì', 'có gì',
                'bán gì', 'shop có gì', 'áo', 'quần', 'váy', 'giày', 'túi', 'đầm', 'áo polo', 'áo khoác',
                'áo sơ mi', 'quần jean', 'quần tây', 'váy đầm', 'giày nam', 'giày nữ', 'túi xách'
            ];
            
            $isProductQuestion = false;
            foreach ($productKeywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $isProductQuestion = true;
                    break;
                }
            }

            // Nếu KHÔNG phải câu hỏi về sản phẩm, KHÔNG hiển thị sản phẩm
            if (!$isProductQuestion) {
                $context['products'] = collect([]);
                $context['coupons'] = collect([]);
                $context['flash_sales'] = collect([]);
                return $context;
            }

            // Tìm kiếm sản phẩm cụ thể theo từ khóa
            $specificProducts = $this->searchBySpecificProduct($message, (clone $productQuery));
            
            if ($specificProducts && $specificProducts->count() > 0) {
                // Chỉ hiển thị sản phẩm thực sự tìm thấy trong database
                $context['products'] = $specificProducts;
                $context['coupons'] = collect([]);
                $context['flash_sales'] = collect([]);
                return $context;
            }

            // Nếu không tìm thấy sản phẩm cụ thể, KHÔNG hiển thị gì
            $context['products'] = collect([]);
            $context['coupons'] = collect([]);
            $context['flash_sales'] = collect([]);
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
        $words = explode(' ', strtolower($message));

        $stopWords = ['tôi', 'muốn', 'mua', 'cần', 'tìm', 'có', 'ạ', 'à', 'và', 'hoặc', 'này', 'đó', 'kia', 'ôi', 'cho', 'với', 'trong', 'ngoài', 'trên', 'dưới', 'bên', 'của', 'là', 'thì', 'mà', 'nhưng', 'hoặc', 'vì', 'nên', 'để', 'từ', 'đến', 'tại', 'về', 'theo', 'cùng', 'cả', 'mỗi', 'mọi', 'mấy', 'bao', 'nhiêu'];
        $keywords = array_diff($words, $stopWords);
        
        // Lọc từ khóa có ý nghĩa (độ dài >= 2)
        $keywords = array_filter($keywords, function($keyword) {
            return strlen(trim($keyword)) >= 2;
        });

        // Nếu không có từ khóa có ý nghĩa, không tìm kiếm
        if (empty($keywords)) {
            return null;
        }

        // Tìm kiếm sản phẩm theo từ khóa chính xác
        $foundProducts = collect();

        foreach ($keywords as $keyword) {
            $keyword = trim($keyword);
            
            // Tìm kiếm theo tên sản phẩm
            $productsByName = (clone $productQuery)->where('name', 'like', "%{$keyword}%")->get();
            if ($productsByName->count() > 0) {
                $foundProducts = $foundProducts->merge($productsByName);
            }

            // Tìm kiếm theo danh mục
            $productsByCategory = (clone $productQuery)->whereHas('categories', function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%");
            })->get();
            if ($productsByCategory->count() > 0) {
                $foundProducts = $foundProducts->merge($productsByCategory);
            }

            // Tìm kiếm theo mô tả (nếu có)
            $productsByDescription = (clone $productQuery)->where('description', 'like', "%{$keyword}%")->get();
            if ($productsByDescription->count() > 0) {
                $foundProducts = $foundProducts->merge($productsByDescription);
            }
        }

        // Loại bỏ sản phẩm trùng lặp và chỉ trả về sản phẩm thực sự liên quan
        if ($foundProducts->count() > 0) {
            $uniqueProducts = $foundProducts->unique('id');
            
            // Sắp xếp theo độ liên quan (sản phẩm có nhiều từ khóa khớp sẽ được ưu tiên)
            $scoredProducts = $uniqueProducts->map(function ($product) use ($keywords) {
                $score = 0;
                $productName = strtolower($product->name);
                $categoryName = strtolower($product->categories->name ?? '');
                $description = strtolower($product->description ?? '');
                
                foreach ($keywords as $keyword) {
                    if (strpos($productName, $keyword) !== false) {
                        $score += 3; // Tên sản phẩm có trọng số cao nhất
                    }
                    if (strpos($categoryName, $keyword) !== false) {
                        $score += 2; // Danh mục có trọng số trung bình
                    }
                    if (strpos($description, $keyword) !== false) {
                        $score += 1; // Mô tả có trọng số thấp nhất
                    }
                }
                
                return ['product' => $product, 'score' => $score];
            });
            
            // Sắp xếp theo điểm số giảm dần và chỉ trả về sản phẩm có điểm > 0
            $relevantProducts = $scoredProducts
                ->filter(function ($item) {
                    return $item['score'] > 0;
                })
                ->sortByDesc('score')
                ->pluck('product')
                ->take(5); // Giới hạn tối đa 5 sản phẩm
            
            return $relevantProducts;
        }

        return null;
    }

    private function searchByCategory($message, $productQuery)
    {
        $message = strtolower($message);

        // Tìm kiếm theo danh mục cụ thể
        if (strpos($message, 'áo khoác') !== false) {
            return $productQuery->whereHas('categories', function ($q) {
                $q->where('name', 'like', '%áo khoác%');
            })->take(3)->get();
        } elseif (strpos($message, 'áo polo') !== false) {
            return $productQuery->where('name', 'like', '%áo polo%')->take(3)->get();
        } elseif (strpos($message, 'sơ mi') !== false) {
            return $productQuery->where('name', 'like', '%sơ mi%')
                ->orWhereHas('categories', function ($q) {
                    $q->where('name', 'like', '%sơ mi%');
                })->take(3)->get();
        } elseif (strpos($message, 'váy') !== false) {
            return $productQuery->whereHas('categories', function ($q) {
                $q->where('name', 'like', '%váy%');
            })->take(3)->get();
        } elseif (strpos($message, 'đầm') !== false) {
            return $productQuery->whereHas('categories', function ($q) {
                $q->where('name', 'like', '%đầm%');
            })->take(3)->get();
        } elseif (strpos($message, 'quần') !== false) {
            return $productQuery->whereHas('categories', function ($q) {
                $q->where('name', 'like', '%quần%');
            })->take(3)->get();
        } elseif (strpos($message, 'giày') !== false) {
            return $productQuery->whereHas('categories', function ($q) {
                $q->where('name', 'like', '%giày%');
            })->take(3)->get();
        } elseif (strpos($message, 'dép') !== false) {
            return $productQuery->whereHas('categories', function ($q) {
                $q->where('name', 'like', '%dép%');
            })->take(3)->get();
        } elseif (strpos($message, 'túi') !== false) {
            return $productQuery->whereHas('categories', function ($q) {
                $q->where('name', 'like', '%túi%');
            })->take(3)->get();
        } elseif (strpos($message, 'áo') !== false && !strpos($message, 'áo khoác') && !strpos($message, 'áo polo') && !strpos($message, 'sơ mi')) {
            return $productQuery->whereHas('categories', function ($q) {
                $q->where('name', 'like', '%áo%');
            })->take(3)->get();
        }

        return null;
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
            ->where(function ($query) {
                $query->whereNull('usage_limit')
                    ->orWhereRaw('used_count < usage_limit');
            })
            ->orderBy('created_at', 'desc')
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
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'flash_sales' => $flashSales
        ]);
    }
}
