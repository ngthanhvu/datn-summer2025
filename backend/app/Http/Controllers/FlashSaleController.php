<?php

namespace App\Http\Controllers;

use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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
            ]);
            $flashSale = FlashSale::create($data);
            foreach ($data['products'] as $prod) {
                FlashSaleProduct::create([
                    'flash_sale_id' => $flashSale->id,
                    'product_id' => $prod['product_id'],
                    'flash_price' => $prod['flash_price'],
                    'quantity' => $prod['quantity'],
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
            $flashSale->products()->delete();
            $flashSale->update($data);
            foreach ($data['products'] as $prod) {
                FlashSaleProduct::create([
                    'flash_sale_id' => $flashSale->id,
                    'product_id' => $prod['product_id'],
                    'flash_price' => $prod['flash_price'],
                    'quantity' => $prod['quantity'],
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
            $flashSale = FlashSale::findOrFail($id);
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
