<?php

namespace App\Http\Controllers;

use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FlashSaleController extends Controller
{
    public function index()
    {
        $flashSales = FlashSale::with([
            'products.product.mainImage',
            'products.product.categories',
            'products.product.brand',
            'products.product.variants',
        ])->get();
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
            ]);
            $flashSale = FlashSale::create($data);
            foreach ($data['products'] as $prod) {
                FlashSaleProduct::create([
                    'flash_sale_id' => $flashSale->id,
                    'product_id' => $prod['product_id'],
                    'flash_price' => $prod['flash_price'],
                    'quantity' => $prod['quantity'],
                ]);
            }
            DB::commit();
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
            ]);
            // Xóa sản phẩm cũ
            $flashSale->products()->delete();
            // Tạo lại sản phẩm mới
            $flashSale->update($data);
            foreach ($data['products'] as $prod) {
                FlashSaleProduct::create([
                    'flash_sale_id' => $flashSale->id,
                    'product_id' => $prod['product_id'],
                    'flash_price' => $prod['flash_price'],
                    'quantity' => $prod['quantity'],
                ]);
            }
            DB::commit();
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
            // Bỏ cộng lại tồn kho khi xóa
            $flashSale->products()->delete();
            $flashSale->delete();
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        $flashSale = FlashSale::with([
            'products.product.mainImage',
            'products.product.categories',
            'products.product.brand',
            'products.product.variants',
        ])->findOrFail($id);
        return response()->json($flashSale);
    }
} 