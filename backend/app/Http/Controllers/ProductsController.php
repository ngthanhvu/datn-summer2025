<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Images;
use App\Models\Categories;
use App\Models\Brands;
use App\Models\Variants;
use App\Models\ProductImages;
use App\Models\ProductVariants;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $query = Products::with(['images' => function ($query) {
            $query->select('id', 'image_path', 'is_main', 'product_id');
        }, 'variants' => function ($query) {
            $query->select('id', 'color', 'size', 'price', 'sku', 'product_id');
        }])
            ->select(
                'id',
                'name',
                'description',
                'price',
                'discount_price',
                'slug',
                'categories_id',
                'brand_id',
                'is_active'
            );
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('category') && !empty($request->category)) {
            $categories = is_array($request->category) ? array_filter($request->category) : [$request->category];
            if (!empty($categories)) {
                $query->whereIn('categories_id', $categories);
            }
        }

        if ($request->has('brand') && !empty($request->brand)) {
            $brands = is_array($request->brand) ? array_filter($request->brand) : [$request->brand];
            if (!empty($brands)) {
                $query->whereIn('brand_id', $brands);
            }
        }

        if ($request->has('color') && !empty($request->color)) {
            $colors = is_array($request->color) ? array_filter($request->color) : [$request->color];
            if (!empty($colors)) {
                $query->whereHas('variants', function ($q) use ($colors) {
                    $q->whereIn('color', $colors);
                });
            }
        }

        if ($request->has('size') && !empty($request->size)) {
            $sizes = is_array($request->size) ? array_filter($request->size) : [$request->size];
            if (!empty($sizes)) {
                $query->whereHas('variants', function ($q) use ($sizes) {
                    $q->whereIn('size', $sizes);
                });
            }
        }

        if ($request->has('sort_by')) {
            $sortField = $request->sort_by;
            $sortDirection = $request->has('sort_direction') ? $request->sort_direction : 'asc';
            $query->orderBy($sortField, $sortDirection);
        }

        $products = $query->get();

        $products->transform(function ($product) {
            $product->images->transform(function ($image) {
                $image->image_path = url('storage/' . $image->image_path);
                return $image;
            });
            return $product;
        });

        return response()->json($products);
    }

    public function getProductById($id)
    {
        $product = Products::with(['images' => function ($query) {
            $query->select('id', 'image_path', 'is_main', 'product_id');
        }, 'variants' => function ($query) {
            $query->select('id', 'color', 'size', 'price', 'sku', 'product_id');
        }])
            ->select(
                'id',
                'name',
                'description',
                'price',
                'discount_price',
                'slug',
                'categories_id',
                'brand_id',
                'is_active'
            )
            ->findOrFail($id);

        return response()->json($product);
    }

    public function getProductBySlug($slug)
    {
        try {
            $product = Products::with(['images' => function ($query) {
                $query->select('id', 'image_path', 'is_main', 'product_id', 'variant_id');
            }, 'variants' => function ($query) {
                $query->select('id', 'color', 'size', 'price', 'sku', 'product_id');
            }, 'variants.images', 'categories', 'brand'])
                ->where('slug', $slug)
                ->firstOrFail();

            $product->images->transform(function ($image) {
                $image->image_path = url('storage/' . $image->image_path);
                return $image;
            });

            // Đảm bảo images của từng variant cũng có url đầy đủ
            if ($product->variants) {
                foreach ($product->variants as $variant) {
                    if ($variant->images) {
                        foreach ($variant->images as $img) {
                            $img->image_path = url('storage/' . $img->image_path);
                        }
                    }
                }
            }

            return response()->json($product);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Product not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'discount_price' => 'required|numeric',
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
                "discount_price" => $request->discount_price,
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
                foreach ($request->input('variants', []) as $variantIndex => $variant) {
                    if (!empty($variant['color']) && !empty($variant['sizes']) && is_array($variant['sizes']) && count($variant['sizes']) > 0) {
                        $sizeObj = $variant['sizes'][0];
                        $createdVariant = Variants::create([
                            'color' => $variant['color'],
                            'size' => $sizeObj['size'],
                            'price' => $sizeObj['price'],
                            'sku' => $sizeObj['sku'] ?? '',
                            'product_id' => $product->id,
                        ]);
                        // Lưu ảnh phụ cho variant (mỗi màu 1 ảnh)
                        $variantImages = $request->file("variants.$variantIndex.images", []);
                        if ($variantImages) {
                            foreach ($variantImages as $variantImage) {
                                $imagePath = $variantImage->store('products', 'public');
                                Images::create([
                                    'image_path' => $imagePath,
                                    'is_main' => false,
                                    'product_id' => $product->id,
                                    'variant_id' => $createdVariant->id,
                                ]);
                            }
                        }
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

    public function update(Request $request, $id)
    {
        try {
            $product = Products::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'discount_price' => 'required|numeric',
                'is_active' => 'required|boolean',
            ]);

            $slug = Str::slug($request->name);
            $count = 1;
            $original_slug = $slug;
            while (Products::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $original_slug . '-' . $count++;
            }

            $product->update([
                "name" => $request->name,
                "price" => $request->price,
                "description" => $request->description,
                "discount_price" => $request->discount_price,
                "slug" => $slug,
                "categories_id" => $request->categories_id,
                "brand_id" => $request->brand_id,
            ]);

            if ($request->hasFile('is_main')) {
                $oldMainImage = Images::where('product_id', $product->id)->where('is_main', true)->first();
                if ($oldMainImage) {
                    Storage::disk('public')->delete($oldMainImage->image_path);
                    $oldMainImage->delete();
                }

                $mainImage = $request->file('is_main')->store('products', 'public');
                Images::create([
                    'image_path' => $mainImage,
                    'is_main' => true,
                    'product_id' => $product->id,
                ]);
            }

            if ($request->hasFile('image_path')) {
                $oldImages = Images::where('product_id', $product->id)->where('is_main', false)->get();
                foreach ($oldImages as $oldImage) {
                    Storage::disk('public')->delete($oldImage->image_path);
                    $oldImage->delete();
                }

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
                Variants::where('product_id', $product->id)->delete();

                foreach ($request->input('variants', []) as $variantIndex => $variant) {
                    if (!empty($variant['color']) && !empty($variant['sizes']) && is_array($variant['sizes']) && count($variant['sizes']) > 0) {
                        $sizeObj = $variant['sizes'][0];
                        $createdVariant = Variants::create([
                            'color' => $variant['color'],
                            'size' => $sizeObj['size'],
                            'price' => $sizeObj['price'],
                            'sku' => $sizeObj['sku'] ?? '',
                            'product_id' => $product->id,
                        ]);
                        // Lưu ảnh phụ cho variant (mỗi màu 1 ảnh)
                        $variantImages = $request->file("variants.$variantIndex.images", []);
                        if ($variantImages) {
                            foreach ($variantImages as $variantImage) {
                                $imagePath = $variantImage->store('products', 'public');
                                Images::create([
                                    'image_path' => $imagePath,
                                    'is_main' => false,
                                    'product_id' => $product->id,
                                    'variant_id' => $createdVariant->id,
                                ]);
                            }
                        }
                    }
                }
            }

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product->fresh(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Product update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $products = Products::findOrFail($id);
            $products->delete();
            return response()->json([
                'message' => 'Product deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Product deletion failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function bulkDestroy(Request $request)
    {
        \Log::info('🔥 bulkDestroy hit', ['ids' => $request->input('ids')]);
        try {
            $ids = $request->input('ids', []);
            if (empty($ids) || !is_array($ids)) {
                return response()->json(['message' => 'No product ids provided'], 400);
            }
            Products::whereIn('id', $ids)->delete();
            return response()->json(['message' => 'Products deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Bulk delete failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $q = $request->query('q');

        if (!$q) {
            return response()->json([], 200);
        }

        $products = Products::with(['images' => function ($query) {
            $query->select('id', 'image_path', 'is_main', 'product_id');
        }])
            ->select('id', 'name', 'price', 'discount_price', 'slug', 'categories_id')
            ->where('name', 'like', "%{$q}%")
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

    public function getFilterOptions()
    {
        try {
            $colors = Variants::select('color')
                ->join('products', 'variants.product_id', '=', 'products.id')
                ->where('products.is_active', true)
                ->whereNotNull('color')
                ->where('color', '!=', '')
                ->distinct()
                ->pluck('color')
                ->toArray();

            $sizes = Variants::select('size')
                ->join('products', 'variants.product_id', '=', 'products.id')
                ->where('products.is_active', true)
                ->whereNotNull('size')
                ->where('size', '!=', '')
                ->distinct()
                ->pluck('size')
                ->toArray();

            $categories = Categories::select('id', 'name')
                ->where('is_active', true)
                ->get()
                ->toArray();

            $brands = Brands::select('id', 'name')
                ->where('is_active', true)
                ->get()
                ->toArray();

            return response()->json([
                'colors' => $colors,
                'sizes' => $sizes,
                'categories' => $categories,
                'brands' => $brands
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get filter options',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function favorite($id)
    {
        return response()->json([
            'message' => 'Product favorite successfully',
        ], 200);
    }
}
