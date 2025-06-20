<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with([
            'variant' => function ($q) {
                $q->select('id', 'color', 'size', 'price', 'sku', 'product_id')
                    ->with([
                        'product:id,name'
                    ]);
            }
        ]);

        if ($request->has('product_id')) {
            $query->whereHas('variant', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        if ($request->has('max_quantity')) {
            $query->where('quantity', '<=', $request->max_quantity);
        }

        $inventories = $query->get();

        return response()->json($inventories);
    }

    public function updateStock(Request $request)
    {
        try {
            $validated = $request->validate([
                'variant_id' => 'required|exists:variants,id',
                'type' => 'required|in:import,export,adjustment',
                'quantity' => 'required|integer|min:1',
                'note' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $inventory = Inventory::where('variant_id', $validated['variant_id'])->first();

            if (!$inventory) {
                $inventory = new Inventory();
                $inventory->variant_id = $validated['variant_id'];
                $inventory->quantity = 0;
            }

            $originalQty = $inventory->quantity;

            if ($validated['type'] === 'import') {
                $inventory->quantity += $validated['quantity'];
            } elseif ($validated['type'] === 'export') {
                if ($inventory->quantity < $validated['quantity']) {
                    throw new \Exception('Không đủ hàng tồn kho');
                }
                $inventory->quantity -= $validated['quantity'];
            } elseif ($validated['type'] === 'adjustment') {
                $inventory->quantity = $validated['quantity'];
            }

            $inventory->save();

            InventoryMovement::create([
                'variant_id' => $validated['variant_id'],
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'note' => $validated['note'],
                'user_id' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Cập nhật tồn kho thành công',
                'previous_quantity' => $originalQty,
                'current_quantity' => $inventory->quantity,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getMovements(Request $request)
    {
        $query = InventoryMovement::with([
            'variant' => function ($q) {
                $q->select('id', 'color', 'size', 'sku', 'product_id')
                    ->with([
                        'product:id,name'
                    ]);
            },
            'user:id,username'
        ]);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('limit')) {
            $query->limit($request->limit);
        }

        $movements = $query->orderBy('created_at', 'desc')->get();

        return response()->json($movements);
    }

    // API: /api/inventory/movement/{id}/pdf
    public function exportMovementPdf($id)
    {
        $movement = \App\Models\InventoryMovement::with([
            'variant.product',
            'user'
        ])->findOrFail($id);

        $data = [
            'movement' => $movement
        ];
        $pdf = Pdf::loadView('pdf.movement-invoice', $data);
        $filename = 'phieu-' . $movement->type . '-' . $movement->id . '.pdf';
        return $pdf->download($filename);
    }
}
