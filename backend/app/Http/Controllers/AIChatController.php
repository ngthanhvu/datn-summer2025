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
use App\Models\Orders;
use App\Models\Cart;
use App\Models\Variants;
use App\Models\Inventory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

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
            $sessionId = $request->input('session_id', 'default');

            $contextHints = $request->input('context', []);
            
            $isSimplePurchaseIntent = $this->isPurchaseIntent($userMessage) && 
            $isSimplePurchaseIntent = $this->isPurchaseIntent($userMessage) && 
                                    (trim(strtolower($userMessage)) === 'tôi muốn mua' || 
                                     trim(strtolower($userMessage)) === 'có' || 
                                     trim(strtolower($userMessage)) === 'muốn' ||
                                     trim(strtolower($userMessage)) === 'có tôi muốn mua' ||
                                     trim(strtolower($userMessage)) === 'có ạ' ||
                                     trim(strtolower($userMessage)) === 'dạ có');
            
            if ($isSimplePurchaseIntent && !empty($contextHints)) {
                $relevantContext = $contextHints;
                \Log::info('Using context from request for simple purchase intent', [
                    'message' => $userMessage,
                    'context_products_count' => isset($relevantContext['products']) ? (is_array($relevantContext['products']) ? count($relevantContext['products']) : $relevantContext['products']->count()) : 0
                ]);
            } else {
                $relevantContext = $this->getRelevantContext($userMessage, $contextHints);
                
                if ((!isset($relevantContext['products']) || (is_array($relevantContext['products']) ? count($relevantContext['products']) : $relevantContext['products']->count()) === 0) && !empty($contextHints)) {
                    $relevantContext = $contextHints;
                    \Log::info('Using previous context because no new products found', [
                        'message' => $userMessage,
                        'context_products_count' => isset($relevantContext['products']) ? (is_array($relevantContext['products']) ? count($relevantContext['products']) : $relevantContext['products']->count()) : 0
                    ]);
                }
            }

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
                'products_count' => is_array($filteredContext['products']) ? count($filteredContext['products']) : ($filteredContext['products']->count() ?? 0),
                'coupons_count' => is_array($filteredContext['coupons']) ? count($filteredContext['coupons']) : ($filteredContext['coupons']->count() ?? 0),
                'flash_sales_count' => is_array($filteredContext['flash_sales']) ? count($filteredContext['flash_sales']) : ($filteredContext['flash_sales']->count() ?? 0),
                'user_message' => $userMessage
            ]);

            $prompt = $this->buildPrompt($userMessage, $filteredContext);
            $response = $this->callGeminiAPI($prompt);
            $aiResponse = $this->processAIResponse($response, $userMessage, $filteredContext);

            $finalContext = $filteredContext;
            
            \Log::info('Final context after AI processing:', [
                'products_count' => is_array($finalContext['products']) ? count($finalContext['products']) : ($finalContext['products']->count() ?? 0),
                'coupons_count' => is_array($finalContext['coupons']) ? count($finalContext['coupons']) : ($finalContext['coupons']->count() ?? 0),
                'flash_sales_count' => is_array($finalContext['flash_sales']) ? count($finalContext['flash_sales']) : ($finalContext['flash_sales']->count() ?? 0)
            ]);
            
            $this->processProductImages($finalContext);

            $hasPurchaseIntent = $this->isPurchaseIntent($userMessage);
            
            \Log::info('Final context products count: ' . (isset($finalContext['products']) ? (is_array($finalContext['products']) ? count($finalContext['products']) : $finalContext['products']->count()) : 0));
            
            $aiAskingAboutCart = strpos(strtolower($aiResponse), 'bạn có muốn thêm vào giỏ hàng') !== false ||
                                 strpos(strtolower($aiResponse), 'bạn có muốn thêm sản phẩm') !== false ||
                                 strpos(strtolower($aiResponse), 'bạn có muốn mua') !== false;
            
            $aiAskingForSelection = strpos(strtolower($aiResponse), 'chọn size') !== false ||
                                   strpos(strtolower($aiResponse), 'chọn màu') !== false ||
                                   strpos(strtolower($aiResponse), 'số lượng') !== false ||
                                   strpos(strtolower($aiResponse), 'thêm vào giỏ hàng') !== false;
            
            $aiShowingCoupons = strpos(strtolower($aiResponse), 'mã giảm giá') !== false ||
                               strpos(strtolower($aiResponse), 'coupon') !== false ||
                               strpos(strtolower($aiResponse), 'lưu mã') !== false;
            
            if ($hasPurchaseIntent || $aiAskingAboutCart || $aiAskingForSelection) {
                \Log::info('User has purchase intent OR AI is asking about cart OR AI is asking for selection, showing purchase form', [
                    'has_purchase_intent' => $hasPurchaseIntent,
                    'ai_asking_about_cart' => $aiAskingAboutCart,
                    'ai_asking_for_selection' => $aiAskingForSelection
                ]);
                $finalContext['show_purchase_form'] = true;
            } elseif ($aiShowingCoupons && isset($finalContext['coupons']) && $finalContext['coupons']->count() > 0) {
                \Log::info('AI is showing coupons, enabling save coupon functionality');
                $finalContext['show_save_coupon'] = true;
                $finalContext['show_purchase_form'] = false;
            } else {
                \Log::info('No purchase intent and AI not asking about cart/selection, showing basic product display only');
                $finalContext['show_purchase_form'] = false;
                $finalContext['show_save_coupon'] = false;
            }
            
            \Log::info('Final product display logic:', [
                'user_message' => $userMessage,
                'has_purchase_intent' => $hasPurchaseIntent,
                'ai_asking_about_cart' => $aiAskingAboutCart ?? false,
                'show_purchase_form' => $finalContext['show_purchase_form'],
                'products_count' => is_array($finalContext['products']) ? count($finalContext['products']) : ($finalContext['products']->count() ?? 0),
                'products_names' => isset($finalContext['products']) ? (is_array($finalContext['products']) ? array_column($finalContext['products'], 'name') : $finalContext['products']->pluck('name')->toArray()) : [],
                'has_products' => isset($finalContext['products']) && (is_array($finalContext['products']) ? count($finalContext['products']) > 0 : $finalContext['products']->count() > 0),
                'ai_response_contains_cart_question' => strpos(strtolower($aiResponse), 'bạn có muốn thêm vào giỏ hàng') !== false
            ]);

            \Log::info('Final context after image processing:', [
                'products_count' => is_array($finalContext['products']) ? count($finalContext['products']) : ($finalContext['products']->count() ?? 0),
                'products_names' => is_array($finalContext['products']) ? array_column($finalContext['products'], 'name') : ($finalContext['products']->pluck('name')->toArray() ?? []),
                'show_purchase_form' => $finalContext['show_purchase_form'] ?? false,
                'show_save_coupon' => $finalContext['show_save_coupon'] ?? false,
                'coupons_count' => isset($finalContext['coupons']) ? (is_array($finalContext['coupons']) ? count($finalContext['coupons']) : $finalContext['coupons']->count()) : 0
            ]);

            \Log::info('Final response being sent to frontend:', [
                'show_purchase_form' => $finalContext['show_purchase_form'] ?? false,
                'show_save_coupon' => $finalContext['show_save_coupon'] ?? false,
                'products_count' => is_array($finalContext['products']) ? count($finalContext['products']) : ($finalContext['products']->count() ?? 0),
                'coupons_count' => isset($finalContext['coupons']) ? (is_array($finalContext['coupons']) ? count($finalContext['coupons']) : $finalContext['coupons']->count()) : 0,
                'context_keys' => array_keys($finalContext),
                'user_message' => $userMessage,
                'ai_response' => $aiResponse,
                'products_names' => isset($finalContext['products']) ? (is_array($finalContext['products']) ? array_column($finalContext['products'], 'name') : $finalContext['products']->pluck('name')->toArray()) : [],
                'has_products' => isset($finalContext['products']) && (is_array($finalContext['products']) ? count($finalContext['products']) > 0 : $finalContext['products']->count() > 0)
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

    public function searchProductsByPrice(Request $request)
    {
        try {
            $query = $request->input('query');
            $priceRange = $this->extractPriceRange($query);
            
            if (!$priceRange) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xác định khoảng giá từ câu hỏi của bạn'
                ]);
            }

            $products = Products::with(['categories', 'brand', 'mainImage', 'variants.inventory'])
                ->where('is_active', true)
                ->where(function ($q) use ($priceRange) {
                    if ($priceRange['type'] === 'above') {
                        $q->where(function ($subQ) use ($priceRange) {
                            $subQ->where('price', '>=', $priceRange['amount'])
                                  ->orWhere('discount_price', '>=', $priceRange['amount']);
                        });
                    } elseif ($priceRange['type'] === 'below') {
                        $q->where(function ($subQ) use ($priceRange) {
                            $subQ->where('price', '<=', $priceRange['amount'])
                                  ->orWhere('discount_price', '<=', $priceRange['amount']);
                        });
                    } else {
                        $q->where(function ($subQ) use ($priceRange) {
                            $subQ->whereBetween('price', [$priceRange['min'], $priceRange['max']])
                                  ->orWhereBetween('discount_price', [$priceRange['min'], $priceRange['max']]);
                        });
                    }
                })
                ->orderByRaw('COALESCE(discount_price, price) ASC')
                ->take(10)
                ->get();

            $this->processProductImages(['products' => $products]);

            return response()->json([
                'success' => true,
                'products' => $products,
                'price_range' => $priceRange
            ]);
        } catch (\Exception $e) {
            \Log::error('Search Products By Price Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tìm kiếm sản phẩm'
            ], 500);
        }
    }

    public function getProductVariants(Request $request)
    {
        try {
            $productId = $request->input('product_id');
            
            $product = Products::with(['variants.inventory', 'mainImage'])
                ->where('id', $productId)
                ->where('is_active', true)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm'
                ], 404);
            }

            $variants = $product->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'price' => $variant->price,
                    'stock' => $variant->inventory ? $variant->inventory->quantity : 0
                ];
            });

            return response()->json([
                'success' => true,
                'product' => $product,
                'variants' => $variants
            ]);
        } catch (\Exception $e) {
            \Log::error('Get Product Variants Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thông tin sản phẩm'
            ], 500);
        }
    }

    public function addToCart(Request $request)
    {
        try {
            $request->validate([
                'variant_id' => 'required',
                'quantity' => 'required|integer|min:1',
            ]);

            $variantId = $request->variant_id;
            $variant = null;
            $product = null;
            $price = $request->has('price') ? $request->price : 0;

            if (is_numeric($variantId) && $variantId > 0) {
                $variant = Variants::with(['inventory', 'product'])->find($variantId);
                
                if ($variant) {
                    $product = $variant->product;
                    if (!$price) {
                        $price = $variant->price;
                    }
                    
                    if (!$variant->inventory) {
                        \Log::warning('Initial variant has no inventory:', [
                            'variant_id' => $variant->id,
                            'variant_type' => get_class($variant)
                        ]);
                        
                        return response()->json([
                            'success' => false,
                            'message' => 'Sản phẩm này không có thông tin tồn kho. Vui lòng liên hệ với chúng tôi để được hỗ trợ.',
                        ], 422);
                    }
                    
                    \Log::info('Initial variant and inventory info:', [
                        'variant_id' => $variant->id,
                        'variant_type' => get_class($variant),
                        'inventory_quantity' => $variant->inventory->quantity ?? 'N/A',
                        'requested_quantity' => $request->quantity
                    ]);
                    

                    if ($request->quantity > $variant->inventory->quantity) {
                        \Log::warning('Initial request quantity exceeds stock:', [
                            'variant_id' => $variant->id,
                            'stock_quantity' => $variant->inventory->quantity,
                            'requested_quantity' => $request->quantity
                        ]);
                        
                        return response()->json([
                            'success' => false,
                            'message' => 'Số lượng vượt quá tồn kho. Chỉ còn ' . $variant->inventory->quantity . ' sản phẩm. Không thể thêm ' . $request->quantity . ' sản phẩm.',
                            'available_quantity' => $variant->inventory->quantity,
                            'requested_quantity' => $request->quantity
                        ], 422);
                    }
                    
                    $currentCartQuantity = 0;
                    if (Auth::check()) {
                        $currentCartQuantity = Cart::where('variant_id', $variant->id)
                            ->where('user_id', Auth::id())
                            ->sum('quantity');
                    } else {
                        $sessionId = $request->header('X-Session-Id');
                        if ($sessionId) {
                            $currentCartQuantity = Cart::where('variant_id', $variant->id)
                                ->where('session_id', $sessionId)
                                ->sum('quantity');
                        }
                    }
                    
                    \Log::info('Initial cart validation check:', [
                        'variant_id' => $variant->id,
                        'stock_quantity' => $variant->inventory->quantity ?? 'N/A',
                        'current_cart_quantity' => $currentCartQuantity,
                        'requested_quantity' => $request->quantity,
                        'total_quantity' => $currentCartQuantity + $request->quantity,
                        'user_id' => Auth::id() ?? 'guest',
                        'session_id' => $request->header('X-Session-Id') ?? 'N/A'
                    ]);
                    

                    $totalQuantity = $currentCartQuantity + $request->quantity;
                    if ($totalQuantity > $variant->inventory->quantity) {
                        \Log::warning('Initial cart quantity exceeds stock:', [
                            'variant_id' => $variant->id,
                            'stock_quantity' => $variant->inventory->quantity,
                            'current_cart_quantity' => $currentCartQuantity,
                            'requested_quantity' => $request->quantity,
                            'total_quantity' => $totalQuantity
                        ]);
                        
                        return response()->json([
                            'success' => false,
                            'message' => 'Giỏ hàng đã có ' . $currentCartQuantity . ' sản phẩm, vượt quá số lượng tồn kho (' . $variant->inventory->quantity . '). Không thể thêm thêm sản phẩm.',
                            'available_quantity' => $variant->inventory->quantity,
                            'current_cart_quantity' => $currentCartQuantity,
                            'requested_quantity' => $request->quantity
                        ], 422);
                    }
                    
                    \Log::info('Initial validation passed:', [
                        'variant_id' => $variant->id,
                        'stock_quantity' => $variant->inventory->quantity,
                        'current_cart_quantity' => $currentCartQuantity,
                        'requested_quantity' => $request->quantity,
                        'total_quantity' => $totalQuantity
                    ]);
                }
            }

            if (!$variant) {
                $product = Products::find($variantId);
                $product = Products::find($variantId);
                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không tìm thấy sản phẩm'
                    ], 404);
                }
                
                if (!$price) {
                    $price = $product->discount_price ?? $product->price;
                }
                
                $variant = (object) [
                    'id' => $product->id,
                    'product' => $product,
                    'price' => $price,
                    'inventory' => (object) [
                        'quantity' => 999
                    ]
                ];
            }

            if (!$variant->inventory) {
                \Log::warning('Variant has no inventory:', [
                    'variant_id' => $variant->id,
                    'variant_type' => get_class($variant)
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm này không có thông tin tồn kho. Vui lòng liên hệ với chúng tôi để được hỗ trợ.',
                ], 422);
            }
            
            \Log::info('Variant and inventory info:', [
                'variant_id' => $variant->id,
                'variant_type' => get_class($variant),
                'inventory_quantity' => $variant->inventory->quantity ?? 'N/A',
                'requested_quantity' => $request->quantity
            ]);
            

            if ($request->quantity > $variant->inventory->quantity) {
                \Log::warning('Request quantity exceeds stock:', [
                    'variant_id' => $variant->id,
                    'stock_quantity' => $variant->inventory->quantity,
                    'requested_quantity' => $request->quantity
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Số lượng vượt quá tồn kho. Chỉ còn ' . $variant->inventory->quantity . ' sản phẩm. Không thể thêm ' . $request->quantity . ' sản phẩm.',
                    'available_quantity' => $variant->inventory->quantity,
                    'requested_quantity' => $request->quantity
                ], 422);
            }
            
            $currentCartQuantity = 0;
            if (Auth::check()) {
                $currentCartQuantity = Cart::where('variant_id', $variant->id)
                    ->where('user_id', Auth::id())
                    ->sum('quantity');
            } else {
                $sessionId = $request->header('X-Session-Id');
                if ($sessionId) {
                    $currentCartQuantity = Cart::where('variant_id', $variant->id)
                        ->where('session_id', $sessionId)
                        ->sum('quantity');
                }
            }
            
            \Log::info('Cart validation check:', [
                'variant_id' => $variant->id,
                'stock_quantity' => $variant->inventory->quantity ?? 'N/A',
                'current_cart_quantity' => $currentCartQuantity,
                'requested_quantity' => $request->quantity,
                'total_quantity' => $currentCartQuantity + $request->quantity,
                'user_id' => Auth::id() ?? 'guest',
                'session_id' => $request->header('X-Session-Id') ?? 'N/A'
            ]);
            

            $totalQuantity = $currentCartQuantity + $request->quantity;
            if ($totalQuantity > $variant->inventory->quantity) {
                \Log::warning('Cart quantity exceeds stock:', [
                    'variant_id' => $variant->id,
                    'stock_quantity' => $variant->inventory->quantity,
                    'current_cart_quantity' => $currentCartQuantity,
                    'requested_quantity' => $request->quantity,
                    'total_quantity' => $totalQuantity
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Giỏ hàng đã có ' . $currentCartQuantity . ' sản phẩm, vượt quá số lượng tồn kho (' . $variant->inventory->quantity . '). Không thể thêm thêm sản phẩm.',
                    'available_quantity' => $variant->inventory->quantity,
                    'current_cart_quantity' => $currentCartQuantity,
                    'requested_quantity' => $request->quantity
                ], 422);
            }
            
            \Log::info('Final validation passed:', [
                'variant_id' => $variant->id,
                'stock_quantity' => $variant->inventory->quantity,
                'current_cart_quantity' => $currentCartQuantity,
                'requested_quantity' => $request->quantity,
                'total_quantity' => $totalQuantity,
                'validation_result' => 'Stock limit respected',
                'critical_check' => 'This validation should prevent exceeding stock limit'
            ]);
            
            $data = [
                'variant_id' => $variant->id,
                'quantity' => $request->quantity,
                'price' => $price,
            ];

            if (Auth::check()) {
                $data['user_id'] = Auth::id();
                $data['session_id'] = null;
                \Log::info('User is authenticated:', ['user_id' => $data['user_id']]);
            } else {
                $sessionId = $request->header('X-Session-Id');
                if (!$sessionId) {
                                $sessionId = 'session_' . time() . '_' . uniqid();
                    \Log::info('Generated new session ID for guest:', ['session_id' => $sessionId]);
                }
                $data['session_id'] = $sessionId;
                $data['user_id'] = null;
                \Log::info('User is guest:', ['session_id' => $data['session_id']]);
            }

            \Log::info('Cart data before creation:', $data);

            $existingCart = Cart::where('variant_id', $variant->id)
                ->where(function ($q) use ($data) {
                    if (isset($data['user_id']) && $data['user_id']) {
                        $q->where('user_id', $data['user_id']);
                    } else {
                        $q->where('session_id', $data['session_id']);
                    }
                })
                ->first();
                
            \Log::info('Existing cart check:', [
                'variant_id' => $variant->id,
                'user_id' => $data['user_id'] ?? 'guest',
                'session_id' => $data['session_id'] ?? 'N/A',
                'existing_cart' => $existingCart ? $existingCart->toArray() : 'Not found',
                'stock_quantity' => $variant->inventory->quantity ?? 'N/A',
                'requested_quantity' => $request->quantity,
                'current_cart_quantity' => $currentCartQuantity ?? 0,
                'total_quantity_would_be' => ($existingCart ? $existingCart->quantity : 0) + $request->quantity,
                'validation_needed' => ($existingCart ? $existingCart->quantity : 0) + $request->quantity > ($variant->inventory->quantity ?? 0),
                'critical_check' => 'This should prevent adding more than stock limit'
            ]);

            if ($existingCart) {
                $newTotalQuantity = $existingCart->quantity + $request->quantity;
                
                \Log::info('Updating existing cart item:', [
                    'cart_id' => $existingCart->id, 
                    'old_quantity' => $existingCart->quantity, 
                    'new_quantity' => $newTotalQuantity, 
                    'stock_quantity' => $variant->inventory->quantity ?? 'N/A',
                    'variant_id' => $variant->id
                ]);
                

                if ($newTotalQuantity > $variant->inventory->quantity) {
                    \Log::warning('Update cart quantity exceeds stock:', [
                        'variant_id' => $variant->id,
                        'stock_quantity' => $variant->inventory->quantity,
                        'existing_cart_quantity' => $existingCart->quantity,
                        'requested_quantity' => $request->quantity,
                        'new_total_quantity' => $newTotalQuantity
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Giỏ hàng đã có ' . $existingCart->quantity . ' sản phẩm, vượt quá số lượng tồn kho (' . $variant->inventory->quantity . '). Không thể thêm thêm sản phẩm.',
                        'available_quantity' => $variant->inventory->quantity,
                        'current_cart_quantity' => $existingCart->quantity,
                        'requested_quantity' => $request->quantity
                    ], 422);
                }
                
                $existingCart->quantity = $newTotalQuantity;
                $existingCart->save();
                $cartItem = $existingCart;
                
                \Log::info('Cart item updated successfully:', [
                    'cart_id' => $existingCart->id, 
                    'new_quantity' => $newTotalQuantity,
                    'stock_quantity' => $variant->inventory->quantity,
                    'validation_passed' => true,
                    'final_validation' => 'Stock limit respected',
                    'critical_validation' => 'Stock limit check passed'
                ]);
            } else {
                \Log::info('Creating new cart item');
                $cartItem = Cart::create($data);
                \Log::info('Cart item created successfully:', [
                    'cart_id' => $cartItem->id, 
                    'data' => $cartItem->toArray(),
                    'stock_quantity' => $variant->inventory->quantity,
                    'validation_passed' => true,
                    'final_validation' => 'Stock limit respected',
                    'critical_validation' => 'Stock limit check passed'
                ]);
            }

            \Log::info('Successfully added to cart:', [
                'variant_id' => $variant->id,
                'product_name' => $product->name,
                'quantity' => $request->quantity,
                'price' => $price,
                'user_id' => $data['user_id'],
                'session_id' => $data['session_id'],
                'final_stock_quantity' => $variant->inventory->quantity ?? 'N/A',
                'final_cart_quantity' => $cartItem->quantity,
                'validation_summary' => [
                    'stock_limit' => $variant->inventory->quantity ?? 'N/A',
                    'cart_total' => $cartItem->quantity,
                    'within_limit' => $cartItem->quantity <= ($variant->inventory->quantity ?? 0)
                ],
                'critical_validation' => 'Stock limit validation completed successfully'
            ]);

                            if (is_numeric($variant->id) && $variant->id > 0) {
                    $responseData = [
                        'success' => true,
                        'message' => 'Đã thêm sản phẩm vào giỏ hàng thành công!',
                        'cart_item' => $cartItem->load(['variant.product.mainImage'])
                    ];
                } else {
                    $responseData = [
                        'success' => true,
                        'message' => 'Đã thêm sản phẩm vào giỏ hàng thành công!',
                        'cart_item' => $cartItem,
                        'product' => $product
                    ];
                }

            return response()->json($responseData);
        } catch (\Exception $e) {
            \Log::error('Add To Cart Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi thêm vào giỏ hàng'
            ], 500);
        }
    }

    public function searchOrder(Request $request)
    {
        try {
            $trackingCode = $request->input('tracking_code');
            $userId = $request->user() ? $request->user()->id : null;

            if (!$trackingCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập mã tra cứu đơn hàng'
                ], 400);
            }

            $query = Orders::with(['orderDetails.variant.product.mainImage', 'address', 'user'])
                ->where('tracking_code', $trackingCode);

            if ($userId) {
                $query->where('user_id', $userId);
            }

            $order = $query->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng với mã tra cứu này'
                ], 404);
            }

            foreach ($order->orderDetails as $detail) {
                if ($detail->variant && $detail->variant->product && $detail->variant->product->mainImage) {
                    $imagePath = $detail->variant->product->mainImage->image_path;
                    if (!str_starts_with($imagePath, 'http')) {
                        $detail->variant->product->mainImage->image_url = url('storage/' . ltrim($imagePath, '/'));
                    } else {
                        $detail->variant->product->mainImage->image_url = $imagePath;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'order' => $order
            ]);
        } catch (\Exception $e) {
            \Log::error('Search Order Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tra cứu đơn hàng'
            ], 500);
        }
    }

    private function extractPriceRange($query)
    {
        $query = strtolower($query);
        
        preg_match_all('/(\d+(?:\.\d+)?)\s*(?:nghìn|k|đồng|vnđ|vnd|triệu|tr)/i', $query, $matches);
        
        if (empty($matches[1])) {
            return null;
        }

        $amount = (float) $matches[1][0];
        
        if (preg_match('/triệu|tr/i', $matches[0][0])) {
            $amount *= 1000000;
        } elseif (preg_match('/nghìn|k/i', $matches[0][0])) {
            $amount *= 1000;
        }

        \Log::info('Extracted price range:', [
            'original_query' => $query,
            'matched_amount' => $amount,
            'matched_unit' => $matches[0][0] ?? 'unknown'
        ]);

        if (preg_match('/(trên|từ|lớn hơn|cao hơn|trên\s+\d+|>\s*\d+)/i', $query)) {
            return [
                'type' => 'above',
                'amount' => $amount
            ];
        } elseif (preg_match('/(dưới|nhỏ hơn|thấp hơn|dưới\s+\d+|<\s*\d+)/i', $query)) {
            return [
                'type' => 'below',
                'amount' => $amount
            ];
        } else {
            $range = $amount * 0.2;
            return [
                'type' => 'range',
                'min' => max(0, $amount - $range),
                'max' => $amount + $range
            ];
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
7. Tìm kiếm sản phẩm theo khoảng giá
8. Hỗ trợ thêm sản phẩm vào giỏ hàng
9. Tra cứu tình trạng đơn hàng

Hãy trả lời bằng tiếng Việt một cách thân thiện và hữu ích.

**QUAN TRỌNG: CHỈ sử dụng thông tin có sẵn trong context, KHÔNG tự bịa ra thông tin.**

**ĐÁNH GIÁ KẾT QUẢ TÌM KIẾM:**
- Nếu context KHÔNG có sản phẩm (products empty), hãy thông báo rõ ràng: Hiện tại cửa hàng chưa có sản phẩm phù hợp với yêu cầu của bạn
- Nếu context có sản phẩm, CHỈ mô tả sản phẩm có sẵn trong context
- ĐỪNG tự bịa ra thông tin về giá cả, danh mục, hoặc tính năng sản phẩm 

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
- Nếu không có flash sale nào, hãy nói: Hiện tại cửa hàng chưa có chương trình flash sale nào.

**KHI KHÁCH HÀNG HỎI VỀ MÃ GIẢM GIÁ:**
- **QUAN TRỌNG NHẤT: CHỈ hiển thị mã giảm giá có sẵn trong context, KHÔNG BAO GIỜ tự bịa ra mã giảm giá**
- Nếu context KHÔNG có mã giảm giá (coupons trống), hãy nói: Hiện tại cửa hàng chưa có mã giảm giá nào.
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
  + Hãy nói rõ ràng: Hiện tại cửa hàng chưa có mã giảm giá nào.
  + KHÔNG tự bịa ra mã giảm giá
  + Có thể gợi ý: Bạn có thể theo dõi trang web hoặc fanpage để cập nhật mã giảm giá mới.

**XỬ LÝ TRƯỜNG HỢP KHÔNG CÓ THÔNG TIN THANH TOÁN:**
- Nếu khách hàng hỏi về thanh toán mà context không có thông tin:
  + Hãy nói: Tôi không có thông tin chi tiết về phương thức thanh toán trong cơ sở dữ liệu.
  + KHÔNG tự bịa ra thông tin thanh toán
  + Có thể gợi ý: Bạn có thể liên hệ với chúng tôi để được tư vấn chi tiết.

**KHI KHÁCH HÀNG HỎI VỀ SẢN PHẨM:**
- **QUAN TRỌNG NHẤT: CHỈ hiển thị sản phẩm có sẵn trong context, KHÔNG BAO GIỜ tự bịa ra thông tin sản phẩm**
- Nếu context KHÔNG có sản phẩm, hãy nói: Xin lỗi, hiện tại cửa hàng chưa có sản phẩm này.
- Nếu context có sản phẩm, chỉ hiển thị thông tin CHÍNH XÁC từ database
- KHÔNG hiển thị flash sale hoặc mã giảm giá
- Tập trung vào thông tin sản phẩm: tên, giá, size, màu, tồn kho

**QUAN TRỌNG VỀ TÌM KIẾM SẢN PHẨM:**

1. **Khi khách hàng hỏi về sản phẩm cụ thể**: 
   - **CHỈ hiển thị sản phẩm có sẵn trong context**
   - Trả lời tự nhiên và trực tiếp về sản phẩm được hỏi
       - Nếu context không có sản phẩm, hãy nói: Xin lỗi, hiện tại cửa hàng chưa có sản phẩm này.
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
       - Nếu context trống hoặc không có sản phẩm, hãy nói rõ ràng: Hiện tại cửa hàng chưa có sản phẩm này.
   - Nếu context có sản phẩm, chỉ hiển thị thông tin CHÍNH XÁC từ database

5. **LƯU Ý QUAN TRỌNG**:
   - **KHÔNG BAO GIỜ tự bịa ra thông tin sản phẩm không có trong context**
   - **CHỈ sử dụng thông tin có sẵn trong context**
       - Nếu context trống, hãy nói rõ ràng: Hiện tại cửa hàng chưa có sản phẩm này.
   - **KHÔNG hiển thị URL hình ảnh trong text trả lời**
   - Hình ảnh sẽ được hiển thị tự động bên dưới thông qua ProductCard

6. **XỬ LÝ TRƯỜNG HỢP KHÔNG CÓ SẢN PHẨM**:x`
   - Nếu khách hàng hỏi về sản phẩm cụ thể mà context không có:
           + Hãy nói: Xin lỗi, hiện tại cửa hàng chưa có sản phẩm này.
     + KHÔNG tự bịa ra thông tin sản phẩm
           + Có thể gợi ý: Bạn có thể xem các sản phẩm khác có sẵn hoặc liên hệ với chúng tôi để được tư vấn thêm.

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
     + KHÔNG tự bịa ra sản phẩm tương tự không có trong database

**HƯỚNG DẪN VỀ TÌM KIẾM THEO GIÁ:**
- Khi khách hàng hỏi về sản phẩm theo khoảng giá (ví dụ: \"áo dưới 500k\", \"quần trên 1 triệu\"):
  + Hiển thị sản phẩm phù hợp với khoảng giá được yêu cầu
  + Giải thích rõ khoảng giá đang tìm kiếm
  + Gợi ý khách hàng có thể chọn size, màu và thêm vào giỏ hàng

**HƯỚNG DẪN VỀ THÊM VÀO GIỎ HÀNG:**
- Khi khách hàng muốn mua sản phẩm:
  + Hướng dẫn khách hàng chọn size, màu và số lượng
  + Giải thích rằng họ có thể thêm vào giỏ hàng ngay
  + Thông báo rằng sản phẩm sẽ được thêm vào giỏ hàng của họ

**HƯỚNG DẪN VỀ TRA CỨU ĐƠN HÀNG:**
- Khi khách hàng hỏi về tình trạng đơn hàng:
  + Yêu cầu khách hàng cung cấp mã tra cứu đơn hàng
  + Giải thích rằng bạn sẽ tra cứu thông tin đơn hàng cho họ
  + Hiển thị thông tin chi tiết về đơn hàng nếu tìm thấy";

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
        $contextInstruction .= "KHÔNG BAO GIỜ tự tạo ra thông tin tồn kho cụ thể\n";
        $contextInstruction .= "KHÔNG BAO GIỜ tự tạo ra thông tin thương hiệu mới không có trong context\n";
        $contextInstruction .= "KHÔNG BAO GIỜ tự tạo ra mô tả sản phẩm mới không có trong context\n";
        $contextInstruction .= "CHỈ sử dụng thông tin có sẵn trong context từ database\n";
        $contextInstruction .= "Nếu context trống, hãy nói rõ ràng: 'Hiện tại cửa hàng chưa có sản phẩm này.'\n";
        $contextInstruction .= "\n**QUY TẮC HIỂN THỊ SẢN PHẨM MỚI:**\n";
        $contextInstruction .= "Khi context có sản phẩm phù hợp, hãy mô tả sản phẩm một cách tự nhiên và hữu ích\n";
        $contextInstruction .= "Sau khi mô tả sản phẩm, HÃY HỎI khách hàng: 'Bạn có muốn thêm vào giỏ hàng không?'\n";
        $contextInstruction .= "CHỈ hiển thị form 'Thêm vào giỏ hàng' khi khách hàng đồng ý\n";
        $contextInstruction .= "Tạo trải nghiệm tương tác tự nhiên: mô tả → hỏi → xác nhận → hiển thị form\n";
        $contextInstruction .= "KHÔNG BAO GIỜ hiển thị form ngay từ đầu khi chỉ mô tả sản phẩm\n";
        $contextInstruction .= "Khi mô tả sản phẩm lần đầu, CHỈ mô tả và hỏi, KHÔNG hiển thị form\n";
        $contextInstruction .= "Form chỉ xuất hiện sau khi khách hàng đồng ý và AI đã xác nhận\n";
        $contextInstruction .= "Khi có nhiều sản phẩm, hãy tạo các nút đề xuất: 'Chọn Sản phẩm 1', 'Chọn Sản phẩm 2'\n";
        $contextInstruction .= "Khi khách hàng click vào nút đề xuất, hiển thị form 'Thêm vào giỏ hàng' cho sản phẩm đó\n";
        $contextInstruction .= "Khi khách hàng nói 'có', 'muốn', 'đồng ý', 'ok', 'được', 'dạ đúng', 'có muốn thêm' thì hiển thị form 'Thêm vào giỏ hàng'\n";
        $contextInstruction .= "Khi khách hàng chọn size cụ thể (ví dụ: 'size s', 'size m') thì cũng coi như có ý định mua hàng\n";
        $contextInstruction .= "Khi khách hàng chọn màu cụ thể (ví dụ: 'màu đen', 'màu xanh') thì cũng coi như có ý định mua hàng\n";
        $contextInstruction .= "Khi khách hàng chọn sản phẩm cụ thể (ví dụ: 'sản phẩm 1', 'áo polo tay ngắn') thì cũng coi như có ý định mua hàng\n";
        $contextInstruction .= "\n**QUAN TRỌNG VỀ TRẢ LỜI:**\n";
        $contextInstruction .= "Khi context có sản phẩm phù hợp, hãy trả lời TỰ NHIÊN và TRỰC TIẾP về sản phẩm đó\n";
        $contextInstruction .= "KHÔNG BAO GIỜ nói 'không tìm thấy' hoặc 'không có sản phẩm' khi context có sản phẩm\n";
        $contextInstruction .= "Khi khách hàng đồng ý mua hàng, hãy xác nhận: 'Dạ được ạ! Bây giờ bạn có thể chọn size, màu và số lượng để thêm vào giỏ hàng.'\n";
        $contextInstruction .= "Sau khi xác nhận, mới hiển thị form 'Thêm vào giỏ hàng'\n";
        $contextInstruction .= "Khi khách hàng thêm sản phẩm vào giỏ hàng thành công, hãy nói: 'Tôi đã thêm vào giỏ hàng cho bạn rồi nè! 🛒'\n";
        $contextInstruction .= "**QUY TẮC QUAN TRỌNG:** LUÔN hiển thị sản phẩm cơ bản trước (hình ảnh, tên, danh mục, giá)\n";
        $contextInstruction .= "**KHÔNG BAO GIỜ** hiển thị form 'Thêm vào giỏ hàng' ngay từ đầu\n";
        $contextInstruction .= "**GIAO DIỆN MẶC ĐỊNH:** Chỉ hiển thị sản phẩm cơ bản như trong ảnh - KHÔNG có form\n";
        $contextInstruction .= "**LUỒNG HOẠT ĐỘNG:** 1) Hiển thị sản phẩm cơ bản → 2) Hỏi 'Bạn có muốn thêm vào giỏ hàng không?' → 3) Chờ phản hồi\n";
        $contextInstruction .= "**FORM CHỈ XUẤT HIỆN:** Sau khi khách hàng đồng ý mua hàng và AI xác nhận rõ ràng\n";
        $contextInstruction .= "**QUY TẮC TRÒ CHUYỆN:** Khi khách hàng nói tên sản phẩm cụ thể, LUÔN trả lời về sản phẩm đó và hiển thị sản phẩm đó\n";
        $contextInstruction .= "**KHÔNG BAO GIỜ** hiển thị sản phẩm khác khi khách hàng đang hỏi về sản phẩm cụ thể\n";
        $contextInstruction .= "**QUY TẮC THÊM VÀO GIỎ HÀNG:** Khi AI hỏi 'Bạn có muốn thêm vào giỏ hàng không?' thì LUÔN hiển thị form thêm vào giỏ hàng\n";
        $contextInstruction .= "**FORM HIỂN THỊ:** Khi AI hỏi về thêm vào giỏ hàng, form sẽ hiển thị ngay lập tức để khách hàng có thể chọn size, màu, số lượng\n";
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

        if (isset($context['products']) && (is_array($context['products']) ? count($context['products']) : $context['products']->count()) > 0) {
            $formatted .= "SẢN PHẨM:\n";
            foreach ($context['products'] as $product) {
                $name = is_object($product) ? $product->name : $product['name'];
                $price = is_object($product) ? $product->price : $product['price'];
                $discountPrice = is_object($product) ? $product->discount_price : ($product['discount_price'] ?? null);

                $formatted .= "📦 {$name}\n";
                $formatted .= "💰 Giá gốc: " . number_format($price) . " VNĐ\n";

                if ($discountPrice && $discountPrice > 0) {
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
                        $totalStock = 0;
                        $availableSizes = [];
                        $availableColors = [];
                        
                        foreach ($product->variants as $variant) {
                            if ($variant->inventory) {
                                $stock = $variant->inventory->quantity ?? 0;
                                $totalStock += $stock;
                                if ($stock > 0) {
                                    $availableSizes[] = $variant->size;
                                    $availableColors[] = $variant->color;
                                }
                            }
                        }

                        if ($totalStock > 0) {
                            $uniqueSizes = array_unique($availableSizes);
                            $uniqueColors = array_unique($availableColors);
                            
                            if (!empty($uniqueSizes)) {
                                $formatted .= "📏 Size có sẵn: " . implode(', ', $uniqueSizes) . "\n";
                            }
                            if (!empty($uniqueColors)) {
                                $formatted .= "🎨 Màu sắc: " . implode(', ', $uniqueColors) . "\n";
                            }
                            $formatted .= "📦 Tình trạng: Còn hàng ({$totalStock} sản phẩm)\n";
                        } else {
                            $formatted .= "📦 Tình trạng: Hết hàng\n";
                        }
                    }
                } else {
                    if (isset($product['variants']) && count($product['variants']) > 0) {
                        $totalStock = 0;
                        $availableSizes = [];
                        $availableColors = [];
                        
                        foreach ($product['variants'] as $variant) {
                            if (isset($variant['inventory'])) {
                                $stock = $variant['inventory']['quantity'] ?? 0;
                                $totalStock += $stock;
                                if ($stock > 0) {
                                    $availableSizes[] = $variant['size'];
                                    $availableColors[] = $variant['color'];
                                }
                            }
                        }

                        if ($totalStock > 0) {
                            $uniqueSizes = array_unique($availableSizes);
                            $uniqueColors = array_unique($availableColors);
                            
                            if (!empty($uniqueSizes)) {
                                $formatted .= "📏 Size có sẵn: " . implode(', ', $uniqueSizes) . "\n";
                            }
                            if (!empty($uniqueColors)) {
                                $formatted .= "🎨 Màu sắc: " . implode(', ', $uniqueColors) . "\n";
                            }
                            $formatted .= "📦 Tình trạng: Còn hàng ({$totalStock} sản phẩm)\n";
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

                    $cleanDescription = strip_tags($description);

                    $cleanDescription = preg_replace('/[^\p{L}\p{N}\s]/u', '', $cleanDescription);
                    $shortDesc = substr(trim($cleanDescription), 0, 100);
                    if (!empty($shortDesc)) {
                        $formatted .= "📝 Mô tả: {$shortDesc}...\n";
                    }
                }

                $formatted .= "---\n";
            }
        }


        if (isset($context['coupons']) && (is_array($context['coupons']) ? count($context['coupons']) : $context['coupons']->count()) > 0) {
            $formatted .= "🎫 MÃ GIẢM GIÁ HIỆN CÓ:\n";
            foreach ($context['coupons'] as $coupon) {
                $name = is_object($coupon) ? $coupon->name : $coupon['name'];
                $code = is_object($coupon) ? $coupon->code : $coupon['code'];
                $value = is_object($coupon) ? $coupon->value : $coupon['value'];
                $type = is_object($coupon) ? $coupon->type : $coupon['type'];
                $maxDiscount = is_object($coupon) ? $coupon->max_discount_value : ($coupon['max_discount_value'] ?? 0);
                $minOrder = is_object($coupon) ? $coupon->min_order_value : $coupon['min_order_value'];
                $endDate = is_object($coupon) ? $coupon->end_date : $coupon['end_date'];
                $description = is_object($coupon) ? $coupon->description : ($coupon['description'] ?? '');
                
                $formatted .= "• **{$name}**\n";
                $formatted .= "  🔑 Mã: {$code}\n";
                $formatted .= "  💰 Giảm: {$value}";
                if ($type === 'percent') {
                    $formatted .= "% (Tối đa: " . number_format($maxDiscount) . " VNĐ)";
                } else {
                    $formatted .= " VNĐ";
                }
                $formatted .= "\n  📦 Đơn tối thiểu: " . number_format($minOrder) . " VNĐ\n";
                $formatted .= "  ⏰ Hạn sử dụng: " . date('d/m/Y', strtotime($endDate)) . "\n";
                if ($description) {
                    $formatted .= "  📝 Mô tả: {$description}\n";
                }
                $formatted .= "  💾 **Lưu mã ngay để sử dụng!**\n";
                $formatted .= "---\n";
            }
        }


        if (isset($context['flash_sales']) && (is_array($context['flash_sales']) ? count($context['flash_sales']) : $context['flash_sales']->count()) > 0) {
            $formatted .= "⚡ FLASH SALE ĐANG DIỄN RA:\n";
            foreach ($context['flash_sales'] as $flashSale) {
                $name = is_object($flashSale) ? $flashSale->name : $flashSale['name'];
                $startTime = is_object($flashSale) ? $flashSale->start_time : $flashSale['start_time'];
                $endTime = is_object($flashSale) ? $flashSale->end_time : $flashSale['end_time'];
                $description = is_object($flashSale) ? $flashSale->description : ($flashSale['description'] ?? '');
                $productsCount = is_object($flashSale) ? ($flashSale->products ? $flashSale->products->count() : 0) : ($flashSale['products_count'] ?? 0);
                
                $formatted .= "• **{$name}**\n";
                $formatted .= "  Thời gian: " . date('d/m/Y H:i', strtotime($startTime)) . " - " . date('d/m/Y H:i', strtotime($endTime)) . "\n";
                if ($description) {
                    $formatted .= "  Mô tả: {$description}\n";
                }
                $formatted .= "  Số sản phẩm: {$productsCount}\n";
                $formatted .= "---\n";
            }
        }


        if (isset($context['products']) && (is_array($context['products']) ? count($context['products']) : $context['products']->count()) > 0) {
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

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-goog-api-key' => $this->geminiApiKey
            ])->post($this->geminiApiUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            }
            
                    if ($response->status() === 429) {
            \Log::warning('Gemini API quota exceeded, using fallback response');
            return "Xin lỗi, hệ thống AI đang tạm thời quá tải. Vui lòng thử lại sau 21 giây hoặc liên hệ hỗ trợ.";
        }
        
        throw new \Exception('Gemini API error: ' . $response->body());
    } catch (\Exception $e) {
        if (strpos($e->getMessage(), '429') !== false || strpos($e->getMessage(), 'RESOURCE_EXHAUSTED') !== false) {
                \Log::warning('Gemini API quota exceeded, using fallback response');
                return "Xin lỗi, hệ thống AI đang tạm thời quá tải. Vui lòng thử lại sau 21 giây hoặc liên hệ hỗ trợ.";
            }
            throw $e;
        }
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
            'có flash sale không',
            'có flash sale nào không',
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
                    if (isset($product->discount_price) && $product->discount_price > 0) {
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
                    $priceRange = $this->extractPriceRange($userMessage);
                if ($priceRange) {
                    if ($priceRange['type'] === 'above') {
                        $amount = number_format($priceRange['amount']);
                        $response = "😔 Xin lỗi, hiện tại cửa hàng **KHÔNG CÓ** sản phẩm nào có giá trên {$amount} VNĐ phù hợp với yêu cầu của bạn.\n\n";
                    } elseif ($priceRange['type'] === 'below') {
                        $amount = number_format($priceRange['amount']);
                        $response = "😔 Xin lỗi, hiện tại cửa hàng **KHÔNG CÓ** sản phẩm nào có giá dưới {$amount} VNĐ phù hợp với yêu cầu của bạn.\n\n";
                    } else {
                        $min = number_format($priceRange['min']);
                        $max = number_format($priceRange['max']);
                        $response = "😔 Xin lỗi, hiện tại cửa hàng **KHÔNG CÓ** sản phẩm nào trong khoảng giá {$min} - {$max} VNĐ phù hợp với yêu cầu của bạn.\n\n";
                    }
                } else {
                    $response = "😔 Xin lỗi, hiện tại cửa hàng **KHÔNG CÓ** sản phẩm phù hợp với yêu cầu của bạn.\n\n";
                }
                $response .= "💡 Bạn có thể:\n";
                $response .= "• Thử tìm kiếm với từ khóa khác\n";
                $response .= "• Xem các sản phẩm khác có sẵn\n";
                $response .= "• Liên hệ với chúng tôi để được tư vấn thêm";
            } elseif ($isFlashSaleQuestion) {
                if (isset($context['flash_sales']) && (is_array($context['flash_sales']) ? count($context['flash_sales']) : $context['flash_sales']->count()) > 0) {
                    $flashSales = is_array($context['flash_sales']) ? collect($context['flash_sales']) : $context['flash_sales'];
                    
                    $response = "⚡ **FLASH SALE ĐANG DIỄN RA:**\n\n";
                    
                    foreach ($flashSales as $flashSale) {
                        $name = is_object($flashSale) ? $flashSale->name : $flashSale['name'];
                        $startTime = is_object($flashSale) ? $flashSale->start_time : $flashSale['start_time'];
                        $endTime = is_object($flashSale) ? $flashSale->end_time : $flashSale['end_time'];
                        $description = is_object($flashSale) ? $flashSale->description : ($flashSale['description'] ?? '');
                        $productsCount = is_object($flashSale) ? ($flashSale->products ? $flashSale->products->count() : 0) : ($flashSale['products_count'] ?? 0);
                        
                        $response .= "🔥 **{$name}**\n";
                        $response .= "⏰ Thời gian: " . date('d/m/Y', strtotime($startTime)) . " - " . date('d/m/Y', strtotime($endTime)) . "\n";
                        if ($description) {
                            $response .= "📝 Mô tả: {$description}\n";
                        }
                        $response .= "📦 Số sản phẩm: {$productsCount}\n\n";
                    }
                    
                    $response .= "✨ Thông tin chi tiết đã được hiển thị bên dưới!";
                } else {
                    $response = "😔 **KHÔNG CÓ** chương trình flash sale nào đang diễn ra tại thời điểm này.\n\n";
                    $response .= "💡 Bạn có thể:\n";
                    $response .= "• Theo dõi trang web để cập nhật thông tin mới\n";
                    $response .= "• Liên hệ với chúng tôi để được tư vấn\n";
                    $response .= "• Xem các sản phẩm khuyến mãi khác";
                }
            } elseif ($isCouponQuestion) {
                if (isset($context['coupons']) && (is_array($context['coupons']) ? count($context['coupons']) : $context['coupons']->count()) > 0) {
                    $coupons = is_array($context['coupons']) ? collect($context['coupons']) : $context['coupons'];
                    
                    $response = "🎫 **MÃ GIẢM GIÁ HIỆN CÓ:**\n\n";
                    
                    foreach ($coupons as $coupon) {
                        $name = is_object($coupon) ? $coupon->name : $coupon['name'];
                        $code = is_object($coupon) ? $coupon->code : $coupon['code'];
                        $value = is_object($coupon) ? $coupon->value : $coupon['value'];
                        $type = is_object($coupon) ? $coupon->type : $coupon['type'];
                        $maxDiscount = is_object($coupon) ? $coupon->max_discount_value : ($coupon['max_discount_value'] ?? 0);
                        $minOrder = is_object($coupon) ? $coupon->min_order_value : $coupon['min_order_value'];
                        $endDate = is_object($coupon) ? $coupon->end_date : $coupon['end_date'];
                        
                        $response .= "💎 **{$name}**\n";
                        $response .= "🔑 Mã: `{$code}`\n";
                        $response .= "💰 Giảm: ";
                        if ($type === 'percent') {
                            $response .= "{$value}% (Tối đa: " . number_format($maxDiscount) . " VNĐ)";
                        } else {
                            $response .= number_format($value) . " VNĐ";
                        }
                        $response .= "\n";
                        $response .= "📦 Đơn tối thiểu: " . number_format($minOrder) . " VNĐ\n";
                        $response .= "⏰ Hạn sử dụng: " . date('d/m/Y', strtotime($endDate)) . "\n";
                        $response .= "💾 **Lưu mã ngay để sử dụng!**\n\n";
                    }
                    
                    $response .= "✨ **HƯỚNG DẪN SỬ DỤNG:**\n";
                    $response .= "1. Nhấn nút 'Lưu mã giảm giá' bên dưới\n";
                    $response .= "2. Mã sẽ được lưu vào tài khoản của bạn\n";
                    $response .= "3. Sử dụng khi thanh toán đơn hàng\n\n";
                    $response .= "🚀 **Tiết kiệm ngay hôm nay!**";
                } else {
                    $response = "😔 **KHÔNG CÓ** mã giảm giá nào đang hoạt động tại thời điểm này.\n\n";
                    $response .= "💡 Bạn có thể:\n";
                    $response .= "• Theo dõi trang web để cập nhật mã mới\n";
                    $response .= "• Liên hệ với chúng tôi để được tư vấn\n";
                    $response .= "• Xem các chương trình khuyến mãi khác";
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

        $orderTrackingKeywords = [
            'tra cứu', 'tình trạng', 'đơn hàng', 'mã đơn hàng', 'tracking', 'theo dõi',
            'đơn hàng của tôi', 'trạng thái đơn hàng', 'mã vận đơn'
        ];
        $isOrderTrackingQuestion = false;
        foreach ($orderTrackingKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $isOrderTrackingQuestion = true;
                break;
            }
        }

        if ($isOrderTrackingQuestion && (strlen($response) < 100 || strpos($response, 'không biết') !== false)) {
            $response = "📦 **TRA CỨU ĐƠN HÀNG:**\n\n";
            $response .= "Để tra cứu tình trạng đơn hàng, vui lòng cung cấp:\n";
            $response .= "• Mã tra cứu đơn hàng (tracking code)\n";
            $response .= "• Hoặc mã đơn hàng của bạn\n\n";
            $response .= "**💡 Cách tra cứu:**\n";
            $response .= "1. Nhập mã tra cứu vào ô chat\n";
            $response .= "2. Tôi sẽ tìm kiếm thông tin đơn hàng cho bạn\n";
            $response .= "3. Hiển thị chi tiết tình trạng đơn hàng\n\n";
            $response .= "**📞 Hỗ trợ:** Nếu không có mã tra cứu, vui lòng liên hệ với chúng tôi!";
        }

        $buyKeywords = [
            'muốn mua', 'tôi mua', 'mua sản phẩm', 'thêm vào giỏ', 'đặt hàng', 'mua hàng'
        ];
        $isBuyQuestion = false;
        foreach ($buyKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $isBuyQuestion = true;
                break;
            }
        }

        if ($isBuyQuestion && isset($context['products']) && (is_array($context['products']) ? count($context['products']) : $context['products']->count()) > 0) {
            $response .= "\n\n**🛒 HƯỚNG DẪN MUA HÀNG:**\n";
            $response .= "Để thêm sản phẩm vào giỏ hàng:\n";
            $response .= "1. Chọn size phù hợp\n";
            $response .= "2. Chọn màu sắc yêu thích\n";
            $response .= "3. Nhập số lượng cần mua\n";
            $response .= "4. Nhấn nút \"Thêm vào giỏ hàng\"\n\n";
            $response .= "**✨ Sản phẩm sẽ được thêm vào giỏ hàng của bạn ngay lập tức!**";
        }
        
                                if ($this->isPurchaseIntent($userMessage) && isset($context['products']) && (is_array($context['products']) ? count($context['products']) : $context['products']->count()) > 0) {
                    $productCount = is_array($context['products']) ? count($context['products']) : $context['products']->count();
                    if ($productCount === 1) {
                        $product = is_array($context['products']) ? array_values($context['products'])[0] : $context['products']->count();
                        $response = "Dạ được ạ! Bây giờ bạn có thể chọn size, màu và số lượng để thêm {$product['name']} vào giỏ hàng.";
                    } else {
                        $response = "Dạ được ạ! Bây giờ bạn có thể chọn sản phẩm, size, màu và số lượng để thêm vào giỏ hàng.";
                    }
                    $context['show_purchase_form'] = true;
                }

                if ($this->isPurchaseIntent($userMessage) && (!isset($context['products']) || (is_array($context['products']) ? count($context['products']) : $context['products']->count()) === 0)) {
                    $response = "Bạn muốn mua sản phẩm gì ạ? Hãy cho tôi biết loại sản phẩm, giá cả hoặc từ khóa tìm kiếm để tôi có thể giúp bạn tìm sản phẩm phù hợp.";
                }
                
                $simplePurchaseKeywords = ['có', 'có ạ', 'dạ có', 'muốn', 'ok', 'được', 'thích', 'tôi muốn mua', 'có tôi muốn mua'];
                if (!empty($userMessage) && !in_array(trim(strtolower($userMessage)), $simplePurchaseKeywords) && isset($context['products']) && (is_array($context['products']) ? count($context['products']) : $context['products']->count()) > 0) {
                    $productCount = is_array($context['products']) ? count($context['products']) : $context['products']->count();
                    if ($productCount === 1) {
                        $product = is_array($context['products']) ? array_values($context['products'])[0] : $context['products']->first();
                        $response = "Dạ được ạ! Tôi đã tìm thấy {$product['name']}. Bây giờ bạn có thể chọn size, màu và số lượng để thêm vào giỏ hàng.";
                    } else {
                        $response = "Dạ được ạ! Tôi đã tìm thấy {$productCount} sản phẩm phù hợp. Bạn có thể chọn sản phẩm, size, màu và số lượng để thêm vào giỏ hàng.";
                    }
                    $context['show_purchase_form'] = true;
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

            $orderTrackingKeywords = [
                'tra cứu', 'tình trạng', 'đơn hàng', 'mã đơn hàng', 'tracking', 'theo dõi',
                'đơn hàng của tôi', 'trạng thái đơn hàng', 'mã vận đơn', 'mã tra cứu'
            ];
            $isOrderTrackingQuestion = false;
            foreach ($orderTrackingKeywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $isOrderTrackingQuestion = true;
                    break;
                }
            }

            if ($isOrderTrackingQuestion) {
                \Log::info('Order tracking question detected, returning empty product context');
                $context['products'] = collect([]);
                $context['coupons'] = collect([]);
                $context['flash_sales'] = collect([]);
                $context['order_tracking'] = true;
                return $context;
            }

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
                $activeFlashSales = FlashSale::with(['products.product'])
                    ->where('active', true)
                    ->where('end_time', '>', now())
                    ->orderBy('created_at', 'desc')
                    ->take(5)->get();
                
                \Log::info('Flash sale question detected, found active flash sales:', [
                    'count' => $activeFlashSales->count(),
                    'flash_sales' => $activeFlashSales->map(function($fs) {
                        return [
                            'id' => $fs->id,
                            'name' => $fs->name,
                            'start_time' => $fs->start_time,
                            'end_time' => $fs->end_time,
                            'description' => $fs->description,
                            'products_count' => $fs->products ? $fs->products->count() : 0
                        ];
                    })->toArray()
                ]);
                
                $context['flash_sales'] = $activeFlashSales;
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
            
            \Log::info('Searching by specific product for query: ' . $message, [
                'found_count' => $specificProducts ? $specificProducts->count() : 0,
                'products' => $specificProducts ? $specificProducts->pluck('name')->toArray() : []
            ]);
            
            if ($specificProducts && $specificProducts->count() > 0) {
                $priceRange = $this->extractPriceRange($message);
                if ($priceRange) {
                    \Log::info('Combining specific product search with price range:', $priceRange);
                    
                    $filteredByPrice = $specificProducts->filter(function ($product) use ($priceRange) {
                        $effectivePrice = ($product->discount_price && $product->discount_price > 0) 
                            ? $product->discount_price 
                            : $product->price;
                        
                        \Log::info("Price filter for product {$product->name}: original={$product->price}, discount={$product->discount_price}, effective={$effectivePrice}, range=" . json_encode($priceRange));
                        
                        if ($priceRange['type'] === 'above') {
                            return $effectivePrice >= $priceRange['amount'];
                        } elseif ($priceRange['type'] === 'below') {
                            return $effectivePrice <= $priceRange['amount'];
                        } else {
                            return $effectivePrice >= $priceRange['min'] && $effectivePrice <= $priceRange['max'];
                        }
                    });
                    
                    if ($filteredByPrice->count() > 0) {
                        $context['products'] = $filteredByPrice->take(5);
                        $context['coupons'] = collect([]);
                        $context['flash_sales'] = collect([]);
                        $context['price_range'] = $priceRange;
                        
                        \Log::info('Found relevant products with price filter for query: ' . $message, [
                            'count' => $filteredByPrice->count(),
                            'products' => $filteredByPrice->pluck('name')->toArray(),
                            'price_range' => $priceRange,
                            'query' => $message
                        ]);
                        
                        return $context;
                    }
                } else {
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
            }


            $priceRange = $this->extractPriceRange($message);
            if ($priceRange) {
                \Log::info('Searching by price range only:', $priceRange);
                

                $genderFilter = null;
                if (strpos($message, 'nam') !== false) {
                    $genderFilter = 'nam';
                } elseif (strpos($message, 'nữ') !== false) {
                    $genderFilter = 'nữ';
                }
                
                $priceProducts = (clone $productQuery);
                

                if ($priceRange['type'] === 'above') {
                    $priceProducts = $priceProducts->where(function ($q) use ($priceRange) {
                        $q->where(function ($subQ) use ($priceRange) {
                            $subQ->whereRaw('COALESCE(discount_price, price) >= ?', [$priceRange['amount']]);
                        });
                    });
                } elseif ($priceRange['type'] === 'below') {
                    $priceProducts = $priceProducts->where(function ($q) use ($priceRange) {
                        $q->where(function ($subQ) use ($priceRange) {
                            $subQ->whereRaw('COALESCE(discount_price, price) <= ?', [$priceRange['amount']]);
                        });
                    });
                } else {
                    $priceProducts = $priceProducts->where(function ($q) use ($priceRange) {
                        $q->where(function ($subQ) use ($priceRange) {
                            $subQ->whereRaw('COALESCE(discount_price, price) BETWEEN ? AND ?', [$priceRange['min'], $priceRange['max']]);
                        });
                    });
                }
                

                if ($genderFilter) {
                    $priceProducts = $priceProducts->where(function ($q) use ($genderFilter) {

                        $q->whereHas('categories', function ($catQ) use ($genderFilter) {
                            if ($genderFilter === 'nam') {
                                $catQ->where('name', 'like', '%nam%')
                                  ->where('name', 'not like', '%nữ%');
                            } elseif ($genderFilter === 'nữ') {
                                $catQ->where('name', 'like', '%nữ%')
                                  ->where('name', 'not like', '%nam%');
                            }
                        });
                        

                        if ($genderFilter === 'nam') {
                            $q->orWhere(function($nameQ) {
                                $nameQ->where('name', 'like', '%nam%')
                                      ->where('name', 'not like', '%nữ%');
                            });
                        } elseif ($genderFilter === 'nữ') {
                            $q->orWhere(function($nameQ) {
                                $nameQ->where('name', 'like', '%nữ%')
                                      ->where('name', 'not like', '%nam%');
                            });
                        }
                    });
                }
                
                $priceProducts = $priceProducts->orderByRaw('COALESCE(discount_price, price) ASC')->take(10)->get();
                
                \Log::info('Found products by price range only:', [
                    'count' => $priceProducts->count(),
                    'products' => $priceProducts->pluck('name', 'id')->toArray(),
                    'price_range' => $priceRange,
                    'gender_filter' => $genderFilter
                ]);

                if ($priceProducts->count() > 0) {
                    $context['products'] = $priceProducts;
                    $context['coupons'] = collect([]);
                    $context['flash_sales'] = collect([]);
                    $context['price_range'] = $priceRange;
                    
                    \Log::info('Found products by price range only for query: ' . $message, [
                        'count' => $priceProducts->count(),
                        'products' => $priceProducts->map(function($product) {
                            return [
                                'name' => $product->name,
                                'price' => $product->price,
                                'discount_price' => $product->discount_price,
                                'effective_price' => $product->discount_price ?? $product->price,
                                'categories' => $product->categories ? $product->categories->pluck('name')->toArray() : []
                            ];
                        })->toArray(),
                        'price_range' => $priceRange,
                        'gender_filter' => $genderFilter,
                        'query' => $message
                    ]);
                    
                    return $context;
                } else {
                    \Log::info('No products found matching price range and gender filter', [
                        'price_range' => $priceRange,
                        'gender_filter' => $genderFilter,
                        'query' => $message
                    ]);
                }
            }


            if (!isset($context['products']) || $context['products']->count() == 0) {
                \Log::info('No products found with price/specific search, returning empty context');
                

                \Log::info('No products found for any search method', [
                    'query' => $message
                ]);
                
                $context['products'] = collect([]);
                $context['coupons'] = collect([]);
                $context['flash_sales'] = collect([]);
                return $context;
            }

        } catch (\Exception $e) {
            \Log::error('getRelevantContext Error: ' . $e->getMessage());
            return [];
        }
    }

    private function filterRelevantProducts($products, $userMessage)
    {
        $message = strtolower($userMessage);
        $words = explode(' ', $message);
        
        $stopWords = ['tôi', 'muốn', 'mua', 'cần', 'tìm', 'ạ', 'à', 'và', 'hoặc', 'này', 'đó', 'kia', 'ôi', 'cho', 'với', 'trong', 'ngoài', 'trên', 'dưới', 'bên', 'của', 'là', 'thì', 'mà', 'nhưng', 'hoặc', 'vì', 'nên', 'để', 'từ', 'đến', 'tại', 'về', 'theo', 'cùng', 'cả', 'mỗi', 'mọi', 'mấy', 'bao', 'nhiêu', 'màu', 'gì', 'size', 'giá', 'bao', 'nhiêu'];
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
            $categoryNames = $product->categories ? $product->categories->pluck('name')->map(function($name) {
                return strtolower($name);
            })->toArray() : [];
            
            if (strpos($productName, $phrase) !== false) {
                \Log::info("Product {$product->name} is relevant (contains phrase: {$phrase})");
                return true;
            }
            
            $keywordMatches = 0;
            foreach ($keywords as $keyword) {
                if (strpos($productName, $keyword) !== false || in_array($keyword, $categoryNames)) {
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
        $stopWords = ['tôi', 'muốn', 'mua', 'cần', 'tìm', 'ạ', 'à', 'và', 'hoặc', 'này', 'đó', 'kia', 'ôi', 'cho', 'với', 'trong', 'ngoài', 'trên', 'dưới', 'bên', 'của', 'là', 'thì', 'mà', 'nhưng', 'hoặc', 'vì', 'nên', 'để', 'từ', 'đến', 'tại', 'về', 'theo', 'cùng', 'cả', 'mỗi', 'mọi', 'mấy', 'bao', 'nhiêu', 'màu', 'gì', 'size', 'giá', 'bao', 'nhiêu'];
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

    private function isPurchaseIntent($userMessage)
    {
        $message = strtolower($userMessage);
        
        \Log::info('Checking purchase intent for message:', ['message' => $userMessage]);
        
        $flashSalePatterns = [
            '/có\s+flash\s+sale/i',
            '/có\s+khuyến\s+mãi/i',
            '/có\s+sale/i',
            '/flash\s+sale/i',
            '/khuyến\s+mãi/i',
            '/sale\s+gì/i',
            '/khuyến\s+mãi\s+gì/i'
        ];
        
        foreach ($flashSalePatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                \Log::info('This is a flash sale question, NOT purchase intent:', ['message' => $userMessage, 'pattern' => $pattern]);
                return false;
            }
        }
        
        $searchPatterns = [
            '/tôi\s+muốn\s+mua\s+một\s+sản\s+phẩm/i',
            '/tôi\s+muốn\s+mua\s+.*\s+có\s+giá/i',
            '/tôi\s+muốn\s+mua\s+.*\s+dành\s+cho\s+nam/i',
            '/tôi\s+muốn\s+mua\s+.*\s+giá\s+dưới/i',
            '/tôi\s+muốn\s+mua\s+.*\s+600k/i',
            '/tôi\s+muốn\s+mua\s+.*\s+500k/i',
            '/tôi\s+muốn\s+mua\s+.*\s+400k/i'
        ];
        
        foreach ($searchPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                \Log::info('This is a product search message, NOT purchase intent:', ['message' => $userMessage, 'pattern' => $pattern]);
                return false;
            }
        }
        
        $specificProductPatterns = [
            '/tôi\s+muốn\s+thêm\s+.*\s+vào\s+giỏ\s+hàng/i',
            '/muốn\s+thêm\s+.*\s+vào\s+giỏ\s+hàng/i',
            '/thêm\s+.*\s+vào\s+giỏ\s+hàng/i',
            '/tôi\s+muốn\s+thêm\s+vào\s+giỏ\s+hàng/i',
            '/muốn\s+thêm\s+vào\s+giỏ\s+hàng/i',
            '/thêm\s+vào\s+giỏ\s+hàng/i'
        ];
        
        foreach ($specificProductPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                \Log::info('This is a specific product purchase intent:', ['message' => $userMessage, 'pattern' => $pattern]);
                return true;
            }
        }
        
        $purchaseKeywords = [
            'có', 'đồng ý', 'ok', 'được', 'thích', 'thêm', 'giỏ hàng',
            'dạ đúng', 'có muốn thêm', 'yes', 'okay', 'thích mua', 'chọn'
        ];
        
        foreach ($purchaseKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                \Log::info('Purchase intent detected by keyword:', ['keyword' => $keyword, 'message' => $userMessage]);
                return true;
            }
        }
        
        if (strpos($message, 'muốn mua') !== false) {
            $searchPatterns = [
                '/muốn\s+mua\s+.*\s+có\s+giá/i',
                '/muốn\s+mua\s+.*\s+dành\s+cho\s+nam/i',
                '/muốn\s+mua\s+.*\s+giá\s+dưới/i',
                '/muốn\s+mua\s+.*\s+600k/i',
                '/muốn\s+mua\s+.*\s+500k/i',
                '/muốn\s+mua\s+.*\s+400k/i',
                '/muốn\s+mua\s+áo\s+khoác/i',
                '/muốn\s+mua\s+áo\s+polo/i',
                '/muốn\s+mua\s+giày/i',
                '/muốn\s+mua\s+quần/i'
            ];
            
            $isSearchQuery = false;
            foreach ($searchPatterns as $pattern) {
                if (preg_match($pattern, $message)) {
                    $isSearchQuery = true;
                    break;
                }
            }
            
            if (!$isSearchQuery) {
                \Log::info('Purchase intent detected by "muốn mua" (not search):', ['message' => $userMessage]);
                return true;
            }
        }
        
        // Kiểm tra các từ khóa đơn giản thể hiện ý định mua hàng
        $simplePurchaseKeywords = ['có', 'có ạ', 'dạ có', 'muốn', 'ok', 'được', 'thích'];
        foreach ($simplePurchaseKeywords as $keyword) {
            if (trim(strtolower($message)) === $keyword) {
                \Log::info('Purchase intent detected by simple keyword:', ['message' => $userMessage, 'keyword' => $keyword]);
                return true;
            }
        }
        

        if (trim($message) === 'có' || trim($message) === 'có ạ' || trim($message) === 'dạ có') {
            \Log::info('Purchase intent detected by simple "có" response:', ['message' => $userMessage]);
            return true;
        }
        
        if (preg_match('/size\s+[smlx]/i', $message)) {
            \Log::info('Purchase intent detected by size:', ['message' => $userMessage]);
            return true;
        }
        
        if (preg_match('/màu\s+\w+/i', $message) || preg_match('/color\s+\w+/i', $message)) {
            \Log::info('Purchase intent detected by color:', ['message' => $userMessage]);
            return true;
        }
        
        if (preg_match('/\d+\s*(cái|chiếc|bộ)/i', $message)) {
            \Log::info('Purchase intent detected by quantity:', ['message' => $userMessage]);
            return true;
        }
        
        if (preg_match('/sản phẩm\s*\d+/i', $message) || 
            preg_match('/áo\s+polo\s+tay\s+ngắn/i', $message) ||
            preg_match('/áo\s+polo\s+sọc\s+ngang/i', $message) ||
            preg_match('/áo\s+khoác\s+nam\s+caro/i', $message)) {
            \Log::info('Purchase intent detected by specific product name/number:', ['message' => $userMessage]);
            return true;
        }
        
        \Log::info('No purchase intent detected:', ['message' => $userMessage]);
        return false;
    }

    private function processProductImages(&$context)
    {
        if (isset($context['products'])) {
            $productsCount = is_array($context['products']) ? count($context['products']) : $context['products']->count();
            if ($productsCount > 0) {
                \Log::info('Processing product images for ' . $productsCount . ' products');
                
                        if (!is_array($context['products'])) {
                    $context['products']->each(function ($product) {
                        \Log::info('Processing product: ' . $product->name, [
                            'has_main_image' => $product->mainImage ? 'YES' : 'NO',
                            'main_image_path' => $product->mainImage ? $product->mainImage->image_path : 'NULL',
                            'main_image_url' => $product->mainImage ? $product->mainImage->image_url : 'NULL'
                        ]);
                        
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
                    
                    if (isset($productArray['mainImage']) && isset($productArray['mainImage']['image_path'])) {
                        $imagePath = $productArray['mainImage']['image_path'];
                        if (!str_starts_with($imagePath, 'storage/')) {
                            $imagePath = 'storage/' . ltrim($imagePath, '/');
                        }
                        $productArray['mainImage']['image_url'] = url($imagePath);
                    }
                    
                    if (isset($productArray['variants']) && is_array($productArray['variants'])) {
                        $uniqueSizes = [];
                        $uniqueColors = [];
                        $availableVariants = [];
                        
                        foreach ($productArray['variants'] as $variant) {
                            if (isset($variant['inventory']) && ($variant['inventory']['quantity'] ?? 0) > 0) {
                                $availableVariants[] = $variant;
                                if (!in_array($variant['size'], $uniqueSizes)) {
                                    $uniqueSizes[] = $variant['size'];
                                }
                                if (!in_array($variant['color'], $uniqueColors)) {
                                    $uniqueColors[] = $variant['color'];
                                }
                            }
                        }
                        
                        $productArray['available_sizes'] = $uniqueSizes;
                        $productArray['available_colors'] = $uniqueColors;
                        $productArray['default_size'] = !empty($uniqueSizes) ? $uniqueSizes[0] : null;
                        $productArray['default_color'] = !empty($uniqueColors) ? $uniqueColors[0] : null;
                        $productArray['available_variants'] = $availableVariants;
                        
                        \Log::info('Processed variants for product: ' . $productArray['name'], [
                            'unique_sizes' => $uniqueSizes,
                            'unique_colors' => $uniqueColors,
                            'default_size' => $productArray['default_size'],
                            'default_color' => $productArray['default_color']
                        ]);
                    }
                    
                    \Log::info('Processed product: ' . $productArray['name'] . ' with mainImage: ' . ($productArray['mainImage'] ? 'yes' : 'no'));
                    
                    return $productArray;
                });
            }
            
            if (is_array($context['products'])) {
                foreach ($context['products'] as &$product) {
                    \Log::info('Processing array product: ' . $product['name'], [
                        'has_mainImage' => isset($product['mainImage']) ? 'YES' : 'NO',
                        'mainImage_path' => isset($product['mainImage']) ? ($product['mainImage']['image_path'] ?? 'NULL') : 'NULL',
                        'mainImage_url' => isset($product['mainImage']) ? ($product['mainImage']['image_url'] ?? 'NULL') : 'NULL'
                    ]);
                    
                    if (isset($product['mainImage']) && isset($product['mainImage']['image_path'])) {
                        $imagePath = $product['mainImage']['image_path'];
                        if (!str_starts_with($imagePath, 'storage/')) {
                            $imagePath = 'storage/' . ltrim($imagePath, '/');
                        }
                        $product['mainImage']['image_url'] = url($imagePath);
                        \Log::info('Set image_url for array product: ' . $product['name'] . ' - ' . $product['mainImage']['image_url']);
                    }
                    
                    if (isset($product['variants']) && is_array($product['variants'])) {
                        $uniqueSizes = [];
                        $uniqueColors = [];
                        $availableVariants = [];
                        
                        foreach ($product['variants'] as $variant) {
                            if (isset($variant['inventory']) && ($variant['inventory']['quantity'] ?? 0) > 0) {
                                $availableVariants[] = $variant;
                                if (!in_array($variant['size'], $uniqueSizes)) {
                                    $uniqueSizes[] = $variant['size'];
                                }
                                if (!in_array($variant['color'], $uniqueColors)) {
                                    $uniqueColors[] = $variant['color'];
                                }
                            }
                        }
                        
                        $product['available_sizes'] = $uniqueSizes;
                        $product['available_colors'] = $uniqueColors;
                        $product['default_size'] = !empty($uniqueSizes) ? $uniqueSizes[0] : null;
                        $product['default_color'] = !empty($uniqueColors) ? $uniqueColors[0] : null;
                        $product['available_variants'] = $availableVariants;
                    }
                }
            }
                
                $finalCount = is_array($context['products']) ? count($context['products']) : $context['products']->count();
                \Log::info('Finished processing ' . $finalCount . ' products');
            } else {
                \Log::info('No products to process images for');
            }
        }
    }

    private function searchBySpecificProduct($message, $productQuery)
    {
        $words = explode(' ', strtolower($message));

        $stopWords = ['tôi', 'muốn', 'mua', 'cần', 'tìm', 'ạ', 'à', 'và', 'hoặc', 'này', 'đó', 'kia', 'ôi', 'cho', 'với', 'trong', 'ngoài', 'trên', 'dưới', 'bên', 'của', 'là', 'thì', 'mà', 'nhưng', 'hoặc', 'vì', 'nên', 'để', 'từ', 'đến', 'tại', 'về', 'theo', 'cùng', 'cả', 'mỗi', 'mọi', 'mấy', 'bao', 'nhiêu', 'màu', 'gì', 'size', 'giá', 'bao', 'nhiêu', 'xem', 'thêm', 'còn', 'hàng', 'không'];
        $keywords = array_diff($words, $stopWords);

        if (empty($keywords)) {
            \Log::info('No keywords found after filtering stop words');
            return null;
        }

        $keywords = array_filter($keywords, function($keyword) {
            return strlen(trim($keyword)) >= 2;
        });

        \Log::info('Search keywords:', $keywords);

        $genderKeywords = ['nam', 'nữ', 'nam giới', 'nữ giới', 'đàn ông', 'phụ nữ', 'con trai', 'con gái'];
        $targetGender = null;
        
        foreach ($genderKeywords as $genderKeyword) {
            if (in_array($genderKeyword, $keywords)) {
                $targetGender = $genderKeyword;
                break;
            }
        }
        
        if (!$targetGender) {
            if (strpos($message, 'nam') !== false) {
                $targetGender = 'nam';
            } elseif (strpos($message, 'nữ') !== false) {
                $targetGender = 'nữ';
            }
        }
        
        \Log::info('Target gender detected:', ['gender' => $targetGender, 'message' => $message]);

        $foundProducts = collect();
        $phrase = implode(' ', $keywords);

        \Log::info("First trying to search with phrase: {$phrase}");
        
        if (strlen($phrase) <= 10) {
            $productsByPhrase = (clone $productQuery)->where('name', 'like', "%{$phrase}%")->get();
            if ($productsByPhrase->count() > 0) {
                $foundProducts = $foundProducts->merge($productsByPhrase);
                \Log::info("Found {$productsByPhrase->count()} products by phrase in name: {$phrase}", [
                    'products' => $productsByPhrase->pluck('name')->toArray()
                ]);
            }
        }
        
        if ($foundProducts->count() == 0 && strlen($phrase) <= 10) {
            $productsByDescription = (clone $productQuery)->where('description', 'like', "%{$phrase}%")->get();
            if ($productsByDescription->count() > 0) {
                $foundProducts = $foundProducts->merge($productsByDescription);
                \Log::info("Found {$productsByDescription->count()} products by phrase in description: {$phrase}", [
                    'products' => $productsByDescription->pluck('name')->toArray()
                ]);
            }
        }
        
        if ($foundProducts->count() == 0) {
            $mainProductKeywords = ['giày', 'giay', 'áo', 'ao', 'quần', 'quan', 'váy', 'vay', 'túi', 'tui', 'mũ', 'mu', 'nón', 'non'];
            
            $foundMainKeyword = null;
            foreach ($mainProductKeywords as $keyword) {
                if (stripos($phrase, $keyword) !== false) {
                    $foundMainKeyword = $keyword;
                    break;
                }
            }
            
            if ($foundMainKeyword) {
                $productsByKeyword = (clone $productQuery)->where('name', 'like', "%{$foundMainKeyword}%")->get();
                if ($productsByKeyword->count() > 0) {
                    $foundProducts = $foundProducts->merge($productsByKeyword);
                    \Log::info("Found {$productsByKeyword->count()} products by main keyword '{$foundMainKeyword}' in name", [
                        'products' => $productsByKeyword->pluck('name')->toArray()
                    ]);
                }
            } else {
                \Log::info("No main product keyword found in phrase: {$phrase}");
            }
        }
        
        if ($foundProducts->count() === 0) {
            \Log::info("No products found with main keywords, search failed");
        }

        if ($targetGender && $foundProducts->count() > 0) {
            $filteredByGender = $foundProducts->filter(function ($product) use ($targetGender) {
                $productName = strtolower($product->name);
                $categoryNames = $product->categories ? $product->categories->pluck('name')->map(function($name) {
                    return strtolower($name);
                })->toArray() : [];
                $description = strtolower($product->description ?? '');
                
                // Từ khóa nam
                if (in_array($targetGender, ['nam', 'nam giới', 'đàn ông', 'con trai'])) {
                    $hasNam = strpos($productName, 'nam') !== false || 
                              in_array('nam', $categoryNames) ||
                              strpos($description, 'nam') !== false;
                    $hasNu = strpos($productName, 'nữ') !== false || 
                             in_array('nữ', $categoryNames) ||
                             strpos($description, 'nữ') !== false;
                    
                    \Log::info("Gender filter for product {$product->name}:", [
                        'target_gender' => $targetGender,
                        'has_nam' => $hasNam,
                        'has_nu' => $hasNu,
                        'category_names' => $categoryNames,
                        'result' => $hasNam && !$hasNu
                    ]);
                    
                    return $hasNam && !$hasNu;
                }
                

                if (in_array($targetGender, ['nữ', 'nữ giới', 'phụ nữ', 'con gái'])) {
                    // Kiểm tra có chứa từ "nữ" và KHÔNG chứa từ "nam"
                    $hasNu = strpos($productName, 'nữ') !== false || 
                             in_array('nữ', $categoryNames) ||
                             strpos($description, 'nữ') !== false;
                    $hasNam = strpos($productName, 'nam') !== false || 
                              in_array('nam', $categoryNames) ||
                              strpos($description, 'nam') !== false;
                    
                    \Log::info("Gender filter for product {$product->name}:", [
                        'target_gender' => $targetGender,
                        'has_nam' => $hasNam,
                        'has_nu' => $hasNu,
                        'category_names' => $categoryNames,
                        'result' => $hasNu && !$hasNam
                    ]);
                    
                    return $hasNu && !$hasNam;
                }
                
                return true;
            });
            
            if ($filteredByGender->count() > 0) {
                $foundProducts = $filteredByGender;
                \Log::info("Filtered products by gender '{$targetGender}':", [
                    'count' => $filteredByGender->count(),
                    'products' => $filteredByGender->pluck('name')->toArray()
                ]);
            } else {
                \Log::info("No products found after gender filtering for '{$targetGender}'");
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
                
                // Tăng điểm cho phrase match chính xác
                if (strpos($productName, $phrase) !== false) {
                    $score += 500; // Tăng lên 500 để ưu tiên tuyệt đối
                    \Log::info("Product {$product->name} got +500 points for exact phrase match in name");
                }
                
                // Tăng điểm cho từng từ trong phrase
                $phraseWords = explode(' ', $phrase);
                $matchedWords = 0;
                foreach ($phraseWords as $word) {
                    if (strlen($word) >= 3 && strpos($productName, $word) !== false) {
                        $score += 100; // Tăng điểm cho từng từ match
                        $matchedWords++;
                        \Log::info("Product {$product->name} got +100 points for word '{$word}' in name");
                    }
                }
                
                // Bonus cho nhiều từ match
                if ($matchedWords >= 2) {
                    $score += 200;
                    \Log::info("Product {$product->name} got +200 bonus for multiple word matches ({$matchedWords} words)");
                }
                
                // KHÔNG cho điểm cho category match - chỉ ưu tiên tên sản phẩm
                // if (strpos($categoryName, $phrase) !== false) {
                //     $score += 80; 
                //     \Log::info("Product {$product->name} got +80 points for phrase match in category");
                // }
                
                // CHỈ cho điểm cho tên sản phẩm và mô tả - KHÔNG cho điểm cho category
                foreach ($keywords as $keyword) {
                    if (strpos($productName, $keyword) !== false) {
                        // Kiểm tra nếu là từ khóa sản phẩm chính thì cho điểm cao hơn
                        $mainProductKeywords = ['giày', 'giay', 'áo', 'ao', 'quần', 'quan', 'váy', 'vay', 'túi', 'tui', 'mũ', 'mu', 'nón', 'non'];
                        if (in_array($keyword, $mainProductKeywords)) {
                            $score += 150; // Điểm cao cho từ khóa sản phẩm chính
                            \Log::info("Product {$product->name} got +150 points for main product keyword '{$keyword}' in name");
                        } else {
                            $score += 50; // Điểm thường cho từ khóa khác
                            \Log::info("Product {$product->name} got +50 points for keyword '{$keyword}' in name");
                        }
                    }
                    if (strpos($productDescription, $keyword) !== false) {
                        $score += 30; // Giảm điểm cho mô tả
                        \Log::info("Product {$product->name} got +30 points for keyword '{$keyword}' in description");
                    }
                    // KHÔNG cho điểm cho category match
                    // if (strpos($categoryName, $keyword) !== false) {
                    //     $score += 20; 
                    //     \Log::info("Product {$product->name} got +20 points for keyword '{$keyword}' in category");
                    // }
                }
                
                \Log::info("Product: {$product->name}, Final Score: {$score}");
                
                return ['product' => $product, 'score' => $score];
            });
            
            $relevantProducts = $scoredProducts
                ->filter(function ($item) {
                    return $item['score'] >= 100; // Tăng ngưỡng điểm để chỉ lấy sản phẩm thực sự liên quan
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
                

                if (strpos(strtolower($message), 'còn hàng') !== false || 
                    strpos(strtolower($message), 'có hàng') !== false ||
                    strpos(strtolower($message), 'tồn kho') !== false) {
                    
                    $hasStock = false;
                    if ($topProduct->variants && $topProduct->variants->count() > 0) {
                        foreach ($topProduct->variants as $variant) {
                            if ($variant->inventory && ($variant->inventory->quantity ?? 0) > 0) {
                                $hasStock = true;
                                break;
                            }
                        }
                    } else {
                        // Nếu không có variants, coi như có hàng
                        $hasStock = true;
                    }
                    
                    if (!$hasStock) {
                        \Log::info("Product {$topProduct->name} is out of stock");
                        return collect([]);
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
        $stopWords = ['tôi', 'muốn', 'mua', 'cần', 'tìm', 'ạ', 'à', 'và', 'hoặc', 'này', 'đó', 'kia', 'ôi', 'cho', 'với', 'trong', 'ngoài', 'trên', 'dưới', 'bên', 'của', 'là', 'thì', 'mà', 'nhưng', 'hoặc', 'vì', 'nên', 'để', 'từ', 'đến', 'tại', 'về', 'theo', 'cùng', 'cả', 'mỗi', 'mọi', 'mấy', 'bao', 'nhiêu', 'màu', 'gì', 'size', 'giá', 'bao', 'nhiêu'];
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
        $genderFilter = null;

        // Kiểm tra filter theo giới tính
        if (strpos($message, 'nam') !== false) {
            $genderFilter = 'nam';
        } elseif (strpos($message, 'nữ') !== false) {
            $genderFilter = 'nữ';
        }

        foreach ($keywords as $keyword) {
            $keyword = trim($keyword);
            
            // Tìm theo danh mục với filter giới tính
            $productsByCategory = (clone $productQuery)->whereHas('categories', function ($q) use ($keyword, $genderFilter) {
                $q->where('name', 'like', "%{$keyword}%");
                
                // Nếu có filter giới tính, thêm điều kiện
                if ($genderFilter) {
                    if ($genderFilter === 'nam') {
                        $q->where('name', 'like', '%nam%')
                          ->where('name', 'not like', '%nữ%');
                    } elseif ($genderFilter === 'nữ') {
                        $q->where('name', 'like', '%nữ%')
                          ->where('name', 'not like', '%nam%');
                    }
                }
            })->get();
            
            // Filter theo giới tính sau khi tìm theo danh mục
            if ($genderFilter && $productsByCategory->count() > 0) {
                $productsByCategory = $productsByCategory->filter(function ($product) use ($genderFilter) {
                    $productName = strtolower($product->name);
                    $categoryNames = $product->categories ? $product->categories->pluck('name')->map(function($name) {
                        return strtolower($name);
                    })->toArray() : [];
                    $description = strtolower($product->description ?? '');
                    
                    if ($genderFilter === 'nam') {
                        // Kiểm tra có chứa từ "nam" và KHÔNG chứa từ "nữ"
                        $hasNam = strpos($productName, 'nam') !== false || 
                                  in_array('nam', $categoryNames) ||
                                  strpos($description, 'nam') !== false;
                        $hasNu = strpos($productName, 'nữ') !== false || 
                                 in_array('nữ', $categoryNames) ||
                                 strpos($description, 'nữ') !== false;
                        
                        return $hasNam && !$hasNu;
                    } elseif ($genderFilter === 'nữ') {
                        // Kiểm tra có chứa từ "nữ" và KHÔNG chứa từ "nam"
                        $hasNu = strpos($productName, 'nữ') !== false || 
                                 in_array('nữ', $categoryNames) ||
                                 strpos($description, 'nữ') !== false;
                        $hasNam = strpos($productName, 'nam') !== false || 
                                  in_array('nam', $categoryNames) ||
                                  strpos($description, 'nam') !== false;
                        
                        return $hasNu && !$hasNam;
                    }
                    
                    return true;
                });
            }
            
            if ($productsByCategory->count() > 0) {
                $foundProducts = $foundProducts->merge($productsByCategory);
                \Log::info("Found {$productsByCategory->count()} products by category keyword: {$keyword}", [
                    'products' => $productsByCategory->pluck('name')->toArray(),
                    'gender_filter' => $genderFilter
                ]);
            }
        }

        if ($foundProducts->count() > 0) {
            $uniqueProducts = $foundProducts->unique('id')->take(5); // Tăng lên 5 sản phẩm
            \Log::info("Returning {$uniqueProducts->count()} category products", [
                'total_found' => $foundProducts->count(),
                'unique_count' => $uniqueProducts->count(),
                'products' => $uniqueProducts->pluck('name')->toArray(),
                'gender_filter' => $genderFilter
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