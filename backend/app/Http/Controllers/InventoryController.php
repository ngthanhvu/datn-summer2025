<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Models\Variants;

class InventoryController extends Controller
{
    // public function index(Request $request)
    // {
    //     $query = Inventory::with([
    //         'variant' => function ($q) {
    //             $q->select('id', 'color', 'size', 'price', 'sku', 'product_id');
    //             $q->with(['product:id,name']);
    //         }
    //     ]);

    //     if ($request->has('product_id')) {
    //         $variantIds = Variants::where('product_id', $request->product_id)->pluck('id');
    //         $query->whereIn('variant_id', $variantIds);
    //     }

    //     if ($request->has('variant_id')) {
    //         $query->where('variant_id', $request->variant_id);
    //     }

    //     if ($request->has('variant_ids')) {
    //         $query->whereIn('variant_id', $request->variant_ids);
    //     }

    //     if ($request->has('max_quantity')) {
    //         $query->where('quantity', '<=', $request->max_quantity);
    //     }

    //     $inventories = $query->get();

    //     return response()->json($inventories);
    // }

    public function index(Request $request)
    {
        $query = Inventory::with([
            'variant' => function ($q) {
                $q->select('id', 'color', 'size', 'price', 'sku', 'product_id');
                $q->with(['product:id,name']);
            }
        ]);

        if ($request->has('product_id')) {
            $variantIds = Variants::where('product_id', $request->product_id)->pluck('id');
            $query->whereIn('variant_id', $variantIds);
        }

        if ($request->has('variant_id')) {
            $query->where('variant_id', $request->variant_id);
        }

        if ($request->has('variant_ids')) {
            $query->whereIn('variant_id', $request->variant_ids);
        }

        if ($request->has('max_quantity')) {
            $query->where('quantity', '<=', $request->max_quantity);
        }

        $inventories = $query->get();

        $variantIdsInInventory = $inventories->pluck('variant_id')->toArray();

        $variantsWithoutInventory = Variants::with('product:id,name')
            ->whereNotIn('id', $variantIdsInInventory)
            ->when($request->has('product_id'), function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            })
            ->get();

        $fakeInventories = $variantsWithoutInventory->map(function ($variant) {
            $inventory = new Inventory();
            $inventory->id = null;
            $inventory->variant_id = $variant->id;
            $inventory->quantity = 0;
            $inventory->setRelation('variant', $variant);
            return $inventory;
        });

        $allInventories = $inventories->merge($fakeInventories);

        return response()->json($allInventories);
    }
}
