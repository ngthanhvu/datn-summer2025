<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Products_image;
use App\Models\Product_variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Products::with(['images', 'variants.attributeValues.attribute'])->get();
        return response()->json($products);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    private function updateProductQuantity(Products $product)
    {
        if ($product->variants()->exists()) {
            $totalQuantity = $product->variants()->sum('quantity');
            $product->update(['quantity' => $totalQuantity]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('products', 'name')
                ],
                'description' => 'required|string|min:10',
                'price' => 'required|numeric|min:0',
                'discount_price' => 'nullable|numeric|min:0|lt:price',
                'original_price' => 'nullable|numeric|min:0',
                'quantity' => 'required_without:variants|integer|min:0|nullable',
                'brand_id' => 'required|exists:brands,id',
                'category_id' => 'required|exists:categories,id',
                'images' => 'required|array|min:1',
                'images.*.is_main' => 'required|boolean',
                'images.*.sub_image' => [
                    'required',
                    'string',
                    'regex:/^(data:image\/(jpeg|png|jpg|gif);base64,|.*\.(jpg|jpeg|png|gif))$/i'
                ],
                'variants' => 'nullable|array',
                'variants.*.price' => 'required_with:variants|numeric|min:0',
                'variants.*.original_price' => 'required_with:variants|numeric|min:0',
                'variants.*.quantity' => 'required_with:variants|integer|min:0',
                'variants.*.sku' => [
                    'required_with:variants',
                    'string',
                    'regex:/^[A-Za-z0-9-_]+$/',
                    'unique:product_variants,sku'
                ],
                'variants.*.attribute_values' => 'required_with:variants|array|min:1',
                'variants.*.attribute_values.*' => 'required_with:variants|exists:attribute_values,id'
            ], [
                'name.required' => 'Tên sản phẩm không được để trống',
                'name.unique' => 'Tên sản phẩm đã tồn tại',
                'description.required' => 'Mô tả sản phẩm không được để trống',
                'description.min' => 'Mô tả sản phẩm phải có ít nhất 10 ký tự',
                'price.required' => 'Giá sản phẩm không được để trống',
                'price.min' => 'Giá sản phẩm không được âm',
                'discount_price.min' => 'Giá khuyến mãi không được âm',
                'discount_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá gốc',
                'quantity.min' => 'Số lượng không được âm',
                'images.required' => 'Phải có ít nhất 1 ảnh sản phẩm',
                'images.*.sub_image.regex' => 'File ảnh không hợp lệ (chỉ chấp nhận JPEG, PNG, JPG, GIF)',
                'variants.*.price.min' => 'Giá biến thể không được âm',
                'variants.*.quantity.min' => 'Số lượng biến thể không được âm',
                'variants.*.sku.unique' => 'Mã SKU đã tồn tại',
                'variants.*.sku.regex' => 'Mã SKU chỉ được chứa chữ cái, số và dấu gạch ngang',
                'variants.*.attribute_values.required_with' => 'Biến thể phải có ít nhất 1 thuộc tính'
            ]);

            $product = Products::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'price' => $request->price,
                'discount_price' => $request->discount_price,
                'original_price' => $request->original_price,
                'quantity' => $request->has('variants') ? 0 : $request->quantity,
                'brand_id' => $request->brand_id,
                'category_id' => $request->category_id,
            ]);

            foreach ($request->images as $image) {
                $product->images()->create([
                    'is_main' => $image['is_main'],
                    'sub_image' => $image['sub_image']
                ]);
            }

            if ($request->has('variants') && !empty($request->variants)) {
                foreach ($request->variants as $variant) {
                    $newVariant = $product->variants()->create([
                        'price' => $variant['price'],
                        'original_price' => isset($variant['original_price']) && is_numeric($variant['original_price']) 
                            ? $variant['original_price'] 
                            : $variant['price'], // Mặc định lấy price nếu original_price không hợp lệ
                        'quantity' => $variant['quantity'],
                        'sku' => $variant['sku']
                    ]);
            
                    $newVariant->attributeValues()->attach($variant['attribute_values']);
                }
                $this->updateProductQuantity($product);
            }

            DB::commit();
            return response()->json($product->load(['images', 'variants.attributeValues.attribute']), 201);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Products $product)
    {
        return response()->json($product->load(['images', 'variants.attributeValues.attribute']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Products $products)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Products $product)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('products', 'name')->ignore($product->id)
                ],
                'description' => 'required|string|min:10',
                'price' => 'required|numeric|min:0',
                'discount_price' => 'nullable|numeric|min:0|lt:price',
                'original_price' => 'nullable|numeric|min:0',
                'quantity' => 'required_without:variants|integer|min:0|nullable',
                'brand_id' => 'required|exists:brands,id',
                'category_id' => 'required|exists:categories,id',
                'images' => 'required|array|min:1',
                'images.*.is_main' => 'required|boolean',
                'images.*.sub_image' => [
                    'required',
                    'string',
                    'regex:/^(data:image\/(jpeg|png|jpg|gif);base64,|.*\.(jpg|jpeg|png|gif))$/i'
                ],
                'variants' => 'nullable|array',
                'variants.*.price' => 'required_with:variants|numeric|min:0',
                'variants.*.original_price' => 'required_with:variants|numeric|min:0',
                'variants.*.quantity' => 'required_with:variants|integer|min:0',
                'variants.*.sku' => [
                    'required_with:variants',
                    'string',
                    'regex:/^[A-Za-z0-9-_]+$/',
                    Rule::unique('product_variants', 'sku')->ignore($product->id, 'products_id')
                ],
                'variants.*.attribute_values' => 'required_with:variants|array|min:1',
                'variants.*.attribute_values.*' => 'required_with:variants|exists:attribute_values,id'
            ], [
                'name.required' => 'Tên sản phẩm không được để trống',
                'name.unique' => 'Tên sản phẩm đã tồn tại',
                'description.required' => 'Mô tả sản phẩm không được để trống',
                'description.min' => 'Mô tả sản phẩm phải có ít nhất 10 ký tự',
                'price.required' => 'Giá sản phẩm không được để trống',
                'price.min' => 'Giá sản phẩm không được âm',
                'discount_price.min' => 'Giá khuyến mãi không được âm',
                'discount_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá gốc',
                'quantity.min' => 'Số lượng không được âm',
                'images.required' => 'Phải có ít nhất 1 ảnh sản phẩm',
                'images.*.sub_image.regex' => 'File ảnh không hợp lệ (chỉ chấp nhận JPEG, PNG, JPG, GIF)',
                'variants.*.price.min' => 'Giá biến thể không được âm',
                'variants.*.quantity.min' => 'Số lượng biến thể không được âm',
                'variants.*.sku.unique' => 'Mã SKU đã tồn tại',
                'variants.*.sku.regex' => 'Mã SKU chỉ được chứa chữ cái, số và dấu gạch ngang',
                'variants.*.attribute_values.required_with' => 'Biến thể phải có ít nhất 1 thuộc tính'
            ]);

            $updateData = [
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'price' => $request->price,
                'discount_price' => $request->discount_price,
                'original_price' => $request->original_price,
                'brand_id' => $request->brand_id,
                'category_id' => $request->category_id,
            ];

            // Only update quantity if no variants
            if (!$request->has('variants')) {
                $updateData['quantity'] = $request->quantity;
            }

            $product->update($updateData);

            if ($request->has('images')) {
                $product->images()->delete();
                foreach ($request->images as $image) {
                    $product->images()->create([
                        'is_main' => $image['is_main'],
                        'sub_image' => $image['sub_image']
                    ]);
                }
            }

            if ($request->has('variants')) {
                $product->variants()->delete();
                if (!empty($request->variants)) {
                    foreach ($request->variants as $variant) {
                        $newVariant = $product->variants()->create([
                            'price' => $variant['price'],
                            'original_price' => isset($variant['original_price']) && is_numeric($variant['original_price']) 
                                ? $variant['original_price'] 
                                : $variant['price'], // Mặc định lấy price nếu original_price không hợp lệ
                            'quantity' => $variant['quantity'],
                            'sku' => $variant['sku']
                        ]);
            
                        $newVariant->attributeValues()->attach($variant['attribute_values']);
                    }
                    $this->updateProductQuantity($product);
                } else {
                    $product->update(['quantity' => 0]);
                }
            }

            DB::commit();
            return response()->json($product->load(['images', 'variants.attributeValues.attribute']));
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Products $product)
    {
        $product->delete();
        return response()->json(null, 204);
    }
}
