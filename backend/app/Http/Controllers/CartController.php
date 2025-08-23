<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Variants;
use App\Models\Inventory;
use App\Models\FlashSale;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CartController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Cart::with([
                'variant.product.mainImage',
                'variant.product.brand',
                'variant.inventory'
            ]);

            if (Auth::check()) {
                $query->where('user_id', Auth::id());
            } else {
                $sessionId = $request->header('X-Session-Id');
                if (!$sessionId) {
                    return response()->json([]);
                }
                $query->where('session_id', $sessionId);
            }

            $carts = $query->get();

            foreach ($carts as $cart) {
                if ($cart->variant && $cart->variant->product && $cart->variant->product->mainImage) {
                    $path = $cart->variant->product->mainImage->image_path;
                    if (preg_match('/^https?:\/\//', $path)) {
                        $cart->variant->product->mainImage->image_path = $path;
                    } elseif (strpos($path, '/storage/') === 0) {
                        $cart->variant->product->mainImage->image_path = $path;
                    } elseif (strpos($path, 'storage/') === 0) {
                        $cart->variant->product->mainImage->image_path = '/' . $path;
                    } else {
                        $cart->variant->product->mainImage->image_path = '/storage/' . ltrim($path, '/');
                    }
                }
            }

            // Bổ sung thông tin flash sale còn lại (KHÔNG tự động giảm giá)
            foreach ($carts as $cart) {
                $product = $cart->variant->product ?? null;
                if (!$product) continue;
                $flashSales = FlashSale::with('products')
                    ->whereHas('products', function ($q) use ($product) {
                        $q->where('product_id', $product->id);
                    })
                    ->where('active', true)
                    ->where('start_time', '<=', now())
                    ->where('end_time', '>=', now())
                    ->get();
                foreach ($flashSales as $fs) {
                    $fsp = $fs->products->firstWhere('product_id', $product->id);
                    if (!$fsp) continue;
                    $campaignFlashPrice = (int) $fsp->flash_price;
                    $remaining = (int) $fsp->quantity;
                    $productBasePrice = (int) ($product->price ?? 0);
                    $variantBasePrice = (int) ($cart->variant->price ?? 0);
                    if ($productBasePrice <= 0 || $variantBasePrice <= 0) continue;
                    $ratio = $campaignFlashPrice / $productBasePrice;
                    $expectedVariantFlashPrice = (int) round($variantBasePrice * $ratio);
                    if (abs(((int) $cart->price) - $expectedVariantFlashPrice) <= 1 || (int) $cart->price === $campaignFlashPrice) {
                        $cart->setAttribute('flash_sale', [
                            'id' => $fs->id,
                            'name' => $fs->name,
                            'flash_price' => $campaignFlashPrice,
                            'remaining' => $remaining,
                            'end_time' => $fs->end_time,
                        ]);
                        break;
                    }
                }
            }

            return response()->json($carts);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Đã xảy ra lỗi khi lấy dữ liệu giỏ hàng'], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $variant = Variants::with(['inventory', 'product'])->findOrFail($request->variant_id);

        if (!$variant->inventory) {
            return response()->json(['error' => 'Không thể xác định số lượng tồn kho'], 422);
        }

        if ($request->quantity > $variant->inventory->quantity) {
            return response()->json([
                'error' => 'Số lượng vượt quá tồn kho',
                'available_quantity' => $variant->inventory->quantity
            ], 422);
        }

        $price = $request->has('price') ? $request->price : $variant->price;

        // Nếu là hàng Flash Sale (giá đúng bằng flash_price) thì kiểm tra thêm quota sale
        $product = $variant->product;
        if ($product) {
            $flashSales = FlashSale::with('products')
                ->whereHas('products', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->where('active', true)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->get();
            foreach ($flashSales as $fs) {
                $fsp = $fs->products->firstWhere('product_id', $product->id);
                if (!$fsp) continue;
                $productBasePrice = (int) ($product->price ?? 0);
                $variantBasePrice = (int) ($variant->price ?? 0);
                if ($productBasePrice <= 0 || $variantBasePrice <= 0) continue;
                $ratio = ((int) $fsp->flash_price) / $productBasePrice;
                $expectedVariantFlashPrice = (int) round($variantBasePrice * $ratio);
                if ((int) $price === $expectedVariantFlashPrice) {
                    if ($request->quantity > (int) $fsp->quantity) {
                        return response()->json([
                            'error' => 'Số lượng vượt quá số lượng Flash Sale còn lại',
                            'available_quantity' => (int) $fsp->quantity
                        ], 422);
                    }
                    break;
                }
            }
        }

        $data = [
            'variant_id' => $variant->id,
            'quantity' => $request->quantity,
            'price' => $price,
        ];

        if (Auth::check()) {
            $data['user_id'] = Auth::id();
            $data['session_id'] = null;
        } else {
            $sessionId = $request->header('X-Session-Id');
            if (!$sessionId) {
                return response()->json(['error' => 'Session ID là bắt buộc cho guest users'], 400);
            }
            $data['session_id'] = $sessionId;
            $data['user_id'] = null;
        }

        // Tìm item hiện có để hợp nhất
        $existingItem = null;
        if (Auth::check()) {
            $existingItem = Cart::where('user_id', Auth::id())
                ->where('variant_id', $variant->id)
                ->where('price', $price)
                ->first();
        } else {
            $existingItem = Cart::where('session_id', $data['session_id'])
                ->where('variant_id', $variant->id)
                ->where('price', $price)
                ->first();
        }

        if ($existingItem) {
            // Cập nhật số lượng cho item hiện có
            $existingItem->quantity += $request->quantity;
            $existingItem->save();
            $item = $existingItem;
        } else {
            // Tạo item mới
            $item = Cart::create($data);
        }

        return response()->json($item->fresh(), 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $item = Cart::findOrFail($id);
        $variant = Variants::with(['inventory', 'product'])->findOrFail($item->variant_id);

        if (Auth::check() && $item->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$variant->inventory) {
            return response()->json(['error' => 'Không thể xác định số lượng tồn kho'], 422);
        }

        if ($request->quantity > $variant->inventory->quantity) {
            return response()->json([
                'error' => 'Số lượng vượt quá tồn kho',
                'available_quantity' => $variant->inventory->quantity
            ], 422);
        }

        // Kiểm tra quota Flash Sale nếu item có giá đúng bằng flash_price
        $product = $variant->product;
        if ($product) {
            $flashSales = FlashSale::with('products')
                ->whereHas('products', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->where('active', true)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->get();
            foreach ($flashSales as $fs) {
                $fsp = $fs->products->firstWhere('product_id', $product->id);
                if (!$fsp) continue;
                $productBasePrice = (int) ($product->price ?? 0);
                $variantBasePrice = (int) ($variant->price ?? 0);
                if ($productBasePrice <= 0 || $variantBasePrice <= 0) continue;
                $ratio = ((int) $fsp->flash_price) / $productBasePrice;
                $expectedVariantFlashPrice = (int) round($variantBasePrice * $ratio);
                if ((int) $item->price === $expectedVariantFlashPrice) {
                    if ($request->quantity > (int) $fsp->quantity) {
                        return response()->json([
                            'error' => 'Số lượng vượt quá số lượng Flash Sale còn lại',
                            'available_quantity' => (int) $fsp->quantity
                        ], 422);
                    }
                    break;
                }
            }
        }

        $item->quantity = $request->quantity;
        $item->save();

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = Cart::findOrFail($id);

        if (Auth::check() && $item->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function transferCartFromSessionToUser(Request $request)
    {
        $sessionId = $request->header('X-Session-Id');
        $userId = Auth::id();
        if (!$sessionId || !$userId) {
            return response()->json(['error' => 'Session ID và User ID là bắt buộc'], 400);
        }
        
        $sessionCarts = Cart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->get();
            
        foreach ($sessionCarts as $sessionCart) {
            $existingCart = Cart::where('user_id', $userId)
                ->where('variant_id', $sessionCart->variant_id)
                ->where('price', $sessionCart->price) // Thêm điều kiện price để tránh trùng lặn
                ->first();
                
            if ($existingCart) {
                $existingCart->quantity += $sessionCart->quantity;
                $existingCart->save();
                $sessionCart->delete();
            } else {
                $sessionCart->update([
                    'user_id' => $userId,
                    'session_id' => null
                ]);
            }
        }
        
        return response()->json(['message' => 'Cart items transferred successfully']);
    }

    public function cleanupOldCartItems()
    {
        try {
            // Xóa các cart items cũ (older than 30 days) của guest users
            $deletedCount = Cart::whereNotNull('session_id')
                ->whereNull('user_id')
                ->where('created_at', '<', Carbon::now()->subDays(30))
                ->delete();
                
            return response()->json([
                'message' => 'Old cart items cleaned up successfully',
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to cleanup old cart items',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}