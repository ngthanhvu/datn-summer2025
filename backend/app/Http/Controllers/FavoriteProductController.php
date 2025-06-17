<?php

namespace App\Http\Controllers;

use App\Models\FavoriteProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteProductController extends Controller
{
    public function index()
    {
        $favorites = FavoriteProduct::with('product')
            ->where('user_id', Auth::id())
            ->get();

        return response()->json($favorites);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_slug' => 'required|exists:products,slug',
        ]);

        $favorite = FavoriteProduct::firstOrCreate([
            'user_id' => Auth::id(),
            'product_slug' => $request->product_slug,
        ]);

        return response()->json([
            'message' => 'Đã thêm vào danh sách yêu thích.',
            'data' => $favorite,
        ]);
    }

    public function destroy($product_slug)
    {
        FavoriteProduct::where('user_id', Auth::id())
            ->where('product_slug', $product_slug)
            ->delete();

        return response()->json([
            'message' => 'Đã xoá khỏi danh sách yêu thích.',
        ]);
    }
    public function check($slug)
    {
        $exists = FavoriteProduct::where('user_id', Auth::id())
            ->where('product_slug', $slug)
            ->exists();

        return response()->json([
            'is_favorite' => $exists,
        ]);
    }
}