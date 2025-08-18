<?php

namespace App\Http\Controllers;

use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB as FacadesDB;
use App\Models\Orders_detail;
use App\Models\Variants;
use App\Models\Inventory;
use Illuminate\Support\Facades\Auth;

class FlashSaleController extends Controller
{
    public function index()
    {
        $cacheKey = 'flash_sales_index';
        $cacheTTL = 300; // 5 phút

        $flashSales = Cache::tags(['flash_sales'])->remember($cacheKey, $cacheTTL, function () {
            $data = FlashSale::with([
                'products.product.mainImage',
                'products.product.categories',
                'products.product.brand',
                'products.product.variants',
            ])->get();

            foreach ($data as $fs) {
                foreach ($fs->products as $p) {
                    if ($p->product) {
                        $p->product->flash_sale_quantity = $p->quantity;
                        $p->product->flash_sale_sold = $p->sold;
                        $p->product->flash_price = $p->flash_price;
                    }
                }
            }
            return $data;
        });

        return response()->json($flashSales);
    }

    // Thống kê bán thật và doanh thu thật theo toàn bộ hoặc theo id
    public function statistics($id = null)
    {
        $query = FlashSale::query();
        if ($id) $query->where('id', $id);
        $flashSales = $query->with('products')->get();

        $stats = [];
        foreach ($flashSales as $fs) {
            $sold = 0; $revenue = 0;
            foreach ($fs->products as $p) {
                // Chỉ đếm các order item có đơn giá <= flash_price (tức là mua theo giá flash)
                $row = DB::table('orders_details as od')
                    ->join('orders as o', 'o.id', '=', 'od.order_id')
                    ->join('variants as v', 'v.id', '=', 'od.variant_id')
                    ->where('v.product_id', $p->product_id)
                    ->whereBetween('o.created_at', [$fs->start_time, $fs->end_time])
                    ->where(function ($q) {
                        $q->where('o.payment_status', 'paid')
                          ->orWhere('o.status', 'completed');
                    })
                    ->where('od.price', '<=', $p->flash_price)
                    ->selectRaw('SUM(od.quantity) as qty, SUM(od.quantity * od.price) as rev')
                    ->first();
                $sold += (int)($row->qty ?? 0);
                $revenue += (int)($row->rev ?? 0);
            }
            $stats[] = [
                'id' => $fs->id,
                'name' => $fs->name,
                'sold_real' => $sold,
                'revenue_real' => $revenue,
            ];
        }
        return response()->json($id ? ($stats[0] ?? []) : $stats);
    }

