<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{

    public function index()
    {
        $categories = Categories::select('id', 'name', 'slug', 'description', 'image', 'is_active', 'parent_id')->get();

        $categories->transform(function ($category) {
            $category->image = url('storage/' . $category->image);
            return $category;
        });
        return response()->json($categories);
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
                'is_active' => $request->is_active ?: true,
            ]);
            return response()->json($category, 201);
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
                'is_active' => 'boolean',
            ]);
            $category = Categories::findOrFail($id);
            if ($category->name !== $request->name) {
                $slug = Str::slug($request->name);
                $originalSlug = $slug;
                $count = 1;
                while (Categories::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $category->slug = $slug;
            }
            if ($request->hasFile('image')) {
                $category->image = $request->file('image')->store('categories', 'public');
            }

            $category->name = $request->name;
            $category->description = $request->description;
            $category->parent_id = $request->parent_id ?: null;
            $category->is_active = (bool) $request->is_active;
            $category->save();

            return response()->json($category);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Categories::findOrFail($id);
            $category->delete();
            return response()->json(['message' => 'Category deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
