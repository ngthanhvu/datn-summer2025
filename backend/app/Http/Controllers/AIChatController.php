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

            $lowerMsg = strtolower($userMessage);
            
            $generalInfoKeywords = [
                'cách thanh toán', 'thanh toán', 'payment', 'hướng dẫn', 'hướng dẫn mua hàng',
                'quy trình', 'quy trình mua hàng', 'mua hàng như thế nào', 'đặt hàng',
                'thông tin shop', 'thông tin cửa hàng', 'về shop', 'về cửa hàng',
                'chính sách', 'chính sách đổi trả', 'đổi trả', 'hoàn tiền',
                'vận chuyển', 'shipping', 'phí vận chuyển', 'thời gian giao hàng',
                'liên hệ', 'hotline', 'email', 'địa chỉ', 'giờ làm việc',
                'bảo mật', 'quyền riêng tư', 'điều khoản', 'điều kiện sử dụng',
                'cod', 'vnpay', 'momo', 'phí ship', 'cước phí'
            ];
            
            $isGeneralInfoQuestion = false;
            foreach ($generalInfoKeywords as $keyword) {
                if (strpos($lowerMsg, $keyword) !== false) {
                    $isGeneralInfoQuestion = true;
                    break;
                }
            }

            if ($isGeneralInfoQuestion) {
                $filteredContext = [
                    'products' => collect([]),
                    'coupons' => collect([]),
                    'flash_sales' => collect([]),
                    'categories' => collect([]),
                    'brands' => collect([]),
                    'payment_methods' => collect([]),
                    'shipping_info' => collect([])
                ];
            } else {
                $filteredContext = $relevantContext;
            }

            \Log::info('Context before AI processing:', [
                'products_count' => $filteredContext['products']->count() ?? 0,
                'coupons_count' => $filteredContext['coupons']->count() ?? 0,
                'flash_sales_count' => $filteredContext['flash_sales']->count() ?? 0,
                'user_message' => $userMessage
            ]);

            $prompt = $this->buildPrompt($userMessage, $filteredContext);
            $response = $this->callGeminiAPI($prompt);
            $aiResponse = $this->processAIResponse($response, $userMessage, $filteredContext);

            $finalContext = $filteredContext;
            
            \Log::info('Final context after AI processing:', [
                'products_count' => $finalContext['products']->count() ?? 0,
                'coupons_count' => $finalContext['coupons']->count() ?? 0,
                'flash_sales_count' => $finalContext['flash_sales']->count() ?? 0
            ]);
            
            $this->processProductImages($finalContext);

            \Log::info('Final context after image processing:', [
                'products_count' => $finalContext['products']->count() ?? 0,
                'products_names' => $finalContext['products']->pluck('name')->toArray() ?? []
            ]);

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
- **QUAN TRỌNG NHẤT: CHỈ hiển thị mã giảm giá có sẵn trong context, KHÔNG BAO GIỜ tự bịa ra mã giảm giá**
- Nếu context KHÔNG có mã giảm giá (coupons trống), hãy nói: \"Hiện tại cửa hàng chưa có mã giảm giá nào.\"
- Nếu context có mã giảm giá, chỉ hiển thị thông tin CHÍNH XÁC từ database
- KHÔNG hiển thị sản phẩm hoặc flash sale
- Tập trung vào thông tin mã giảm giá: mã code, giá trị giảm, điều kiện sử dụng

**KHI KHÁCH HÀNG HỎI VỀ THÔNG TIN THANH TOÁN VÀ VẬN CHUYỂN:**
- **HƯỚNG DẪN CHI TIẾT cách thanh toán cho từng phương thức**
- **KHÔNG hardcode phí vận chuyển** vì sử dụng API bên thứ 3
- Tập trung vào hướng dẫn quy trình thanh toán và vận chuyển

**HƯỚNG DẪN THANH TOÁN CHI TIẾT:**

**1. Thanh toán khi nhận hàng (COD):**
- Khách hàng chọn sản phẩm và đặt hàng
- Nhân viên gọi điện xác nhận đơn hàng
- Giao hàng đến địa chỉ khách hàng
- Khách hàng kiểm tra hàng và thanh toán tiền mặt
- Nhận hóa đơn và phiếu bảo hành

**2. Thanh toán qua VnPay:**
- Khách hàng chọn sản phẩm và đặt hàng
- Chọn phương thức thanh toán VnPay
- Hệ thống chuyển hướng đến trang thanh toán VnPay
- Khách hàng nhập thông tin thẻ ngân hàng
- Xác nhận thanh toán và nhận mã giao dịch
- Hàng được giao sau khi xác nhận thanh toán thành công

**3. Thanh toán qua Momo:**
- Khách hàng chọn sản phẩm và đặt hàng
- Chọn phương thức thanh toán Momo
- Quét mã QR hoặc nhập số điện thoại
- Xác nhận thanh toán qua ứng dụng Momo
- Nhận thông báo xác nhận và mã giao dịch
- Hàng được giao sau khi xác nhận thanh toán thành công

**VỀ PHÍ VẬN CHUYỂN:**
- **KHÔNG hardcode phí vận chuyển** vì sử dụng API bên thứ 3
- Phí vận chuyển được tính toán dựa trên:
  + Địa chỉ giao hàng
  + Trọng lượng và kích thước sản phẩm
  + Thời gian giao hàng (giao thường, giao nhanh)
- Khách hàng sẽ thấy phí vận chuyển chính xác khi đặt hàng
- Hướng dẫn khách hàng sử dụng công cụ tính phí ship trên website

