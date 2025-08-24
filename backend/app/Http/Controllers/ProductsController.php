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
use Illuminate\Support\Facades\Cache;

class ProductsController extends Controller
{
    public function bestsellers(Request $request)
    {
        $limit = (int) $request->input('limit', 10);
        $limit = max(1, min($limit, 50));

        $cacheKey = 'products_bestsellers_' . $limit;
        $cacheTTL = 3600; // giây

        $products = Cache::tags(['products'])->remember($cacheKey, $cacheTTL, function () use ($limit) {
            $items = Products::with([
                'images' => function ($query) {
                    $query->select('id', 'image_path', 'is_main', 'product_id');
                },
                'variants' => function ($query) {
                    $query->select('id', 'color', 'size', 'price', 'sku', 'product_id');
                }
            ])
                ->select(
                    'id',
                    'name',
                    'description',
                    'price',
                    'discount_price',
                    'slug',
                    'categories_id',
                    'brand_id',
                    'is_active',
                    'sold_count'
                )
                ->where('is_active', true)
                ->orderByDesc('sold_count')
                ->limit($limit)
                ->get();

            $items->transform(function ($product) {
                if ($product->images) {
                    $product->images->transform(function ($image) {
                        $image->image_path = url('storage/' . $image->image_path);
                        return $image;
                    });
                }
                return $product;
            });

            return $items;
        });

        return response()->json($products);
    }
    public function index(Request $request)
    {
        $cacheKey = 'products_index_' . md5(json_encode($request->query()));
        $cacheTTL = 3600; // thời gian cache (giây)

        $products = Cache::tags(['products'])->remember($cacheKey, $cacheTTL, function () use ($request) {
            $query = Products::with([
                'images' => function ($query) {
                    $query->select('id', 'image_path', 'is_main', 'product_id');
                },
                'variants' => function ($query) {
                    $query->select('id', 'color', 'size', 'price', 'sku', 'product_id');
                }
            ])->select(
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

            if ($request->has('category_id') && !empty($request->category_id)) {
                $categoryIds = is_array($request->category_id) ? array_filter($request->category_id) : [$request->category_id];
                $query->whereIn('categories_id', $categoryIds);
            } else if ($request->has('category') && !empty($request->category)) {
                $categories = is_array($request->category) ? array_filter($request->category) : [$request->category];
                $categoryIds = [];
                foreach ($categories as $cat) {
                    if (is_numeric($cat)) {
                        $categoryIds[] = $cat;
                    } else {
                        $catModel = \App\Models\Categories::where('slug', $cat)->first();
                        if ($catModel) $categoryIds[] = $catModel->id;
                    }
                }
                if (!empty($categoryIds)) $query->whereIn('categories_id', $categoryIds);
            }

            if ($request->has('brand_id') && !empty($request->brand_id)) {
                $brandIds = is_array($request->brand_id) ? array_filter($request->brand_id) : [$request->brand_id];
                $query->whereIn('brand_id', $brandIds);
            } else if ($request->has('brand') && !empty($request->brand)) {
                $brands = is_array($request->brand) ? array_filter($request->brand) : [$request->brand];
                $brandIds = [];
                foreach ($brands as $b) {
                    if (is_numeric($b)) {
                        $brandIds[] = $b;
                    } else {
                        $brandModel = \App\Models\Brands::where('slug', $b)->first();
                        if ($brandModel) $brandIds[] = $brandModel->id;
                    }
                }
                if (!empty($brandIds)) $query->whereIn('brand_id', $brandIds);
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
            } else {
                $query->orderBy('id', 'desc');
            }

            $perPage = (int) $request->input('per_page', 8);
            $perPage = max(1, min($perPage, 100));

            $products = $query->paginate($perPage, ['*'], 'page')
                ->appends($request->query());

            $products->getCollection()->transform(function ($product) {
                $product->images->transform(function ($image) {
                    $image->image_path = url('storage/' . $image->image_path);
                    return $image;
                });
                return $product;
            });

            return $products;
        });

        return response()->json($products);
    }

    public function getProductById($id)
    {
        $cacheKey = "product_detail_{$id}";
        $cacheTTL = 3600; // giây

        $product = Cache::tags(['products'])->remember($cacheKey, $cacheTTL, function () use ($id) {
            $product = Products::with([
                'images' => function ($query) {
                    $query->select('id', 'image_path', 'is_main', 'product_id');
                },
                'variants' => function ($query) {
                    $query->select('id', 'color', 'size', 'price', 'sku', 'product_id');
                }
            ])
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

            if ($product && $product->images) {
                $product->images->transform(function ($image) {
                    $image->image_path = url('storage/' . $image->image_path);
                    return $image;
                });
            }

            return $product;
        });

        return response()->json($product);
    }

    public function getProductBySlug($slug)
    {
        $cacheKey = "product_detail_slug_{$slug}";
        $cacheTTL = 3600;

        try {
            $product = Cache::tags(['products'])->remember($cacheKey, $cacheTTL, function () use ($slug) {
                $product = Products::with([
                    'images' => function ($query) {
                        $query->select('id', 'image_path', 'is_main', 'product_id', 'variant_id');
                    },
                    'variants' => function ($query) {
                        $query->select('id', 'color', 'size', 'price', 'sku', 'product_id');
                    },
                    'variants.images',
                    'categories',
                    'brand'
                ])
                    ->where('slug', $slug)
                    ->firstOrFail();

                $product->images->transform(function ($image) {
                    $image->image_path = url('storage/' . $image->image_path);
                    return $image;
                });

                if ($product->variants) {
                    foreach ($product->variants as $variant) {
                        if ($variant->images) {
                            foreach ($variant->images as $img) {
                                $img->image_path = url('storage/' . $img->image_path);
                            }
                        }
                    }
                }

                return $product;
            });

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
                        $firstVariant = null;

                        foreach ($variant['sizes'] as $sizeObj) {
                            $createdVariant = Variants::create([
                                'color' => $variant['color'],
                                'size' => $sizeObj['size'],
                                'price' => $sizeObj['price'],
                                'sku' => $sizeObj['sku'] ?? '',
                                'product_id' => $product->id,
                            ]);

                            if ($firstVariant === null) {
                                $firstVariant = $createdVariant;
                            }
                        }

                        $variantImages = $request->file("variants.$variantIndex.images", []);
                        if ($variantImages && $firstVariant) {
                            foreach ($variantImages as $variantImage) {
                                $imagePath = $variantImage->store('products', 'public');
                                Images::create([
                                    'image_path' => $imagePath,
                                    'is_main' => false,
                                    'product_id' => $product->id,
                                    'variant_id' => $firstVariant->id,
                                ]);
                            }
                        }
                    }
                }
            }

