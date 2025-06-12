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

        // Kiểm tra xem người dùng đã đánh giá sản phẩm này chưa
        $existingReview = ProductReview::where('user_id', $request->user_id)
            ->where('product_slug', $request->product_slug)
            ->whereNull('parent_id') // Chỉ kiểm tra đánh giá chính, không phải phản hồi
            ->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'Bạn đã đánh giá sản phẩm này rồi. Vui lòng chỉnh sửa đánh giá hiện có thay vì tạo mới.'
            ], 422);
        }

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
            ->where('product_slug', 'LIKE', '%' . $slug . '%')
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

    public function checkUserReview(Request $request, $userId, $productSlug)
    {
        $review = ProductReview::where('user_id', $userId)
            ->where('product_slug', $productSlug)
            ->whereNull('parent_id') 
            ->with(['images'])
            ->first();

        if ($review) {
            return response()->json([
                'hasReviewed' => true,
                'review' => $review
            ]);
        }

        return response()->json([
            'hasReviewed' => false
        ]);
    }

    public function updateAdminReply(Request $request, $id)
    {
        // Tìm kiếm phản hồi admin đã tồn tại dựa trên ID trong URL
        $reply = ProductReview::findOrFail($id);
        
        // Nếu đã tìm thấy reply theo ID, chỉ validate content
        $validated = $request->validate([
            'content' => 'required|string',
        ]);
        
        // Cập nhật phản hồi đã tồn tại
        $reply->update([
            'content' => $validated['content'],
        ]);
        
        return response()->json($reply->fresh());
    }

    public function getByCategory($categoryId)
    {
        $reviews = ProductReview::with(['user', 'product', 'replies', 'images'])
            ->whereHas('product', function ($query) use ($categoryId) {
                $query->where('categories_id', $categoryId);
            })
            ->whereNull('parent_id')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($reviews);
    }

    public function getByBrand($brandId)
    {
        $reviews = ProductReview::with(['user', 'product', 'replies', 'images'])
            ->whereHas('product', function ($query) use ($brandId) {
                $query->where('brand_id', $brandId);
            })
            ->whereNull('parent_id')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($reviews);
    }
}
