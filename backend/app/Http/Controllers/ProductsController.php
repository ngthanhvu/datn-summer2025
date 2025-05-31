<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Images;
use App\Models\Categories;
use App\Models\Brands;
use App\Models\Variants;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductsController extends Controller
{
    public function index()
    {
        $products = Products::with(['images' => function ($query) {
            $query->select('id', 'image_path', 'is_main', 'product_id');
        }, 'variants' => function ($query) {
            $query->select('id', 'color', 'size', 'price', 'quantity', 'sku', 'product_id');
        }])
            ->select(
                'id',
                'name',
                'description',
                'price',
                'original_price',
                'discount_price',
                'quantity',
                'slug',
                'categories_id',
                'brand_id',
                'is_active'
            )
            ->get();

        $products->transform(function ($product) {
            $product->images->transform(function ($image) {
                $image->image_path = url('storage/' . $image->image_path);
                return $image;
            });
            return $product;
        });

        return response()->json($products);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'original_price' => 'required|numeric',
                'discount_price' => 'required|numeric',
                'quantity' => 'required|integer',
                'is_active' => 'required|boolean',
            ]);

            $slug = Str::slug($request->name);

            $count = 1;
            $original_slug = $slug;
            while (Products::where('slug', $slug)->exists()) {
                $slug = $original_slug . '-' . $count++;
            }

            $product = Products::create([
                "name" => $request->name,
                "price" => $request->price,
                "description" => $request->description,
                "original_price" => $request->original_price,
                "discount_price" => $request->discount_price,
                "quantity" => $request->quantity,
                "slug" => $slug,
                "categories_id" => $request->categories_id,
                "brand_id" => $request->brand_id,
            ]);

            $mainImage = $request->file('is_main')->store('products', 'public');
            Images::create([
                'image_path' => $mainImage,
                'is_main' => true,
                'product_id' => $product->id,
            ]);

            if ($request->hasFile('image_path')) {
                $images = $request->file('image_path');
                foreach ($images as $image) {
                    $imagePath = $image->store('products', 'public');
                    Images::create([
                        'image_path' => $imagePath,
                        'is_main' => false,
                        'product_id' => $product->id,
                    ]);
                }
            }

            if ($request->has('variants')) {
                foreach ($request->variants as $variant) {
                    if (!empty($variant['price']) && (!empty($variant['color']) || !empty($variant['size']))) {
                        Variants::create([
                            'color' => $variant['color'],
                            'size' => $variant['size'],
                            'price' => $variant['price'],
                            'quantity' => $variant['quantity'] ?? 0,
                            'sku' => $variant['sku'] ?? '',
                            'product_id' => $product->id,
                        ]);
                    }
                }
            }

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Product creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Products $products)
    {
        //
    }

    public function destroy(Products $products)
    {
        //
    }
}