            Cache::tags(['products'])->flush();
            Cache::tags(['filters'])->flush();

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
            \Log::info('🔥 Update product request', [
                'id' => $id,
                'request_data' => $request->all(),
                'files' => $request->allFiles()
            ]);

            $product = Products::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'discount_price' => 'required|numeric',
                'is_active' => 'required|in:0,1',
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
                "is_active" => $request->is_active,
            ]);

            if ($request->hasFile('is_main')) {
                $oldMainImage = Images::where('product_id', $product->id)->where('is_main', true)->first();
                if ($oldMainImage) {
                    try {
                        Storage::disk('public')->delete($oldMainImage->image_path);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to delete old main image', ['path' => $oldMainImage->image_path, 'error' => $e->getMessage()]);
                    }
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
                $oldImages = Images::where('product_id', $product->id)
                    ->where('is_main', false)
                    ->whereNull('variant_id')
                    ->get();

                foreach ($oldImages as $oldImage) {
                    try {
                        Storage::disk('public')->delete($oldImage->image_path);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to delete old additional image', ['path' => $oldImage->image_path, 'error' => $e->getMessage()]);
                    }
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

            if ($request->has('variants') && is_array($request->input('variants'))) {
                \Log::info('🔥 Processing variants', ['variants' => $request->input('variants')]);

                $oldVariants = Variants::where('product_id', $product->id)->get();
                foreach ($oldVariants as $oldVariant) {
                    $variantImages = Images::where('variant_id', $oldVariant->id)->get();
                    foreach ($variantImages as $variantImage) {
                        try {
                            Storage::disk('public')->delete($variantImage->image_path);
                        } catch (\Exception $e) {
                            \Log::warning('Failed to delete old variant image', ['path' => $variantImage->image_path, 'error' => $e->getMessage()]);
                        }
                        $variantImage->delete();
                    }
                }
                Variants::where('product_id', $product->id)->delete();

                foreach ($request->input('variants', []) as $variantIndex => $variant) {
                    \Log::info('🔥 Processing variant', ['variant' => $variant]);

                    if (!isset($variant['color']) || !isset($variant['sizes']) || !is_array($variant['sizes'])) {
                        \Log::warning('🔥 Invalid variant structure', ['variant' => $variant]);
                        continue;
                    }

                    if (!empty($variant['color']) && !empty($variant['sizes']) && count($variant['sizes']) > 0) {
                        $firstVariant = null;

                        foreach ($variant['sizes'] as $sizeIndex => $sizeObj) {
                            \Log::info('🔥 Creating variant size', ['sizeObj' => $sizeObj]);

                            if (!isset($sizeObj['size']) || !isset($sizeObj['price'])) {
                                \Log::warning('🔥 Invalid size object', ['sizeObj' => $sizeObj]);
                                continue;
                            }

                            $createdVariant = Variants::create([
                                'color' => $variant['color'],
                                'size' => $sizeObj['size'],
                                'price' => $sizeObj['price'],
                                'sku' => $sizeObj['sku'] ?? '',
                                'product_id' => $product->id,
                            ]);

                            if ($firstVariant === null) {
                                $firstVariant = $createdVariant;
                            }
                        }

                        if ($request->hasFile("variants.$variantIndex.images")) {
                            $variantImages = $request->file("variants.$variantIndex.images");
                            \Log::info('🔥 Processing variant images', ['variantImages' => $variantImages]);

                            if (is_array($variantImages) && $firstVariant) {
                                foreach ($variantImages as $variantImage) {
                                    $imagePath = $variantImage->store('products', 'public');
                                    Images::create([
                                        'image_path' => $imagePath,
                                        'is_main' => false,
                                        'product_id' => $product->id,
                                        'variant_id' => $firstVariant->id,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            \Log::info('🔥 Product updated successfully', ['product_id' => $product->id]);

            Cache::tags(['products'])->flush();
            Cache::tags(['filters'])->flush();

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product->fresh(),
            ], 200);
        } catch (\Exception $e) {
            \Log::error('🔥 Product update failed', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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

            Cache::tags(['products'])->flush();
            Cache::tags(['filters'])->flush();

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

            Cache::tags(['products'])->flush();
            Cache::tags(['filters'])->flush();

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

        $cacheKey = "search_" . md5($q);
        $cacheTTL = 3600;

        $products = Cache::tags(['products'])->remember($cacheKey, $cacheTTL, function () use ($q) {
            return Products::with(['images' => function ($query) {
                $query->select('id', 'image_path', 'is_main', 'product_id');
            }])
                ->select('id', 'name', 'price', 'discount_price', 'slug', 'categories_id')
                ->where('name', 'like', "%{$q}%")
                ->get()
                ->transform(function ($product) {
                    $product->images->transform(function ($image) {
                        $image->image_path = url('storage/' . $image->image_path);
                        return $image;
                    });
                    return $product;
                });
        });

        return response()->json($products);
    }


    public function getFilterOptions()
    {
        try {
            $cacheKey = 'filter_options';
            $cacheTTL = 3600; // 1 tiếng

            $data = Cache::tags(['filters', 'products'])->remember($cacheKey, $cacheTTL, function () {
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

                return [
                    'colors' => $colors,
                    'sizes' => $sizes,
                    'categories' => $categories,
                    'brands' => $brands
                ];
            });

            return response()->json($data);
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

    public function recommend(Request $request)
    {
        $gender = $request->get('gender');
        $dateOfBirth = $request->get('dateOfBirth');
        $address = $request->get('address');

        $user = auth('api')->user();
        if (!$gender && $user) $gender = $user->gender;
        if (!$dateOfBirth && $user) $dateOfBirth = $user->dateOfBirth;
        if (!$address && $user) $address = $user->address;

        $query = \App\Models\Products::query();
        $keyword = null;
        if ($gender) {
            $keyword = $gender === 'male' ? 'nam' : ($gender === 'female' ? 'nữ' : null);
        }
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('categories', function ($catQ) use ($keyword) {
                    $catQ->where('name', 'like', '%' . $keyword . '%');
                })
                    ->orWhere('name', 'like', '%' . $keyword . '%');
            });
        }
        // if ($dateOfBirth) {
        //     $age = \Carbon\Carbon::parse($dateOfBirth)->age;
        //     if (\Schema::hasColumn('products', 'min_age') && \Schema::hasColumn('products', 'max_age')) {
        //         $query->where('min_age', '<=', $age)->where('max_age', '>=', $age);
        //     }
        // }
        // if ($address) {
        //     if (\Schema::hasColumn('products', 'city')) {
        //         $query->where('city', 'like', '%' . $address . '%');
        //     }
        // }
        $products = $query->with(['images', 'variants'])->orderByDesc('created_at')->limit(10)->get();
        return response()->json($products);
    }
}
