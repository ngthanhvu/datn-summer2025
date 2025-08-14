<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class CategoriesController extends Controller
{
    public function index()
    {
        $cacheKey = 'categories_index';
        $cacheTTL = 3600; // 1 tiếng

        $categories = Cache::tags(['categories'])->remember($cacheKey, $cacheTTL, function () {
            $categories = Categories::select('id', 'name', 'slug', 'description', 'image', 'is_active', 'parent_id')
                ->withCount('products')
                ->get();

            $categories->transform(function ($category) {
                $category->image = $category->image ? url('storage/' . $category->image) : null;
                return $category;
            });

            return $categories;
        });

        return response()->json($categories);
    }

    public function show($id)
    {
        $cacheKey = "category_show_{$id}";
        $cacheTTL = 3600; // 1 tiếng

        $category = Cache::tags(['categories'])->remember($cacheKey, $cacheTTL, function () use ($id) {
            $category = Categories::findOrFail($id);
            $category->image = $category->image ? url('storage/' . $category->image) : null;
            return $category;
        });

        return response()->json($category);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'parent_id' => 'nullable|exists:categories,id',
                'is_active' => 'required|boolean',
            ]);

            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $count = 1;
            while (Categories::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('categories', 'public');
            } else {
                $imagePath = null;
            }

            $category = Categories::create([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'image' => $imagePath,
                'parent_id' => $request->parent_id ?: null,
                'is_active' => $request->boolean('is_active'),
            ]);

            $category->image = $imagePath ? url('storage/' . $imagePath) : null;
            Cache::tags(['categories'])->flush();
            return response()->json($category, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            \Log::info('Received update request for category:', [
                'id' => $id,
                'request_data' => $request->all()
            ]);

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'parent_id' => 'nullable|exists:categories,id',
                'is_active' => 'required|boolean',
            ]);

            $category = Categories::findOrFail($id);

            if ($request->name !== $category->name) {
                $slug = Str::slug($request->name);
                $originalSlug = $slug;
                $count = 1;
                while (Categories::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $category->slug = $slug;
            }

            if ($request->hasFile('image')) {
                // Xóa ảnh cũ nếu có
                if ($category->image) {
                    Storage::disk('public')->delete($category->image);
                }
                $category->image = $request->file('image')->store('categories', 'public');
            }

            $category->name = $request->name;
            $category->description = $request->description;
            $category->parent_id = $request->parent_id ?: null;
            $category->is_active = $request->boolean('is_active');

            $category->save();

            \Log::info('Category updated successfully:', [
                'id' => $category->id,
                'name' => $category->name,
                'is_active' => $category->is_active
            ]);

            $category->image = $category->image ? url('storage/' . $category->image) : null;
            Cache::tags(['categories'])->flush();
            return response()->json($category);
        } catch (\Exception $e) {
            \Log::error('Error updating category:', [
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
            $category = Categories::findOrFail($id);
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $category->delete();
            Cache::tags(['categories'])->flush();
            return response()->json(['message' => 'Category deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bulkDestroy(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids) || !is_array($ids)) {
                return response()->json(['message' => 'No category ids provided'], 400);
            }

            $categories = Categories::whereIn('id', $ids)->get();

            foreach ($categories as $category) {
                if ($category->image && Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }
                $category->delete();
            }
            Cache::tags(['categories'])->flush();
            return response()->json(['message' => 'Categories deleted successfully'], 200);
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

            $category = Categories::findOrFail($id);
            $category->is_active = $request->boolean('is_active');
            $category->save();

            Cache::tags(['categories'])->flush();

            return response()->json([
                'message' => 'Category status updated successfully',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update category status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
