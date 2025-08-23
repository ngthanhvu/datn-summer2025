<?php

namespace App\Http\Controllers;

use App\Models\ProductReview;
use App\Models\ReviewImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Products;
use App\Notifications\NewCommentNotification;

class ProductReviewController extends Controller
{
    private $badWords = [
        'cặc',
        'lồn',
        'chó đẻ',
        'rác rưởi',
        'lừa đảo',
        'hàng giả',
        'sủa',
        'thối',
        'đểu',
        'vớ vẩn',
        'tào lao',
        'phịa',
        'bẩn thỉu',
        'khốn nạn',
        'mạt sát',
        'vô dụng',
        'lởm',
        'đểu cáng',
        'lừa gạt',
        'hạ phẩm',
        'mất dạy',
        'kệch cỡm',
        'bội bạc',
        'chửi rủa',
        'ngớ ngẩn',
        'xấu xí',
        'không ra gì',
        'dối trá',
        'độc hại',
        'lôi thôi',
        'kém chất lượng',
        'vô giá trị',
        'giả mạo',
        'đểu cáng',
        'lởm',
        'lừa gạt',
    ];


    private function containsBadWords($content)
    {
        foreach ($this->badWords as $word) {
            if (stripos($content, $word) !== false) {
                return true;
            }
        }
        return false;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 5);
        $query = ProductReview::with(['user', 'product', 'replies', 'images'])
            ->whereNull('parent_id');

        if ($request->get('badwords') == 1) {
            $badWords = $this->badWords;
            $query->where(function ($q) use ($badWords) {
                foreach ($badWords as $word) {
                    $q->orWhere('content', 'LIKE', "%$word%");
                }
            })->where('is_hidden', true)->where('is_approved', false);
        } else if ($request->get('negative') == 1) {
            $query->where('rating', '<=', 2)
                ->where('is_approved', true)
                ->where('is_hidden', false);
        } else if ($request->get('admin') == 1) {
        } else {
            $query->where('is_approved', true)->where('is_hidden', false);
        }

        $reviews = $query->orderByDesc('created_at')->paginate($perPage);
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

        // Check if user has purchased the product
        $hasPurchased = \App\Models\Orders::where('user_id', $request->user_id)
            ->where('status', 'completed')
            ->whereHas('orderDetails', function($query) use ($request) {
                $query->whereHas('variant', function($variantQuery) use ($request) {
                    $variantQuery->whereHas('product', function($productQuery) use ($request) {
                        $productQuery->where('slug', $request->product_slug);
                    });
                });
            })
            ->exists();

        if (!$hasPurchased) {
            return response()->json([
                'message' => 'Bạn chỉ có thể đánh giá sản phẩm sau khi đã mua và nhận hàng thành công.',
                'error' => 'purchase_required'
            ], 403);
        }

        // Check if user has already reviewed this product
        $existingReview = ProductReview::where('user_id', $request->user_id)
            ->where('product_slug', $request->product_slug)
            ->whereNull('parent_id')
            ->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'Bạn đã đánh giá sản phẩm này rồi.',
                'error' => 'already_reviewed'
            ], 400);
        }

        if ($this->containsBadWords($validated['content'])) {
            $validated['is_hidden'] = true;
            $validated['is_approved'] = false;
        } else {
            $validated['is_approved'] = true;
            $validated['is_hidden'] = false;
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

        $admin = \App\Models\User::where('role', 'admin')->first();
        if ($admin) {
            $admin->notify(new NewCommentNotification($review));
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

        if (isset($validated['is_approved']) && isset($validated['is_hidden'])) {
            if ($validated['is_approved'] === true) {
                $validated['is_hidden'] = false;
            } else if ($validated['is_hidden'] === true) {
                $validated['is_approved'] = false;
            }
        } else if (isset($validated['content'])) {
            if ($this->containsBadWords($validated['content'])) {
                $validated['is_hidden'] = true;
                $validated['is_approved'] = false;
            } else {
                $validated['is_hidden'] = false;
                $validated['is_approved'] = true;
            }
        }

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

    public function getByProductSlug($slug, Request $request)
    {
        $perPage = $request->get('per_page', 3);
        $userId = $request->user('api')?->id ?? $request->get('user_id');
        $query = ProductReview::with(['user', 'replies.images', 'images'])
            ->where('product_slug', 'LIKE', '%' . $slug . '%')
            ->whereNull('parent_id')
            ->where(function ($q) use ($userId) {
                $q->where(function ($q2) {
                    $q2->where('is_approved', true)->where('is_hidden', false);
                });
                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
            })
            ->orderByDesc('created_at');
        $reviews = $query->paginate($perPage);
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

    public function checkUserPurchase(Request $request, $userId, $productSlug)
    {
        $hasPurchased = \App\Models\Orders::where('user_id', $userId)
            ->where('status', 'completed')
            ->whereHas('orderDetails', function($query) use ($productSlug) {
                $query->whereHas('variant', function($variantQuery) use ($productSlug) {
                    $variantQuery->whereHas('product', function($productQuery) use ($productSlug) {
                        $productQuery->where('slug', $productSlug);
                    });
                });
            })
            ->exists();

        return response()->json([
            'hasPurchased' => $hasPurchased
        ]);
    }

    public function updateAdminReply(Request $request, $id)
    {
        $reply = ProductReview::findOrFail($id);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $reply->update([
            'content' => $validated['content'],
        ]);

        return response()->json($reply->fresh());
    }

    public function getByCategory($categoryId, Request $request)
    {
        $perPage = $request->get('per_page', 5);
        $reviews = ProductReview::with(['user', 'product', 'replies', 'images'])
            ->whereHas('product', function ($query) use ($categoryId) {
                $query->where('categories_id', $categoryId);
            })
            ->whereNull('parent_id')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json($reviews);
    }

    public function getByBrand($brandId, Request $request)
    {
        $perPage = $request->get('per_page', 5);
        $reviews = ProductReview::with(['user', 'product', 'replies', 'images'])
            ->whereHas('product', function ($query) use ($brandId) {
                $query->where('brand_id', $brandId);
            })
            ->whereNull('parent_id')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json($reviews);
    }

    public function getReviewedProducts()
    {
        $products = Products::with(['images'])
            ->get()
            ->map(function ($product) {
                $reviews = ProductReview::where('product_slug', $product->slug)
                    ->whereNull('parent_id');
                $reviewCount = $reviews->count();
                $averageRating = $reviews->avg('rating');
                $latestReview = $reviews->orderByDesc('created_at')->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->images->first() ? $product->images->first()->image_path : null,
                    'review_count' => $reviewCount,
                    'average_rating' => $averageRating ?? 0,
                    'latest_review_date' => $latestReview ? $latestReview->created_at : null,
                    'slug' => $product->slug
                ];
            })
            ->filter(function ($item) {
                return $item['review_count'] > 0;
            })
            ->values();
        return response()->json($products);
    }

    public function latestReviews(Request $request)
    {
        $perPage = $request->get('per_page', 6);
        $reviews = ProductReview::with(['user', 'product', 'images'])
            ->whereNull('parent_id')
            ->where('is_approved', true)
            ->where('is_hidden', false)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json($reviews);
    }
}
