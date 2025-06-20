<?php

namespace App\Http\Controllers;

use App\Models\FavoriteProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteProductController extends Controller
{
    public function index()
    {
        $favorites = FavoriteProduct::with(['product' => function($query) {
            $query->with(['images' => function($query) {
                $query->select('id', 'image_path', 'is_main', 'product_id');
            }]);
        }])
            ->where('user_id', Auth::id())
            ->get();

        $favorites->transform(function ($favorite) {
            if ($favorite->product && $favorite->product->images) {
                $favorite->product->images->transform(function ($image) {
                    $image->image_path = url('storage/' . $image->image_path);
                    return $image;
                });
            }
            return $favorite;
        });

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

    public function count()
    {
        try {
            if (!Auth::check()) {
                return response()->json(['count' => 0]);
            }

            $user = Auth::user();
            $count = FavoriteProduct::where('user_id', $user->id)->count();
            
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get favorite count'], 500);
        }
    }
}