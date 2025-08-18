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

    public function statistics($id = null)
    {
        $query = FlashSale::query();
        if ($id) $query->where('id', $id);
        $flashSales = $query->with('products')->get();

        $stats = [];
        foreach ($flashSales as $fs) {
            $sold = 0; $revenue = 0;

            $lastImportIds = DB::table('stock_movement_items as smi')
                ->join('stock_movements as sm', 'sm.id', '=', 'smi.stock_movement_id')
                ->where('sm.type', 'import')
                ->where('sm.created_at', '<=', $fs->end_time)
                ->select(
                    'smi.variant_id',
                    DB::raw('MAX(smi.id) as last_smi_id')
                )
                ->groupBy('smi.variant_id');

            $costSub = DB::table('stock_movement_items as last_smi')
                ->joinSub($lastImportIds, 'latest', function ($join) {
                    $join->on('latest.last_smi_id', '=', 'last_smi.id');
                })
                ->select('last_smi.variant_id', 'last_smi.unit_price as last_cost');

            $lines = DB::table('orders_details as od')
                ->join('orders as o', 'o.id', '=', 'od.order_id')
                ->join('variants as v', 'v.id', '=', 'od.variant_id')
                ->join('flash_sale_products as fsp', function ($join) use ($fs) {
                    $join->on('fsp.product_id', '=', 'v.product_id')
                         ->where('fsp.flash_sale_id', '=', $fs->id);
                })
                ->leftJoinSub($costSub, 'costs', function ($join) {
                    $join->on('costs.variant_id', '=', 'v.id');
                })
                ->whereBetween('o.created_at', [$fs->start_time, $fs->end_time])
                ->where(function ($q) {
                    $q->where('o.payment_status', 'paid')
                      ->orWhere('o.status', 'completed');
                })
                // Chỉ tính các dòng có đơn giá đúng bằng giá flash của chiến dịch này
                ->whereColumn('od.price', '=', 'fsp.flash_price')
                ->select(
                    'o.id as order_id',
                    'v.product_id',
                    DB::raw('SUM(od.quantity) as qty'),
                    DB::raw('SUM(od.quantity * od.price) as line_rev'),
                    DB::raw('SUM(od.quantity * COALESCE(costs.last_cost, 0)) as line_cost')
                )
                ->groupBy('o.id', 'v.product_id')
                ->get();

            if ($lines->isNotEmpty()) {
                foreach ($lines as $ln) {
                    $sold += (int)($ln->qty ?? 0);
                    $profit = (float)$ln->line_rev - (float)$ln->line_cost;
                    $revenue += (int)round($profit);
                }
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