    // Bật/tắt chiến dịch
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['active' => 'required|boolean']);
        $fs = FlashSale::findOrFail($id);
        $fs->active = $request->active;
        $fs->save();
        Cache::tags(['flash_sales'])->flush();
        return response()->json(['success' => true, 'active' => $fs->active]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validate([
                'name' => 'required',
                'start_time' => 'required|date',
                'end_time' => 'required|date',
                'repeat' => 'boolean',
                'repeat_minutes' => 'nullable|integer',
                'auto_increase' => 'boolean',
                'active' => 'boolean',
                'products' => 'required|array',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.flash_price' => 'required|numeric',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.sold' => 'nullable|integer|min:0',
                'products.*.variant_quantities' => 'nullable|array',
                'products.*.variant_quantities.*.variant_id' => 'required_with:products.*.variant_quantities|exists:variants,id',
                'products.*.variant_quantities.*.qty' => 'required_with:products.*.variant_quantities|integer|min:0',
            ]);
            foreach ($data['products'] as $prod) {
                $requiredPerVariant = (int) $prod['quantity'];
                $variants = Variants::where('product_id', (int) $prod['product_id'])->get();
                if ($variants->isEmpty()) continue;
                $insufficient = [];
                foreach ($variants as $variant) {
                    $available = (int) (Inventory::where('variant_id', $variant->id)->value('quantity') ?? 0);
                    if ($available < $requiredPerVariant) {
                        $insufficient[] = [
                            'variant_id' => $variant->id,
                            'size' => $variant->size,
                            'color' => $variant->color,
                            'available' => $available,
                        ];
                    }
                }
                if (!empty($insufficient)) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'Số lượng flash sale vượt quá tồn kho của một số biến thể',
                        'product_id' => (int) $prod['product_id'],
                        'required_per_variant' => $requiredPerVariant,
                        'insufficient_variants' => $insufficient,
                    ], 422);
                }
            }

            $flashSale = FlashSale::create($data);
            foreach ($data['products'] as $prod) {
                FlashSaleProduct::create([
                    'flash_sale_id' => $flashSale->id,
                    'product_id' => (int)$prod['product_id'],
                    'flash_price' => $prod['flash_price'],
                    'quantity' => (int)$prod['quantity'],
                    'sold' => $prod['sold'] ?? 0,
                ]);
            }
            DB::commit();
            Cache::tags(['flash_sales'])->flush();
            return response()->json(['success' => true, 'flash_sale' => $flashSale->load('products.product')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $flashSale = FlashSale::findOrFail($id);
            $data = $request->validate([
                'name' => 'required',
                'start_time' => 'required|date',
                'end_time' => 'required|date',
                'repeat' => 'boolean',
                'repeat_minutes' => 'nullable|integer',
                'auto_increase' => 'boolean',
                'active' => 'boolean',
                'products' => 'required|array',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.flash_price' => 'required|numeric',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.sold' => 'nullable|integer|min:0',
            ]);
            foreach ($data['products'] as $prod) {
                $requiredPerVariant = (int) $prod['quantity'];
                $variants = Variants::where('product_id', (int) $prod['product_id'])->get();
                if ($variants->isEmpty()) continue;
                $insufficient = [];
                foreach ($variants as $variant) {
                    $available = (int) (Inventory::where('variant_id', $variant->id)->value('quantity') ?? 0);
                    if ($available < $requiredPerVariant) {
                        $insufficient[] = [
                            'variant_id' => $variant->id,
                            'size' => $variant->size,
                            'color' => $variant->color,
                            'available' => $available,
                        ];
                    }
                }
                if (!empty($insufficient)) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'Số lượng flash sale vượt quá tồn kho của một số biến thể, hãy kiểm tra số lượng tồn kho trước khi cập nhật',
                        'product_id' => (int) $prod['product_id'],
                        'required_per_variant' => $requiredPerVariant,
                        'insufficient_variants' => $insufficient,
                    ], 422);
                }
            }

            $flashSale->products()->delete();
            $flashSale->update($data);
            // Ghi lại danh sách sản phẩm sale (không tác động kho, quantity là áp cho MỖI size)
            foreach ($data['products'] as $prod) {
                FlashSaleProduct::create([
                    'flash_sale_id' => $flashSale->id,
                    'product_id' => (int)$prod['product_id'],
                    'flash_price' => $prod['flash_price'],
                    'quantity' => (int)$prod['quantity'],
                    'sold' => $prod['sold'] ?? 0,
                ]);
            }
            DB::commit();
            Cache::tags(['flash_sales'])->flush();
            return response()->json(['success' => true, 'flash_sale' => $flashSale->load('products.product')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $flashSale = FlashSale::with('products')->findOrFail($id);
            // Không tác động xuất/nhập kho khi xóa campaign. Chỉ xóa cấu hình.
            $flashSale->products()->delete();
            $flashSale->delete();
            DB::commit();
            Cache::tags(['flash_sales'])->flush();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        $cacheKey = "flash_sale_show_{$id}";
        $cacheTTL = 300; // 5 phút

        $flashSale = Cache::tags(['flash_sales'])->remember($cacheKey, $cacheTTL, function () use ($id) {
            $fs = FlashSale::with([
                'products.product.mainImage',
                'products.product.categories',
                'products.product.brand',
                'products.product.variants',
            ])->findOrFail($id);

            foreach ($fs->products as $p) {
                if ($p->product) {
                    $p->product->flash_sale_quantity = $p->quantity;
                    $p->product->flash_sale_sold = $p->sold;
                    $p->product->flash_price = $p->flash_price;
                }
            }
            return $fs;
        });

        return response()->json($flashSale);
    }
}
