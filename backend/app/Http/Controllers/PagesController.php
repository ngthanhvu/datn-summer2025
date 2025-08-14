<?php

namespace App\Http\Controllers;

use App\Models\Pages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class PagesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cacheKey = 'pages_index_' . md5(json_encode($request->all()));
        $cacheTTL = 3600; // 1 tiếng

        $pages = Cache::tags(['pages'])->remember($cacheKey, $cacheTTL, function () use ($request) {
            $query = Pages::with(['creator', 'updater']);

            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }

            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            if ($request->has('search') && $request->search) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            return $query->ordered()->paginate(10);
        });

        return response()->json([
            'success' => true,
            'data' => $pages,
            'message' => 'Pages retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:policy,support,about,other',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'status' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $counter = 1;

        while (Pages::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $page = Pages::create([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'type' => $request->type,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'status' => $request->status ?? true,
            'sort_order' => $request->sort_order ?? 0,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id()
        ]);
        Cache::tags(['pages'])->flush();

        return response()->json([
            'success' => true,
            'data' => $page->load(['creator', 'updater']),
            'message' => 'Page created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cacheKey = "page_show_{$id}";
        $cacheTTL = 3600;

        $page = Cache::tags(['pages'])->remember($cacheKey, $cacheTTL, function () use ($id) {
            return Pages::with(['creator', 'updater'])->find($id);
        });

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $page,
            'message' => 'Page retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $page = Pages::find($id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:policy,support,about,other',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'status' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $slug = $page->slug;
        if ($request->title !== $page->title) {
            $slug = Str::slug($request->title);
            $originalSlug = $slug;
            $counter = 1;

            while (Pages::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $page->update([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'type' => $request->type,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'status' => $request->status,
            'sort_order' => $request->sort_order,
            'updated_by' => Auth::id()
        ]);
        Cache::tags(['pages'])->flush();

        return response()->json([
            'success' => true,
            'data' => $page->load(['creator', 'updater']),
            'message' => 'Page updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $page = Pages::find($id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found'
            ], 404);
        }

        $page->delete();
        Cache::tags(['pages'])->flush();
        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully'
        ]);
    }

    /**
     * Get pages by type for frontend
     */
    public function getByType($type)
    {
        $cacheKey = "pages_type_{$type}";
        $cacheTTL = 3600;

        $pages = Cache::tags(['pages'])->remember($cacheKey, $cacheTTL, function () use ($type) {
            return Pages::where('type', $type)
                ->where('status', true)
                ->ordered()
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => $pages,
            'message' => 'Pages retrieved successfully'
        ]);
    }

    /**
     * Get page by slug for frontend
     */
    public function getBySlug($slug)
    {
        $cacheKey = "page_slug_{$slug}";
        $cacheTTL = 3600;

        $page = Cache::tags(['pages'])->remember($cacheKey, $cacheTTL, function () use ($slug) {
            return Pages::where('slug', $slug)
                ->where('status', true)
                ->first();
        });

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $page,
            'message' => 'Page retrieved successfully'
        ]);
    }

    /**
     * Update page status
     */
    public function updateStatus(Request $request, $id)
    {
        $page = Pages::find($id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $page->update([
            'status' => $request->status,
            'updated_by' => Auth::id()
        ]);
        Cache::tags(['pages'])->flush();

        return response()->json([
            'success' => true,
            'data' => $page,
            'message' => 'Page status updated successfully'
        ]);
    }
}
