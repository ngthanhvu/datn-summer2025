<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\VariantController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\BlogsController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CouponsController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\FavoriteProductController;
use App\Http\Controllers\ProductImportController;
use App\Models\Inventory;

// Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/admin/user', [AuthController::class, 'listUser']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/reset-password-profile', [AuthController::class, 'resetPasswordProfile']);

    Route::post('/inventory/update', [InventoryController::class, 'updateStock']);
    Route::get('/inventory/movements', [InventoryController::class, 'getMovements']);
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::get('inventory/movement/{id}/pdf', [InventoryController::class, 'exportMovementPdf']);

    Route::post('/blogs', [BlogsController::class, 'store']);
    Route::put('/blogs/{id}', [BlogsController::class, 'update']);
    Route::delete('/blogs/{id}', [BlogsController::class, 'destroy']);
    Route::get('/blogs/{id}', [BlogsController::class, 'show']);

    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::post('/cart/transfer-session-to-user', [CartController::class, 'transferCartFromSessionToUser']);

    Route::get('/orders', [OrdersController::class, 'index']);
    Route::get('/orders/{id}', [OrdersController::class, 'show']);
    Route::post('/orders', [OrdersController::class, 'store']);
    Route::get('/orders/track/{tracking_code}', [OrdersController::class, 'getOrderByTrackingCode']);
    Route::put('/orders/{id}/status', [OrdersController::class, 'updateStatus']);

    Route::get('/me/address', [AddressController::class, 'getMyAddress']);
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);

    Route::get('/favorites', [FavoriteProductController::class, 'index']);
    Route::post('/favorites', [FavoriteProductController::class, 'store']);
    Route::get('/favorites/check/{slug}', [FavoriteProductController::class, 'check']);
    Route::delete('/favorites/{product_slug}', [FavoriteProductController::class, 'destroy']);
});

// Public blog routes
Route::get('/blogs', [BlogsController::class, 'index']);
Route::get('/blogs/{slug}', [BlogsController::class, 'showBySlug']);

// Brand routes
Route::get('/brands', [BrandsController::class, 'index']);
Route::get('/brands/{id}', [BrandsController::class, 'show']);
Route::post('/brands', [BrandsController::class, 'store']);
Route::put('/brands/{id}', [BrandsController::class, 'update']);
Route::delete('/brands/{id}', [BrandsController::class, 'destroy']);
Route::post('/brands/bulk-delete', [BrandsController::class, 'bulkDestroy']);

// Category routes
Route::get('/categories', [CategoriesController::class, 'index']);
Route::get('/categories/{id}', [CategoriesController::class, 'show']);
Route::post('/categories', [CategoriesController::class, 'store']);
Route::put('/categories/{id}', [CategoriesController::class, 'update']);
Route::delete('/categories/{id}', [CategoriesController::class, 'destroy']);
Route::post('/categories/bulk-delete', [CategoriesController::class, 'bulkDestroy']);

// Google login
Route::get('/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Product routes
Route::get('/products/search', [ProductsController::class, 'search']);
Route::get('/products/filter-options', [ProductsController::class, 'getFilterOptions']);
Route::get('/products', [ProductsController::class, 'index']);
Route::post('/products', [ProductsController::class, 'store']);
Route::put('/products/{id}', [ProductsController::class, 'update']);
Route::get('/products/slug/{slug}', [ProductsController::class, 'getProductBySlug']);
Route::get('/products/{id}', [ProductsController::class, 'getProductById']);
Route::delete('/products/{id}', [ProductsController::class, 'destroy']);
Route::delete('/products/bulk-delete', [ProductsController::class, 'bulkDestroy']);
Route::get('/products/{id}/favorite', [ProductsController::class, 'favorite']);

// Variant routes
Route::get('/variants', [VariantController::class, 'index']);

// Guest cart routes
Route::get('/guest-cart', [CartController::class, 'index']);
Route::post('/guest-cart', [CartController::class, 'store']);
Route::put('/guest-cart/{id}', [CartController::class, 'update']);
Route::delete('/guest-cart/{id}', [CartController::class, 'destroy']);

// Coupon routes
Route::get('/coupons', [CouponsController::class, 'index']);
Route::post('/coupons', [CouponsController::class, 'store']);
Route::get('/coupons/{id}', [CouponsController::class, 'show']);
Route::put('/coupons/{id}', [CouponsController::class, 'update']);
Route::delete('/coupons/{id}', [CouponsController::class, 'destroy']);
Route::post('/coupons/validate', [CouponsController::class, 'validate_coupon']);

// Product review routes
Route::get('/product-reviews', [ProductReviewController::class, 'index']);
Route::post('/product-reviews', [ProductReviewController::class, 'store']);
Route::get('/product-reviews/{id}', [ProductReviewController::class, 'show']);
Route::put('/product-reviews/{id}', [ProductReviewController::class, 'update']);
Route::delete('/product-reviews/{id}', [ProductReviewController::class, 'destroy']);
Route::get('/product-reviews/product/{slug}', [ProductReviewController::class, 'getByProductSlug']);
Route::get('/product-reviews/check/{userId}/{productSlug}', [ProductReviewController::class, 'checkUserReview']);
Route::post('/product-reviews/{id}/admin-reply', [ProductReviewController::class, 'adminReply']);
Route::put('/product-reviews/{id}/admin-reply', [ProductReviewController::class, 'updateAdminReply']);
Route::get('/product-reviews/category/{categoryId}', [ProductReviewController::class, 'getByCategory']);
Route::get('/products-reviewed', [ProductReviewController::class, 'getReviewedProducts']);

// Payment routes
Route::prefix('payment')->group(function () {
    Route::post('vnpay', [PaymentController::class, 'generateVnpayUrl']);
    Route::post('momo', [PaymentController::class, 'generateMomoUrl']);
    Route::post('paypal', [PaymentController::class, 'generatePaypalUrl']);

    Route::get('vnpay-callback', [PaymentController::class, 'vnpayCallback']);
    Route::get('momo-callback', [PaymentController::class, 'momoCallback']);
    Route::get('paypal-callback', [PaymentController::class, 'paypalCallback']);
    Route::get('paypal-cancel', [PaymentController::class, 'paypalCancel']);
});

// Product import routes
Route::prefix('products')->group(function () {
    Route::get('import', [ProductImportController::class, 'index']);
    Route::post('import', [ProductImportController::class, 'import']);
    Route::get('import/template', [ProductImportController::class, 'downloadTemplate']);
    Route::get('import/history', [ProductImportController::class, 'getImportHistory']);
});
