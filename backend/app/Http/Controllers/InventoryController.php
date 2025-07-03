<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with([
            'product' => function ($q) {
                $q->select('id', 'name', 'sku', 'barcode');
            }
        ]);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('max_quantity')) {
            $query->where('quantity', '<=', $request->max_quantity);
        }

        $inventories = $query->get();

        return response()->json($inventories);
    }
}
