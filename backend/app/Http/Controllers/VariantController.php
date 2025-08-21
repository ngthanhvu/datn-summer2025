<?php

namespace App\Http\Controllers;

use App\Models\Variants;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    public function index()
    {
        $variants = Variants::with([
            'product:id,name',
            'images:id,image_path,is_main,product_id,variant_id',
            'inventory:id,variant_id,quantity',
        ])->get();

        $variants = $variants->map(function ($variant) {
            if ($variant->images) {
                $variant->images = $variant->images->map(function ($image) {
                    $image->image_path = url('storage/' . $image->image_path);
                    return $image;
                });
            }
            return $variant;
        });

        return response()->json($variants);
    }
}
