<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Variants;
use App\Models\Inventory;
use App\Models\FlashSale;
use Illuminate\Support\Facades\Auth;

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
                $flashSale = FlashSale::whereHas('products', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                    ->where('active', true)
                    ->where('start_time', '<=', now())
                    ->where('end_time', '>=', now())
                    ->first();
                if ($flashSale) {
                    $fsp = $flashSale->products->firstWhere('product_id', $product->id);
                    if ($fsp) {
                        $flashPrice = (int) $fsp->flash_price;
                        $remaining = (int) $fsp->quantity; // quantity là tồn còn lại thực tế
                        // Chỉ gắn nhãn flash sale cho item nếu giá của item == giá flash (hoặc thấp hơn)
                        if ((int) $cart->price <= $flashPrice) {
                            $cart->setAttribute('flash_sale', [
                                'id' => $flashSale->id,
                                'name' => $flashSale->name,
                                'flash_price' => $flashPrice,
                                'remaining' => $remaining,
                                'end_time' => $flashSale->end_time,
                            ]);
                        }
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

        $variant = Variants::with('inventory')->findOrFail($request->variant_id);

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

        $data = [
            'variant_id' => $variant->id,
            'quantity' => $request->quantity,
            'price' => $price,
        ];

        if (Auth::check()) {
            $data['user_id'] = Auth::id();
            $data['session_id'] = null;
        } else {
            $data['session_id'] = $request->header('X-Session-Id');
            $data['user_id'] = null;
        }

        // Phân biệt theo giá để tách sản phẩm sale và không sale thành 2 dòng riêng
        $item = Cart::where('variant_id', $variant->id)
            ->where('price', $price)
            ->where(function ($q) use ($data) {
                if (isset($data['user_id']) && $data['user_id']) $q->where('user_id', $data['user_id']);
                else $q->where('session_id', $data['session_id']);
            })
            ->first();

        if ($item) {
            // Cùng biến thể và cùng giá: cộng dồn số lượng
            $item->quantity += $data['quantity'];
            if (isset($data['user_id']) && $data['user_id']) {
                $item->user_id = $data['user_id'];
                $item->session_id = null;
            }
            $item->save();
        } else {
            $item = Cart::create($data);
        }

        // Hợp nhất nếu có nhiều dòng trùng nhau theo cặp (variant_id, price)
        $dups = Cart::where('variant_id', $variant->id)
            ->where('price', $price)
            ->where(function ($q) use ($data) {
                if (isset($data['user_id']) && $data['user_id']) $q->where('user_id', $data['user_id']);
                else $q->where('session_id', $data['session_id']);
            })
            ->orderBy('id')
            ->get();
        if ($dups->count() > 1) {
            $keep = $dups->first();
            $keep->quantity = $dups->sum('quantity');
            $keep->save();
            $idsToDelete = $dups->pluck('id')->filter(fn($id) => $id !== $keep->id);
            if ($idsToDelete->isNotEmpty()) {
                Cart::whereIn('id', $idsToDelete)->delete();
            }
            $item = $keep;
        }

        return response()->json($item->fresh(), 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $item = Cart::findOrFail($id);
        $variant = Variants::with('inventory')->findOrFail($item->variant_id);

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
}
