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
        try {
            $cacheKey = 'flash_sales_index';
            $cacheTTL = 300;

            $flashSales = Cache::remember($cacheKey, $cacheTTL, function () {
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
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

        public function statistics($id = null)
    {
        try {
            $query = FlashSale::query();
            if ($id) $query->where('id', $id);
            $flashSales = $query->with('products.product')->get();

            $stats = [];
            foreach ($flashSales as $fs) {
                $sold = 0; 
                $totalRevenue = 0;
                $totalCost = 0;
                $totalShippingFee = 0;

                foreach ($fs->products as $product) {
                    $stockOutData = DB::table('stock_movements as sm')
                        ->join('stock_movement_items as smi', 'sm.id', '=', 'smi.stock_movement_id')
                        ->join('variants as v', 'smi.variant_id', '=', 'v.id')
                        ->where('v.product_id', $product->product_id)
                        ->where('sm.type', 'export')
                        ->where('sm.created_at', '>=', $fs->start_time)
                        ->where('sm.created_at', '<=', $fs->end_time)
                        ->where('sm.note', 'like', '%sale%')
                        ->select(
                            'smi.quantity',
                            'smi.unit_price'
                        )
                        ->get();

                    $soldQty = $stockOutData->sum('quantity');
                    $actualRevenue = $stockOutData->sum(function($item) {
                        return $item->quantity * $item->unit_price;
                    });
                    
                    $sold += $soldQty;
                    $totalRevenue += $actualRevenue;
                    
                    $importCost = 0;
                    $flashPrice = max(0, (float)($product->flash_price ?? 0));
                    
                    if ($product->product) {
                        $latestImport = DB::table('stock_movement_items as smi')
                            ->join('stock_movements as sm', 'sm.id', '=', 'smi.stock_movement_id')
                            ->join('variants as v', 'v.id', '=', 'smi.variant_id')
                            ->where('sm.type', 'import')
                            ->where('v.product_id', $product->product_id)
                            ->where('sm.created_at', '<=', $fs->end_time)
                            ->orderBy('sm.created_at', 'desc')
                            ->select('smi.unit_price')
                            ->first();
                        
                        if ($latestImport) {
                            $importCost = (float)$latestImport->unit_price;
                        } else {
                            $importCost = $flashPrice * 0.9;
                        }
                        
                        \Log::info("Flash Sale {$fs->name} - Product {$product->product_id}: Stock Out Sold = {$soldQty}, Stock Out Revenue = {$actualRevenue}, Import Cost = {$importCost}");
                    } else {
                        $importCost = $flashPrice * 0.9;
                    }
                    
                    $totalCost += $importCost * $soldQty;
                }

                $totalProfit = $totalRevenue - $totalCost - $totalShippingFee;

                $unitImportCost = $sold > 0 ? round($totalCost / $sold, 2) : 0;
                
                $stats[] = [
                    'id' => $fs->id,
                    'name' => $fs->name,
                    'sold_real' => $sold,
                    'revenue_real' => max(0, round($totalRevenue, 2)),
                    'cost_real' => $unitImportCost,
                    'shipping_fee' => round($totalShippingFee, 2),
                    'profit_real' => round($totalProfit, 2),
                ];
            }
            return response()->json($id ? ($stats[0] ?? []) : $stats);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }   
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['active' => 'required|boolean']);
        $fs = FlashSale::findOrFail($id);
        $fs->active = $request->active;
        $fs->save();
        Cache::forget('flash_sales_index');
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
            Cache::forget('flash_sales_index');
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
            Cache::forget('flash_sales_index');
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
            $flashSale->products()->forceDelete();
            $flashSale->forceDelete();
            DB::commit();
            Cache::forget('flash_sales_index');
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        try {
            $cacheKey = "flash_sale_show_{$id}";
            $cacheTTL = 300;

            $flashSale = Cache::remember($cacheKey, $cacheTTL, function () use ($id) {
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
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function processRepeat()
    {
        try {
            DB::beginTransaction();
            
            $now = now();
            $processedCount = 0;
            $autoIncreaseCount = 0;
            
            $expiredFlashSales = FlashSale::where('repeat', true)
                ->where('active', true)
                ->where('end_time', '<=', $now)
                ->with('products')
                ->get();

            foreach ($expiredFlashSales as $flashSale) {
                $duration = $flashSale->start_time->diffInMinutes($flashSale->end_time);
                $newStartTime = $now;
                $newEndTime = $now->copy()->addMinutes($duration);
                
                $newFlashSale = FlashSale::create([
                    'name' => $flashSale->name . ' (Lặp lại - ' . $now->format('d/m/Y H:i') . ')',
                    'start_time' => $newStartTime,
                    'end_time' => $newEndTime,
                    'repeat' => $flashSale->repeat,
                    'repeat_minutes' => $flashSale->repeat_minutes,
                    'auto_increase' => $flashSale->auto_increase,
                    'active' => true,
                ]);
                
                foreach ($flashSale->products as $product) {
                    FlashSaleProduct::create([
                        'flash_sale_id' => $newFlashSale->id,
                        'product_id' => $product->product_id,
                        'flash_price' => $product->flash_price,
                        'quantity' => $product->quantity,
                        'sold' => 0,
                    ]);
                }
                
                $flashSale->update(['active' => false]);
                $processedCount++;
            }
            
            $activeFlashSales = FlashSale::where('auto_increase', true)
                ->where('active', true)
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->with('products.product')
                ->get();

            foreach ($activeFlashSales as $flashSale) {
                $elapsedMinutes = $flashSale->start_time->diffInMinutes($now);
                
                foreach ($flashSale->products as $product) {
                    $originalPrice = $product->product->price ?? 0;
                    $currentFlashPrice = $product->flash_price;
                    
                    $increaseIntervals = floor($elapsedMinutes / 30);
                    $increasePercentage = min($increaseIntervals * 5, 50);
                    
                    $newFlashPrice = $originalPrice * (1 - ($increasePercentage / 100));
                    
                    $newFlashPrice = max($newFlashPrice, $originalPrice * 0.5);
                    
                    if (abs($newFlashPrice - $currentFlashPrice) > 0.01) {
                        $product->update(['flash_price' => round($newFlashPrice, 2)]);
                        $autoIncreaseCount++;
                    }
                }
            }
            
            DB::commit();
            Cache::forget('flash_sales_index');
            
            return response()->json([
                'success' => true, 
                'message' => "Xử lý thành công! Đã tạo {$processedCount} Flash Sale mới và cập nhật {$autoIncreaseCount} giá sản phẩm.",
                'processed_count' => $processedCount,
                'auto_increase_count' => $autoIncreaseCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getActiveFlashSales()
    {
        $now = now();
        
        $activeFlashSales = FlashSale::where('active', true)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->with([
                'products.product.mainImage',
                'products.product.categories',
                'products.product.brand',
                'products.product.variants',
            ])
            ->get();

        foreach ($activeFlashSales as $fs) {
            foreach ($fs->products as $p) {
                if ($p->product) {
                    $p->product->flash_sale_quantity = $p->quantity;
                    $p->product->flash_sale_sold = $p->sold;
                    $p->product->flash_price = $p->flash_price;
                }
            }
        }

        return response()->json($activeFlashSales);
    }
}
