<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockMovementController extends Controller
{
    public function index()
    {
        $movements = StockMovement::with([
            'user:id,username,email',
            'items' => function ($q) {
                $q->with([
                    'variant:id,sku,product_id',
                    'variant.product:id,name'
                ]);
            }
        ])->orderBy('created_at', 'desc')->get();
        return response()->json($movements);
    }

    public function show($id)
    {
        $movement = StockMovement::with([
            'user:id,username,email',
            'items' => function ($q) {
                $q->with([
                    'variant:id,sku,product_id',
                    'variant.product:id,name'
                ]);
            }
        ])->findOrFail($id);
        return response()->json($movement);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:import,export',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|integer|min:0',
        ]);
        DB::beginTransaction();
        try {
            $user_id = Auth::id() ?? $request->input('created_by') ?? 1;
            $movement = StockMovement::create([
                'type' => $request->type,
                'user_id' => $user_id,
                'note' => $request->note,
            ]);
            foreach ($request->items as $item) {
                StockMovementItem::create([
                    'stock_movement_id' => $movement->id,
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'] ?? 0,
                ]);
                $inventory = Inventory::firstOrCreate(
                    ['variant_id' => $item['variant_id']],
                    ['quantity' => 0]
                );
                if ($request->type === 'import') {
                    $inventory->quantity += $item['quantity'];
                } else if ($request->type === 'export') {
                    if ($inventory->quantity < $item['quantity']) {
                        throw new \Exception('Không đủ tồn kho cho biến thể ID: ' . $item['variant_id']);
                    }
                    $inventory->quantity -= $item['quantity'];
                }
                $inventory->save();
            }
            DB::commit();
            return response()->json($movement, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $movement = StockMovement::with('items')->findOrFail($id);
            foreach ($movement->items as $item) {
                $inventory = Inventory::where('variant_id', $item->variant_id)->first();
                if ($inventory) {
                    if ($movement->type === 'import') {
                        $inventory->quantity -= $item->quantity;
                    } else if ($movement->type === 'export') {
                        $inventory->quantity += $item->quantity;
                    }
                    $inventory->save();
                }
            }
            $movement->items()->delete();
            $movement->delete();
            DB::commit();
            return response()->json(['message' => 'Xóa phiếu nhập/xuất thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
