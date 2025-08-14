<?php

namespace App\Http\Controllers;

use App\Models\Brands;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class BrandsController extends Controller
{
    public function index()
    {
        $cacheKey = 'brands_index';
        $cacheTTL = 3600; // 1 tiếng

        $brands = Cache::tags(['brands'])->remember($cacheKey, $cacheTTL, function () {
            $brands = Brands::select('id', 'name', 'description', 'image', 'slug', 'parent_id', 'is_active')
                ->withCount('products')
                ->get();

            $brands->transform(function ($brand) {
                $brand->image = $brand->image ? url('storage/' . $brand->image) : null;
                return $brand;
            });

            return $brands;
        });

        return response()->json($brands);
    }

    public function show($id)
    {
        $cacheKey = "brand_show_{$id}";
        $cacheTTL = 3600; // 1 tiếng

        $brand = Cache::tags(['brands'])->remember($cacheKey, $cacheTTL, function () use ($id) {
            $brand = Brands::findOrFail($id);
            $brand->image = $brand->image ? url('storage/' . $brand->image) : null;
            return $brand;
        });

        return response()->json($brand);
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'parent_id' => 'nullable|exists:brands,id',
                'is_active' => 'boolean',
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
                'parent_id' => $request->parent_id ?: null,
                'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
            ]);
            Cache::tags(['brands'])->flush();
            return response()->json($brand, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            \Log::info('Received update request for brand:', [
                'id' => $id,
                'request_data' => $request->all()
            ]);

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'parent_id' => 'nullable|exists:brands,id',
                'is_active' => 'required|boolean',
            ]);

            $brand = Brands::findOrFail($id);

            if ($request->name !== $brand->name) {
                $slug = Str::slug($request->name);
                $originalSlug = $slug;
                $count = 1;
                while (Brands::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $brand->slug = $slug;
            }

            if ($request->hasFile('image')) {
                if ($brand->image) {
                    Storage::disk('public')->delete($brand->image);
                }
                $brand->image = $request->file('image')->store('brands', 'public');
            }

            $brand->name = $request->name;
            $brand->description = $request->description;
            $brand->parent_id = $request->parent_id ?: null;
            $brand->is_active = $request->boolean('is_active');

            $brand->save();

            \Log::info('Brand updated successfully:', [
                'id' => $brand->id,
                'name' => $brand->name,
                'is_active' => $brand->is_active
            ]);

            $brand->image = $brand->image ? url('storage/' . $brand->image) : null;
            Cache::tags(['brands'])->flush();
            return response()->json($brand);
        } catch (\Exception $e) {
            \Log::error('Error updating brand:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $brand = Brands::findOrFail($id);
            $brand->delete();
            Cache::tags(['brands'])->flush();
            return response()->json(['message' => 'Brand deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bulkDestroy(Request $request)
    {
        \Log::info($request->all());
        try {
            $ids = $request->input('ids', []);

            if (empty($ids) || !is_array($ids)) {
                return response()->json(['message' => 'No brand ids provided'], 400);
            }

            $brands = Brands::whereIn('id', $ids)->get();

            foreach ($brands as $brand) {
                if ($brand->image && Storage::disk('public')->exists($brand->image)) {
                    Storage::disk('public')->delete($brand->image);
                }
                $brand->delete();
            }
            Cache::tags(['brands'])->flush();
            return response()->json(['message' => 'Brands deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Bulk delete failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus($id, Request $request)
    {
        try {
            $request->validate([
                'is_active' => 'required|boolean'
            ]);

            $brand = Brands::findOrFail($id);
            $brand->is_active = $request->boolean('is_active');
            $brand->save();

            Cache::tags(['brands'])->flush();

            return response()->json([
                'message' => 'Brand status updated successfully',
                'brand' => $brand
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update brand status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