**KHI KHÁCH HÀNG HỎI VỀ PHÍ SHIP:**
- Không đưa ra con số cụ thể
- Hướng dẫn: Phí vận chuyển được tính toán dựa trên địa chỉ giao hàng và loại sản phẩm. Bạn có thể sử dụng công cụ tính phí ship trên website hoặc liên hệ với chúng tôi để được tư vấn cụ thể.

**XỬ LÝ TRƯỜNG HỢP KHÔNG CÓ MÃ GIẢM GIÁ:**
- Nếu khách hàng hỏi về mã giảm giá mà context không có:
  + Hãy nói rõ ràng: \"Hiện tại cửa hàng chưa có mã giảm giá nào.\"
  + KHÔNG tự bịa ra mã giảm giá
  + Có thể gợi ý: \"Bạn có thể theo dõi trang web hoặc fanpage để cập nhật mã giảm giá mới.\"

**XỬ LÝ TRƯỜNG HỢP KHÔNG CÓ THÔNG TIN THANH TOÁN:**
- Nếu khách hàng hỏi về thanh toán mà context không có thông tin:
  + Hãy nói: \"Tôi không có thông tin chi tiết về phương thức thanh toán trong cơ sở dữ liệu.\"
  + KHÔNG tự bịa ra thông tin thanh toán
  + Có thể gợi ý: \"Bạn có thể liên hệ với chúng tôi để được tư vấn chi tiết.\"

**KHI KHÁCH HÀNG HỎI VỀ SẢN PHẨM:**
- **QUAN TRỌNG NHẤT: CHỈ hiển thị sản phẩm có sẵn trong context, KHÔNG BAO GIỜ tự bịa ra thông tin sản phẩm**
- Nếu context KHÔNG có sản phẩm, hãy nói: \"Xin lỗi, hiện tại cửa hàng chưa có sản phẩm này.\"
- Nếu context có sản phẩm, chỉ hiển thị thông tin CHÍNH XÁC từ database
- KHÔNG hiển thị flash sale hoặc mã giảm giá
- Tập trung vào thông tin sản phẩm: tên, giá, size, màu, tồn kho

**QUAN TRỌNG VỀ TÌM KIẾM SẢN PHẨM:**

1. **Khi khách hàng hỏi về sản phẩm cụ thể**: 
   - **CHỈ hiển thị sản phẩm có sẵn trong context**
   - Trả lời tự nhiên và trực tiếp về sản phẩm được hỏi
   - Nếu context không có sản phẩm, hãy nói: \"Xin lỗi, hiện tại cửa hàng chưa có sản phẩm này.\"
   - KHÔNG tự bịa ra thông tin sản phẩm

2. **Khi khách hàng hỏi về thông tin cụ thể (màu sắc, size, tồn kho)**: 
   - **CHỈ HIỂN THỊ** thông tin có sẵn trong context
   - Trả lời trực tiếp về thông tin được hỏi
   - Nếu không có thông tin, hãy nói rõ ràng: \"Tôi không có thông tin về [thông tin được hỏi] trong cơ sở dữ liệu.\"

3. **Cách trả lời tự nhiên cho sản phẩm**:
   - Trả lời trực tiếp và tự nhiên về sản phẩm được hỏi
   - Sử dụng thông tin CHÍNH XÁC từ database
   - Không cần format cứng nhắc, hãy trả lời như một người thật
   - Tập trung vào thông tin người dùng thực sự cần

4. **QUAN TRỌNG VỀ HIỂN THỊ SẢN PHẨM**:
   - **CHỈ hiển thị sản phẩm có sẵn trong context**
   - **KHÔNG BAO GIỜ tự bịa ra tên sản phẩm, giá cả, size, màu sắc**
   - Nếu context trống hoặc không có sản phẩm, hãy nói rõ ràng: \"Hiện tại cửa hàng chưa có sản phẩm này.\"
   - Nếu context có sản phẩm, chỉ hiển thị thông tin CHÍNH XÁC từ database

5. **LƯU Ý QUAN TRỌNG**:
   - **KHÔNG BAO GIỜ tự bịa ra thông tin sản phẩm không có trong context**
   - **CHỈ sử dụng thông tin có sẵn trong context**
   - Nếu context trống, hãy nói rõ ràng: \"Hiện tại cửa hàng chưa có sản phẩm này.\"
   - **KHÔNG hiển thị URL hình ảnh trong text trả lời**
   - Hình ảnh sẽ được hiển thị tự động bên dưới thông qua ProductCard

6. **XỬ LÝ TRƯỜNG HỢP KHÔNG CÓ SẢN PHẨM**:
   - Nếu khách hàng hỏi về sản phẩm cụ thể mà context không có:
     + Hãy nói: \"Xin lỗi, hiện tại cửa hàng chưa có sản phẩm này.\"
     + KHÔNG tự bịa ra thông tin sản phẩm
     + Có thể gợi ý: \"Bạn có thể xem các sản phẩm khác có sẵn hoặc liên hệ với chúng tôi để được tư vấn thêm.\"

7. **TRẢ LỜI TỰ NHIÊN**:
   - Hãy trả lời như một người thật, không cần format cứng nhắc
   - Sử dụng ngôn ngữ tự nhiên, thân thiện
   - Tập trung vào việc giải đáp thắc mắc của khách hàng
   - Không cần phải liệt kê tất cả thông tin nếu không cần thiết

