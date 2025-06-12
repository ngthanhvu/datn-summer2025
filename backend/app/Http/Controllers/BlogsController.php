<?php

namespace App\Http\Controllers;

use App\Models\Blogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BlogsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Blogs::with('author')->orderBy('created_at', 'desc');
            $blogs = $query->paginate(10);
            return response()->json([
                'success' => true,
                'data' => $blogs,
                'message' => 'Blogs retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve blogs: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $blog = Blogs::with('author')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $blog,
                'message' => 'Blog retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve blog: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showBySlug($slug)
    {
        try {
            $blog = Blogs::with('author')->where('slug', $slug)->firstOrFail();
            return response()->json([
                'success' => true,
                'data' => $blog,
                'message' => 'Blog retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve blog: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        \Log::info('Store method called', ['request' => $request->all()]);
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:500',
                'content' => 'required|string',
                'status' => 'required|in:draft,published,archived',
                'published_at' => 'nullable|date',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $blogData = $request->except('image');
            // Gán author_id mặc định nếu không có
            $blogData['author_id'] = 1;

            if ($blogData['status'] === 'published' && empty($blogData['published_at'])) {
                $blogData['published_at'] = now();
            }

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('public/blogs', $imageName);
                $blogData['image'] = \Storage::url($path);
            }

            $blog = Blogs::create($blogData);
            $blog->load('author');

            return response()->json([
                'success' => true,
                'data' => $blog,
                'message' => 'Blog created successfully'
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Blog creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create blog: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $blog = Blogs::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string|max:500',
                'content' => 'sometimes|required|string',
                'status' => 'sometimes|required|in:draft,published,archived',
                'published_at' => 'nullable|date',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $blogData = $request->except('image');

            if (isset($blogData['status']) && $blogData['status'] === 'published' && empty($blogData['published_at']) && $blog->status !== 'published') {
                $blogData['published_at'] = now();
            }

            if ($request->hasFile('image')) {
                if ($blog->image) {
                    $oldImagePath = str_replace('/storage/', 'public/', $blog->image);
                    Storage::delete($oldImagePath);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('public/blogs', $imageName);
                $blogData['image'] = Storage::url($path);
            }

            $blog->update($blogData);
            $blog->load('author');

            return response()->json([
                'success' => true,
                'data' => $blog,
                'message' => 'Blog updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update blog: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $blog = Blogs::findOrFail($id);
            
            if ($blog->image) {
                $imagePath = str_replace('/storage/', 'public/', $blog->image);
                Storage::delete($imagePath);
            }
            
            $blog->delete();

            return response()->json([
                'success' => true,
                'message' => 'Blog deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete blog: ' . $e->getMessage()
            ], 500);
        }
    }
}