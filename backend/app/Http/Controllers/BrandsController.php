<?php

namespace App\Http\Controllers;

use App\Models\Brands;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandsController extends Controller
{
    public function index()
    {
        $brands = Brands::select('id', 'name', 'description', 'image', 'slug', 'parent_id')->get();
        return response()->json($brands);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'parent_id' => 'nullable|exists:brands,id',
            ]);
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $count = 1;
            while (Brands::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            $imagePath = $request->file('image')->store('brands', 'public');

            $brand = Brands::create([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'image' => $imagePath,
                'parent_id' => $request->parent_id ?: null, // Nếu không chọn parent_id thì để null
            ]);
            return response()->json($brand, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'parent_id' => 'nullable|exists:brands,id',
            ]);

            $brands = Brands::findOrFail($id);

            if ($brands->name !== $request->name) {
                $slug = Str::slug($request->name);
                $originalSlug = $slug;
                $count = 1;
                while (Brands::where('slug', $slug)->where('id', '!=', $brands->id)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $brands->slug = $slug;
            }

            if ($request->hasFile('image')) {
                $brands->image = $request->file('image')->store('brands', 'public');
            }

            $brands->name = $request->name;
            $brands->description = $request->description;
            $brands->parent_id = $request->parent_id ?: null;
            $brands->save();

            return response()->json($brands);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    public function destroy($id)
    {
        try {
            $brand = Brands::findOrFail($id);
            $brand->delete();
            return response()->json(['message' => 'Brand deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