8. **XỬ LÝ SẢN PHẨM HẾT HÀNG**:
   - Nếu sản phẩm được hỏi đã hết hàng:
     + Hãy nói rõ ràng: \"Sản phẩm này đã hết hàng rồi ạ.\"
     + Có thể gợi ý: \"Bạn có thể tham khảo một số sản phẩm tương tự còn hàng.\"
     + CHỈ hiển thị sản phẩm tương tự nếu có trong context
     + KHÔNG tự bịa ra sản phẩm tương tự không có trong database";

        $contextData = $this->formatContextForPrompt($context, $userMessage);

        $contextInstruction = "\n\n**HƯỚNG DẪN SỬ DỤNG CONTEXT:**\n";
        $contextInstruction .= "Bạn CHỈ được phép sử dụng thông tin có sẵn trong context bên dưới.\n";
        $contextInstruction .= "KHÔNG BAO GIỜ tự bịa ra thông tin không có trong context.\n";
        $contextInstruction .= "Nếu context trống hoặc không có thông tin phù hợp, hãy nói rõ điều đó.\n";
        $contextInstruction .= "Đặc biệt: Khi hỏi về mã giảm giá, CHỈ liệt kê các mã có sẵn trong context, không tự tạo mã mới.\n";
        $contextInstruction .= "Khi hỏi về flash sale, CHỈ liệt kê các chương trình có sẵn trong context, không tự tạo thông tin mới.\n";
        $contextInstruction .= "Nếu context không có thông tin về mã giảm giá hoặc flash sale, hãy nói rõ ràng: 'Hiện tại cửa hàng chưa có...'\n";
        $contextInstruction .= "KHÔNG BAO GIỜ tự bịa ra mã giảm giá, tên sản phẩm, hoặc thông tin khuyến mãi không có trong context.\n";
        $contextInstruction .= "\n**QUAN TRỌNG VỀ HIỂN THỊ SẢN PHẨM:**\n";
        $contextInstruction .= "Khi khách hàng hỏi về sản phẩm cụ thể, CHỈ hiển thị sản phẩm thực sự liên quan.\n";
        $contextInstruction .= "KHÔNG BAO GIỜ hiển thị sản phẩm không liên quan đến câu hỏi của khách hàng.\n";
        $contextInstruction .= "Nếu context không có sản phẩm phù hợp, hãy nói: 'Xin lỗi, hiện tại cửa hàng chưa có sản phẩm này.'\n";
        $contextInstruction .= "CHỈ hiển thị thông tin sản phẩm thực sự có trong database, KHÔNG BAO GIỜ tự tạo ra thông tin mới\n";
        $contextInstruction .= "\n**QUY TẮC VÀNG - KHÔNG BAO GIỜ VI PHẠM:**\n";
        $contextInstruction .= "KHÔNG BAO GIỜ tự tạo ra tên sản phẩm mới không có trong context\n";
        $contextInstruction .= "KHÔNG BAO GIỜ tự tạo ra giá cả mới không có trong context\n";
        $contextInstruction .= "KHÔNG BAO GIỜ tự tạo ra thông tin size, màu sắc mới không có trong context\n";
        $contextInstruction .= "KHÔNG BAO GIỜ tự tạo ra thông tin tồn kho mới không có trong context\n";
        $contextInstruction .= "KHÔNG BAO GIỜ tự tạo ra thông tin thương hiệu mới không có trong context\n";
        $contextInstruction .= "KHÔNG BAO GIỜ tự tạo ra mô tả sản phẩm mới không có trong context\n";
        $contextInstruction .= "CHỈ sử dụng thông tin có sẵn trong context từ database\n";
        $contextInstruction .= "Nếu context trống, hãy nói rõ ràng: 'Hiện tại cửa hàng chưa có sản phẩm này.'\n";
        $contextInstruction .= "\n**QUY TẮC HIỂN THỊ SẢN PHẨM:**\n";
        $contextInstruction .= "Khi khách hàng hỏi về sản phẩm cụ thể, CHỈ hiển thị sản phẩm phù hợp nhất\n";
        $contextInstruction .= "Tập trung vào sản phẩm được hỏi, không hiển thị sản phẩm khác\n";
        $contextInstruction .= "Trả lời ngắn gọn và tập trung vào thông tin cần thiết\n";
        $contextInstruction .= "\n**QUAN TRỌNG VỀ TRẢ LỜI:**\n";
        $contextInstruction .= "Khi context có sản phẩm phù hợp, hãy trả lời TỰ NHIÊN và TRỰC TIẾP về sản phẩm đó\n";
        $contextInstruction .= "KHÔNG BAO GIỜ nói 'không tìm thấy' khi context có sản phẩm\n";
        $contextInstruction .= "Nếu context có sản phẩm, hãy mô tả sản phẩm một cách tự nhiên và hữu ích\n";
        $contextInstruction .= "Sử dụng thông tin CHÍNH XÁC từ context để trả lời khách hàng\n";
        $contextInstruction .= "\n**QUY TẮC NGHIÊM NGẶT VỀ THÔNG TIN SẢN PHẨM:**\n";
        $contextInstruction .= "KHÔNG BAO GIỜ tự bịa ra thông tin về thương hiệu (Nike, Adidas, v.v.)\n";
        $contextInstruction .= "KHÔNG BAO GIỜ tự bịa ra thông tin về size, màu sắc cụ thể\n";
        $contextInstruction .= "KHÔNG BAO GIỜ tự bịa ra thông tin về tồn kho cụ thể\n";
        $contextInstruction .= "KHÔNG BAO GIỜ tự bịa ra thông tin về giá cả cụ thể\n";
        $contextInstruction .= "CHỈ sử dụng thông tin có sẵn trong context từ database\n";
        $contextInstruction .= "Nếu context không có thông tin, hãy nói rõ ràng: 'Tôi không có thông tin về [thông tin được hỏi]'\n";
        $contextInstruction .= "\n**QUY TẮC HIỂN THỊ SẢN PHẨM CHÍNH XÁC:**\n";
        $contextInstruction .= "Khi khách hàng hỏi về sản phẩm cụ thể (ví dụ: 'áo polo vải mềm'), CHỈ hiển thị sản phẩm đó\n";
        $contextInstruction .= "KHÔNG BAO GIỜ hiển thị sản phẩm khác không liên quan (ví dụ: áo polo nam khi hỏi áo polo vải mềm)\n";
        $contextInstruction .= "Nếu context chỉ có 1 sản phẩm phù hợp, CHỈ hiển thị sản phẩm đó\n";
        $contextInstruction .= "Nếu context có nhiều sản phẩm, chỉ hiển thị sản phẩm có tên CHÍNH XÁC nhất với câu hỏi\n";
        $contextInstruction .= "KHÔNG BAO GIỜ hiển thị sản phẩm 'tương tự' hoặc 'liên quan' nếu không được yêu cầu cụ thể\n";

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

    private function processAIResponse($aiResponse, $userMessage, $context)
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

        // Xử lý câu hỏi về sản phẩm
        $productKeywords = [
            'áo', 'quần', 'váy', 'đầm', 'giày', 'dép', 'túi', 'polo', 'sơ mi', 'áo khoác',
            'mua', 'tìm', 'cần', 'muốn', 'có', 'sản phẩm', 'hàng'
        ];
        $isProductQuestion = false;
        foreach ($productKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $isProductQuestion = true;
                break;
            }
        }

        // Nếu AI response quá ngắn hoặc không rõ ràng, hãy bổ sung thông tin
        if (strlen($response) < 50) {
            if ($isProductQuestion && isset($context['products']) && $context['products']->count() > 0) {
                $productCount = $context['products']->count();
                if ($productCount === 1) {
                    $product = $context['products']->first();
                    $response = "Tôi đã tìm thấy sản phẩm phù hợp với yêu cầu của bạn:\n\n";
                    $response .= "📦 **{$product->name}**\n";
                    if (isset($product->categories) && $product->categories->name) {
                        $response .= "📂 Danh mục: {$product->categories->name}\n";
                    }
                    if (isset($product->price)) {
                        $response .= "💰 Giá: " . number_format($product->price) . " VNĐ\n";
                    }
                    if (isset($product->discount_price) && $product->discount_price) {
                        $discountPercent = round((($product->price - $product->discount_price) / $product->price) * 100);
                        $response .= "🏷️ Giảm giá: {$discountPercent}% - " . number_format($product->discount_price) . " VNĐ\n";
                    }
                    $response .= "\n✨ Sản phẩm đã được hiển thị bên dưới để bạn xem chi tiết!";
                } else {
                    $response = "Tôi đã tìm thấy {$productCount} sản phẩm phù hợp với yêu cầu của bạn:\n\n";
                    $response .= "✨ Các sản phẩm đã được hiển thị bên dưới\n";
                    $response .= "💡 Bạn có thể xem chi tiết và đặt hàng\n";
                    $response .= "🔍 Cần tìm sản phẩm khác? Hãy cho tôi biết nhé!";
                }
            } elseif ($isProductQuestion && (!isset($context['products']) || $context['products']->count() === 0)) {
                $response = "😔 Xin lỗi, hiện tại cửa hàng chưa có sản phẩm phù hợp với yêu cầu của bạn.\n\n";
                $response .= "💡 Bạn có thể:\n";
                $response .= "• Thử tìm kiếm với từ khóa khác\n";
                $response .= "• Xem các sản phẩm khác có sẵn\n";
                $response .= "• Liên hệ với chúng tôi để được tư vấn thêm";
            } elseif ($isFlashSaleQuestion) {
                if (isset($context['flash_sales']) && $context['flash_sales']->count() > 0) {
                    $response = "⚡ Flash Sale đang diễn ra:\n\n";
                    $response .= "🔥 Nhiều khuyến mãi hấp dẫn\n";
                    $response .= "⏰ Thời gian có hạn\n";
                    $response .= "💰 Giá cả cực tốt\n\n";
                    $response .= "Thông tin chi tiết đã được hiển thị bên dưới!";
                } else {
                    $response = "Hiện tại cửa hàng chưa có chương trình flash sale nào.\n\n";
                    $response .= "Bạn có thể theo dõi trang web hoặc fanpage để cập nhật thông tin mới.";
                }
            } elseif ($isCouponQuestion) {
                if (isset($context['coupons']) && $context['coupons']->count() > 0) {
                    $response = "🎫 Mã giảm giá hiện có:\n\n";
                    $response .= "💎 Nhiều mã giảm giá hấp dẫn\n";
                    $response .= "💰 Tiết kiệm đáng kể\n";
                    $response .= "📱 Dễ dàng sử dụng\n\n";
                    $response .= "Thông tin chi tiết đã được hiển thị bên dưới!";
                } else {
                    $response = "Hiện tại cửa hàng chưa có mã giảm giá nào.\n\n";
                    $response .= "Bạn có thể theo dõi trang web hoặc fanpage để cập nhật mã giảm giá mới.";
                }
            } else {
                $response .= "\n\nBạn có thể hỏi tôi về:\n- Sản phẩm cụ thể\n- Mã giảm giá\n- Flash sale\n- Quy trình thanh toán\n- Danh mục sản phẩm\n- Tình trạng tồn kho";
            }
        }

        $paymentKeywords = [
            'thanh toán', 'payment', 'cod', 'vnpay', 'momo', 'phí ship', 'vận chuyển', 'shipping'
        ];
        $isPaymentQuestion = false;
        foreach ($paymentKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $isPaymentQuestion = true;
                break;
            }
        }

        if ($isPaymentQuestion && (strlen($response) < 100 || strpos($response, 'không biết') !== false)) {
            $response = "💳 **HƯỚNG DẪN THANH TOÁN CHI TIẾT:**\n\n";
            
            $response .= "**1. Thanh toán khi nhận hàng (COD):**\n";
            $response .= "• Chọn sản phẩm và đặt hàng\n";
            $response .= "• Nhân viên gọi điện xác nhận đơn hàng\n";
            $response .= "• Giao hàng đến địa chỉ của bạn\n";
            $response .= "• Kiểm tra hàng và thanh toán tiền mặt\n";
            $response .= "• Nhận hóa đơn và phiếu bảo hành\n\n";
            
            $response .= "**2. Thanh toán qua VnPay:**\n";
            $response .= "• Chọn sản phẩm và đặt hàng\n";
            $response .= "• Chọn phương thức thanh toán VnPay\n";
            $response .= "• Hệ thống chuyển hướng đến trang thanh toán VnPay\n";
            $response .= "• Nhập thông tin thẻ ngân hàng\n";
            $response .= "• Xác nhận thanh toán và nhận mã giao dịch\n";
            $response .= "• Hàng được giao sau khi xác nhận thanh toán thành công\n\n";
            
            $response .= "**3. Thanh toán qua Momo:**\n";
            $response .= "• Chọn sản phẩm và đặt hàng\n";
            $response .= "• Chọn phương thức thanh toán Momo\n";
            $response .= "• Quét mã QR hoặc nhập số điện thoại\n";
            $response .= "• Xác nhận thanh toán qua ứng dụng Momo\n";
            $response .= "• Nhận thông báo xác nhận và mã giao dịch\n";
            $response .= "• Hàng được giao sau khi xác nhận thanh toán thành công\n\n";
            
            $response .= "**📞 Liên hệ hỗ trợ:** Nếu cần tư vấn thêm, vui lòng liên hệ với chúng tôi!";
        }

        $shippingKeywords = [
            'phí ship', 'phí vận chuyển', 'shipping', 'giao hàng', 'cước phí', 'tiền ship'
        ];
        $isShippingQuestion = false;
        foreach ($shippingKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $isShippingQuestion = true;
                break;
            }
        }

        if ($isShippingQuestion && (strlen($response) < 100 || strpos($response, 'không biết') !== false)) {
            $response = "🚚 **THÔNG TIN VỀ PHÍ VẬN CHUYỂN:**\n\n";
            $response .= "**Phí vận chuyển được tính toán dựa trên:**\n";
            $response .= "• Địa chỉ giao hàng cụ thể\n";
            $response .= "• Trọng lượng và kích thước sản phẩm\n";
            $response .= "• Thời gian giao hàng (giao thường, giao nhanh)\n\n";
            $response .= "**💡 Cách biết phí ship chính xác:**\n";
            $response .= "• Sử dụng công cụ tính phí ship trên website\n";
            $response .= "• Hoặc liên hệ với chúng tôi để được tư vấn cụ thể\n\n";
            $response .= "**📞 Liên hệ hỗ trợ:** Để biết phí ship chính xác cho địa chỉ của bạn!";
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

            $generalInfoKeywords = [
                'cách thanh toán', 'thanh toán', 'payment', 'hướng dẫn', 'hướng dẫn mua hàng',
                'quy trình', 'quy trình mua hàng', 'mua hàng như thế nào', 'đặt hàng',
                'thông tin shop', 'thông tin cửa hàng', 'về shop', 'về cửa hàng',
                'chính sách', 'chính sách đổi trả', 'đổi trả', 'hoàn tiền',
                'vận chuyển', 'shipping', 'phí vận chuyển', 'thời gian giao hàng',
                'liên hệ', 'hotline', 'email', 'địa chỉ', 'giờ làm việc',
                'bảo mật', 'quyền riêng tư', 'điều khoản', 'điều kiện sử dụng',
                'cod', 'vnpay', 'momo', 'phí ship', 'cước phí'
            ];
            
            $isGeneralInfoQuestion = false;
            foreach ($generalInfoKeywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $isGeneralInfoQuestion = true;
                    break;
                }
            }

            if ($isGeneralInfoQuestion) {
                $context['products'] = collect([]);
                $context['coupons'] = collect([]);
                $context['flash_sales'] = collect([]);
                
                $databaseContext = $this->getDatabaseContext();
                if (isset($databaseContext['payment_methods'])) {
                    $context['payment_methods'] = collect($databaseContext['payment_methods']);
                }
                if (isset($databaseContext['shipping_info'])) {
                    $context['shipping_info'] = collect($databaseContext['shipping_info']);
                }
                
                return $context;
            }

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
                $context['coupons'] = collect([]);
                $context['products'] = collect([]);
                return $context;
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

            if ($isCouponQuestion) {
                $availableCoupons = Coupons::where('is_active', true)
                    ->where('end_date', '>', now())
                    ->where(function ($query) {
                        $query->whereNull('usage_limit')
                            ->orWhereRaw('used_count < usage_limit');
                    })
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                if ($availableCoupons->count() > 0) {
                    $context['coupons'] = $availableCoupons->take(5);
                } else {
                    $context['coupons'] = collect([]);
                }
                
                $context['flash_sales'] = collect([]);
                $context['products'] = collect([]);
                return $context;
            }

            $specificProducts = $this->searchBySpecificProduct($message, (clone $productQuery));
            
            if ($specificProducts && $specificProducts->count() > 0) {
                $relevantProducts = $this->filterRelevantProducts($specificProducts, $message);
                
                if ($relevantProducts->count() > 0) {
                    $context['products'] = $relevantProducts;
                    $context['coupons'] = collect([]);
                    $context['flash_sales'] = collect([]);
                    
                    \Log::info('Found relevant specific products for query: ' . $message, [
                        'count' => $relevantProducts->count(),
                        'products' => $relevantProducts->pluck('name')->toArray(),
                        'query' => $message
                    ]);
                    
                    return $context;
                }
            }

            $categoryProducts = $this->searchByCategory($message, (clone $productQuery));
            if ($categoryProducts && $categoryProducts->count() > 0) {
                $relevantProducts = $this->filterRelevantProducts($categoryProducts, $message);
                
                if ($relevantProducts->count() > 0) {
                    $context['products'] = $relevantProducts;
                    $context['coupons'] = collect([]);
                    $context['flash_sales'] = collect([]);
                    
                    \Log::info('Found relevant category products for query: ' . $message, [
                        'count' => $relevantProducts->count(),
                        'products' => $relevantProducts->pluck('name')->toArray(),
                        'query' => $message
                    ]);
                    
                    return $context;
                }
            }

            $context['products'] = collect([]);
            $context['coupons'] = collect([]);
            $context['flash_sales'] = collect([]);
            
            \Log::info('No products found for query: ' . $message, [
                'query' => $message,
                'keywords' => $this->extractKeywords($message)
            ]);
            
            return $context;

        } catch (\Exception $e) {
            \Log::error('getRelevantContext Error: ' . $e->getMessage());
            return [];
        }
    }

    private function filterRelevantProducts($products, $userMessage)
    {
        $message = strtolower($userMessage);
        $words = explode(' ', $message);
        
        $stopWords = ['tôi', 'muốn', 'mua', 'cần', 'tìm', 'có', 'ạ', 'à', 'và', 'hoặc', 'này', 'đó', 'kia', 'ôi', 'cho', 'với', 'trong', 'ngoài', 'trên', 'dưới', 'bên', 'của', 'là', 'thì', 'mà', 'nhưng', 'hoặc', 'vì', 'nên', 'để', 'từ', 'đến', 'tại', 'về', 'theo', 'cùng', 'cả', 'mỗi', 'mọi', 'mấy', 'bao', 'nhiêu', 'có', 'màu', 'gì', 'size', 'giá', 'bao', 'nhiêu'];
        $keywords = array_diff($words, $stopWords);
        $keywords = array_filter($keywords, function($keyword) {
            return strlen(trim($keyword)) >= 2;
        });
        
        $phrase = implode(' ', $keywords);
        
        \Log::info("Filtering products for relevance:", [
            'message' => $userMessage,
            'keywords' => $keywords,
            'phrase' => $phrase,
            'total_products' => $products->count()
        ]);
        
        $exactMatches = $products->filter(function ($product) use ($phrase) {
            $productName = strtolower($product->name);
            return strpos($productName, $phrase) !== false;
        });
        
        if ($exactMatches->count() > 0) {
            \Log::info("Found exact phrase matches:", [
                'count' => $exactMatches->count(),
                'products' => $exactMatches->pluck('name')->toArray()
            ]);
            return $exactMatches->take(1); 
        }
        
        $relevantProducts = $products->filter(function ($product) use ($keywords, $phrase, $message) {
            $productName = strtolower($product->name);
            $categoryName = strtolower($product->categories->name ?? '');
            
            if (strpos($productName, $phrase) !== false) {
                \Log::info("Product {$product->name} is relevant (contains phrase: {$phrase})");
                return true;
            }
            
            $keywordMatches = 0;
            foreach ($keywords as $keyword) {
                if (strpos($productName, $keyword) !== false || strpos($categoryName, $keyword) !== false) {
                    $keywordMatches++;
                }
            }
            
            if ($keywordMatches >= 2) {
                \Log::info("Product {$product->name} is relevant (matches {$keywordMatches} keywords)");
                return true;
            }
            
            \Log::info("Product {$product->name} is NOT relevant (insufficient keyword matches)");
            return false;
        });
        
        \Log::info("Filtered products result:", [
            'original_count' => $products->count(),
            'relevant_count' => $relevantProducts->count(),
            'relevant_products' => $relevantProducts->pluck('name')->toArray()
        ]);
        
        return $relevantProducts->take(1); 
    }

    private function extractKeywords($message)
    {
        $words = explode(' ', strtolower($message));
        $stopWords = ['tôi', 'muốn', 'mua', 'cần', 'tìm', 'có', 'ạ', 'à', 'và', 'hoặc', 'này', 'đó', 'kia', 'ôi', 'cho', 'với', 'trong', 'ngoài', 'trên', 'dưới', 'bên', 'của', 'là', 'thì', 'mà', 'nhưng', 'hoặc', 'vì', 'nên', 'để', 'từ', 'đến', 'tại', 'về', 'theo', 'cùng', 'cả', 'mỗi', 'mọi', 'mấy', 'bao', 'nhiêu', 'có', 'màu', 'gì', 'size', 'giá', 'bao', 'nhiêu'];
        $keywords = array_diff($words, $stopWords);
        
        return array_filter($keywords, function($keyword) {
            return strlen(trim($keyword)) >= 2;
        });
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
            \Log::info('Processing product images for ' . $context['products']->count() . ' products');
            
            $context['products']->each(function ($product) {
                if ($product->mainImage && $product->mainImage->image_path) {
                    $imagePath = $product->mainImage->image_path;
                    if (!str_starts_with($imagePath, 'storage/')) {
                        $imagePath = 'storage/' . ltrim($imagePath, '/');
                    }
                    $product->mainImage->image_url = url($imagePath);
                    \Log::info('Set image URL for product: ' . $product->name . ' - ' . $product->mainImage->image_url);
                } else {
                    \Log::info('No main image found for product: ' . $product->name);
                }
            });

            $context['products'] = $context['products']->map(function ($product) {
                $productArray = $product->toArray();
                
                if (isset($productArray['main_image'])) {
                    $productArray['mainImage'] = $productArray['main_image'];
                    unset($productArray['main_image']);
                }
                
                if (!isset($productArray['mainImage'])) {
                    $productArray['mainImage'] = null;
                }
                
                \Log::info('Processed product: ' . $productArray['name'] . ' with mainImage: ' . ($productArray['mainImage'] ? 'yes' : 'no'));
                
                return $productArray;
            });
            
            \Log::info('Finished processing ' . $context['products']->count() . ' products');
        } else {
            \Log::info('No products to process images for');
        }
    }

    private function searchBySpecificProduct($message, $productQuery)
    {
        $words = explode(' ', strtolower($message));

        $stopWords = ['tôi', 'muốn', 'mua', 'cần', 'tìm', 'có', 'ạ', 'à', 'và', 'hoặc', 'này', 'đó', 'kia', 'ôi', 'cho', 'với', 'trong', 'ngoài', 'trên', 'dưới', 'bên', 'của', 'là', 'thì', 'mà', 'nhưng', 'hoặc', 'vì', 'nên', 'để', 'từ', 'đến', 'tại', 'về', 'theo', 'cùng', 'cả', 'mỗi', 'mọi', 'mấy', 'bao', 'nhiêu', 'có', 'màu', 'gì', 'size', 'giá', 'bao', 'nhiêu'];
        $keywords = array_diff($words, $stopWords);

        if (empty($keywords)) {
            \Log::info('No keywords found after filtering stop words');
            return null;
        }

        $keywords = array_filter($keywords, function($keyword) {
            return strlen(trim($keyword)) >= 2;
        });

        \Log::info('Search keywords:', $keywords);

        $foundProducts = collect();
        $phrase = implode(' ', $keywords);

        \Log::info("First trying to search with phrase: {$phrase}");
        
        $productsByPhrase = (clone $productQuery)->where('name', 'like', "%{$phrase}%")->get();
        if ($productsByPhrase->count() > 0) {
            $foundProducts = $foundProducts->merge($productsByPhrase);
            \Log::info("Found {$productsByPhrase->count()} products by phrase in name: {$phrase}", [
                'products' => $productsByPhrase->pluck('name')->toArray()
            ]);
        }
        
        $productsByCategoryPhrase = (clone $productQuery)->whereHas('categories', function ($q) use ($phrase) {
            $q->where('name', 'like', "%{$phrase}%");
        })->get();
        if ($productsByCategoryPhrase->count() > 0) {
            $foundProducts = $foundProducts->merge($productsByCategoryPhrase);
            \Log::info("Found {$productsByCategoryPhrase->count()} products by category phrase: {$phrase}", [
                'products' => $productsByCategoryPhrase->pluck('name')->toArray()
            ]);
        }

        if ($foundProducts->count() === 0) {
            \Log::info("No products found with phrase, trying individual keywords");
            
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                
                $productsByName = (clone $productQuery)->where('name', 'like', "%{$keyword}%")->get();
                if ($productsByName->count() > 0) {
                    $foundProducts = $foundProducts->merge($productsByName);
                    \Log::info("Found {$productsByName->count()} products by name with keyword: {$keyword}", [
                        'products' => $productsByName->pluck('name')->toArray()
                    ]);
                }

                $productsByCategory = (clone $productQuery)->whereHas('categories', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                })->get();
                if ($productsByCategory->count() > 0) {
                    $foundProducts = $foundProducts->merge($productsByCategory);
                    \Log::info("Found {$productsByCategory->count()} products by category with keyword: {$keyword}", [
                        'products' => $productsByCategory->pluck('name')->toArray()
                    ]);
                }
            }
        }

        if ($foundProducts->count() > 0) {
            $uniqueProducts = $foundProducts->unique('id');
            \Log::info("Total unique products found: {$uniqueProducts->count()}", [
                'total_found' => $foundProducts->count(),
                'unique_count' => $uniqueProducts->count(),
                'all_products' => $uniqueProducts->pluck('name')->toArray()
            ]);
            
            $scoredProducts = $uniqueProducts->map(function ($product) use ($keywords, $phrase, $message) {
                $score = 0;
                $productName = strtolower($product->name);
                $categoryName = strtolower($product->categories->name ?? '');
                $productDescription = strtolower($product->description ?? '');
                
                if (strpos($productName, $phrase) !== false) {
                    $score += 100; 
                    \Log::info("Product {$product->name} got +100 points for phrase match in name");
                }
                if (strpos($categoryName, $phrase) !== false) {
                    $score += 80; 
                    \Log::info("Product {$product->name} got +80 points for phrase match in category");
                }
                
                foreach ($keywords as $keyword) {
                    if (strpos($productName, $keyword) !== false) {
                        $score += 30;
                        \Log::info("Product {$product->name} got +30 points for keyword '{$keyword}' in name");
                    }
                    if (strpos($categoryName, $keyword) !== false) {
                        $score += 20; 
                        \Log::info("Product {$product->name} got +20 points for keyword '{$keyword}' in category");
                    }
                }
                
                \Log::info("Product: {$product->name}, Final Score: {$score}");
                
                return ['product' => $product, 'score' => $score];
            });
            
            $relevantProducts = $scoredProducts
                ->filter(function ($item) {
                    return $item['score'] >= 50; 
                })
                ->sortByDesc('score')
                ->pluck('product');
            
            \Log::info("Relevant products after scoring: {$relevantProducts->count()}", [
                'total_scored' => $scoredProducts->count(),
                'filtered_count' => $relevantProducts->count(),
                'products_with_scores' => $scoredProducts->map(function($item) {
                    return ['name' => $item['product']->name, 'score' => $item['score']];
                })->toArray()
            ]);
            
            if ($relevantProducts->count() > 0) {
                $topProduct = $relevantProducts->first();
                \Log::info("Top product: {$topProduct->name} with score: " . $scoredProducts->first()['score']);
                
                // Kiểm tra tồn kho nếu cần
                if (strpos(strtolower($message), 'còn hàng') !== false || 
                    strpos(strtolower($message), 'có hàng') !== false ||
                    strpos(strtolower($message), 'tồn kho') !== false) {
                    if ($topProduct->variants && $topProduct->variants->count() > 0) {
                        $totalStock = 0;
                        foreach ($topProduct->variants as $variant) {
                            if ($variant->inventory) {
                                $totalStock += $variant->inventory->quantity ?? 0;
                            }
                        }
                        if ($totalStock === 0) {
                            \Log::info("Product {$topProduct->name} is out of stock");
                            return collect([]);
                        }
                    }
                }
                
                // Chỉ trả về sản phẩm có điểm cao nhất (liên quan nhất)
                $topProducts = $relevantProducts->take(2); // Giới hạn chỉ 2 sản phẩm tốt nhất
                \Log::info("Returning top relevant products:", [
                    'count' => $topProducts->count(),
                    'products' => $topProducts->pluck('name')->toArray()
                ]);
                return $topProducts;
            } else {
                \Log::info("No products with score >= 50 found");
            }
        }

        \Log::info("No products found for message: {$message}", [
            'message' => $message,
            'keywords' => $keywords,
            'phrase' => $phrase,
            'found_products_count' => $foundProducts->count()
        ]);
        return null;
    }

    private function searchByCategory($message, $productQuery)
    {
        $message = strtolower($message);
        \Log::info("Searching by category for message: {$message}");

        // Tìm kiếm động dựa trên từ khóa trong message
        $words = explode(' ', $message);
        $stopWords = ['tôi', 'muốn', 'mua', 'cần', 'tìm', 'có', 'ạ', 'à', 'và', 'hoặc', 'này', 'đó', 'kia', 'ôi', 'cho', 'với', 'trong', 'ngoài', 'trên', 'dưới', 'bên', 'của', 'là', 'thì', 'mà', 'nhưng', 'hoặc', 'vì', 'nên', 'để', 'từ', 'đến', 'tại', 'về', 'theo', 'cùng', 'cả', 'mỗi', 'mọi', 'mấy', 'bao', 'nhiêu', 'có', 'màu', 'gì', 'size', 'giá', 'bao', 'nhiêu'];
        $keywords = array_diff($words, $stopWords);
        
        $keywords = array_filter($keywords, function($keyword) {
            return strlen(trim($keyword)) >= 2;
        });

        if (empty($keywords)) {
            \Log::info("No keywords found for category search");
            return null;
        }

        \Log::info("Category search keywords:", $keywords);

        $foundProducts = collect();

        foreach ($keywords as $keyword) {
            $keyword = trim($keyword);
            
            // Tìm theo danh mục (chỉ khi từ khóa xuất hiện trong danh mục)
            $productsByCategory = (clone $productQuery)->whereHas('categories', function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%");
            })->get();
            
            if ($productsByCategory->count() > 0) {
                $foundProducts = $foundProducts->merge($productsByCategory);
                \Log::info("Found {$productsByCategory->count()} products by category keyword: {$keyword}", [
                    'products' => $productsByCategory->pluck('name')->toArray()
                ]);
            }
        }

        if ($foundProducts->count() > 0) {
            $uniqueProducts = $foundProducts->unique('id')->take(2); // Giới hạn chỉ 2 sản phẩm
            \Log::info("Returning {$uniqueProducts->count()} category products", [
                'total_found' => $foundProducts->count(),
                'unique_count' => $uniqueProducts->count(),
                'products' => $uniqueProducts->pluck('name')->toArray()
            ]);
            return $uniqueProducts;
        }

        \Log::info("No category products found for keywords:", $keywords);
        return null;
    }

    public function searchProducts(Request $request)
    {
        $query = $request->input('query');

        $products = Products::with(['categories', 'brand', 'mainImage'])
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
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

