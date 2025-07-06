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
            'images',
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
