<?php

namespace App\Http\Controllers;

use App\Models\ProductReview;
use App\Models\ReviewImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductReviewController extends Controller
{
    public function index()
    {
        $reviews = ProductReview::with(['user', 'product', 'replies', 'images'])->get();
        return response()->json($reviews);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_slug' => 'required|exists:products,slug',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:product_reviews,id',
            'is_admin_reply' => 'boolean',
            'is_approved' => 'boolean',
            'is_hidden' => 'boolean',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048' 
        ]);

        $review = ProductReview::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $path = $imageFile->store('review_images', 'public');

                ReviewImage::create([
                    'review_id' => $review->id,
                    'image_path' => $path,
                ]);
            }
        }

        return response()->json($review->load(['images']), 201);
    }

    public function show($id)
    {
        $review = ProductReview::with(['user', 'product', 'replies.images', 'images'])->findOrFail($id);
        return response()->json($review);
    }

public function update(Request $request, $id)
{
    $review = ProductReview::with('images')->findOrFail($id);

    $validated = $request->validate([
        'rating' => 'nullable|integer|min:1|max:5',
        'content' => 'nullable|string',
        'is_approved' => 'nullable|boolean',
        'is_hidden' => 'nullable|boolean',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        'delete_image_ids' => 'nullable|array',
        'delete_image_ids.*' => 'integer|exists:review_images,id'
    ]);

    $review->update($validated);

    if (!empty($validated['delete_image_ids'])) {
        foreach ($validated['delete_image_ids'] as $imageId) {
            $image = ReviewImage::where('review_id', $review->id)->where('id', $imageId)->first();
            if ($image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }
        }
    }

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $imageFile) {
            $path = $imageFile->store('review_images', 'public');

            ReviewImage::create([
                'review_id' => $review->id,
                'image_path' => $path,
            ]);
        }
    }

    return response()->json($review->fresh(['images']));
}



    public function destroy($id)
    {
        $review = ProductReview::findOrFail($id);

        foreach ($review->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $review->delete();

        return response()->json(['message' => 'Xóa thành công']);
    }

    public function getByProductSlug($slug)
    {
        $reviews = ProductReview::with(['user', 'replies.images', 'images'])
            ->where('product_slug', $slug)
            ->whereNull('parent_id')
            ->where('is_hidden', 0)
            ->where('is_approved', 1)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($reviews);
    }

    public function adminReply(Request $request, $parentId)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_slug' => 'required|exists:products,slug',
            'content' => 'required|string',
            'is_approved' => 'boolean',
            'is_hidden' => 'boolean',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $parentReview = ProductReview::findOrFail($parentId);

        $reply = ProductReview::create([
            'user_id' => $validated['user_id'],
            'product_slug' => $validated['product_slug'],
            'rating' => null,
            'content' => $validated['content'],
            'parent_id' => $parentId,
            'is_admin_reply' => true,
            'is_approved' => $validated['is_approved'] ?? true,
            'is_hidden' => $validated['is_hidden'] ?? false,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $path = $imageFile->store('review_images', 'public');

                ReviewImage::create([
                    'review_id' => $reply->id,
                    'image_path' => $path,
                ]);
            }
        }

        return response()->json($reply->load(['images']), 201);
    }
}
