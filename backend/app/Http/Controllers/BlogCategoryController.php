<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = BlogCategory::withCount('blogs')->get();
            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Blog categories retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve blog categories: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $category = BlogCategory::with('blogs')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $category,
                'message' => 'Blog category retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Blog category not found'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $categoryData = $request->except('image');

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('public/blog-categories', $imageName);
                $categoryData['image'] = Storage::url($path);
            }

            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $counter = 1;
            while (BlogCategory::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            $categoryData['slug'] = $slug;

            $category = BlogCategory::create($categoryData);

            return response()->json([
                'success' => true,
                'data' => $category,
                'message' => 'Blog category created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create blog category: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $category = BlogCategory::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'sometimes|required|in:active,inactive',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $categoryData = $request->except('image');

            if ($request->hasFile('image')) {
                if ($category->image) {
                    $oldImagePath = str_replace('/storage/', 'public/', $category->image);
                    Storage::delete($oldImagePath);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('public/blog-categories', $imageName);
                $categoryData['image'] = Storage::url($path);
            }

            if (isset($categoryData['name']) && $categoryData['name'] !== $category->name) {
                $slug = Str::slug($request->name);
                $originalSlug = $slug;
                $counter = 1;
                while (BlogCategory::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = $originalSlug . '-' . $counter++;
                }
                $categoryData['slug'] = $slug;
            }

            $category->update($categoryData);

            return response()->json([
                'success' => true,
                'data' => $category,
                'message' => 'Blog category updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update blog category: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = BlogCategory::findOrFail($id);

            if ($category->blogs()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with existing blogs'
                ], 400);
            }

            if ($category->image) {
                $imagePath = str_replace('/storage/', 'public/', $category->image);
                Storage::delete($imagePath);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Blog category deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete blog category: ' . $e->getMessage()
            ], 500);
        }
    }
}
