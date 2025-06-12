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
use App\Http\Controllers\CartController;
use App\Http\Controllers\CouponsController;
use App\Http\Controllers\ProductReviewController;

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
    Route::post('/update-profile', [AuthController::class, 'updateProfile']); // Thêm route này
    Route::post('/reset-password-profile', [AuthController::class, 'resetPasswordProfile']);
    Route::post('/inventory/update', [InventoryController::class, 'updateStock']);
    Route::get('/inventory/movements', [InventoryController::class, 'getMovements']);
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::post('/cart/transfer-session-to-user', [CartController::class, 'transferCartFromSessionToUser']);
});

Route::get('/brands', [BrandsController::class, 'index']);
Route::get('/brands/{id}', [BrandsController::class, 'show']);
Route::post('/brands', [BrandsController::class, 'store']);
Route::put('/brands/{id}', [BrandsController::class, 'update']);
Route::delete('/brands/{id}', [BrandsController::class, 'destroy']);

Route::get('/categories', [CategoriesController::class, 'index']);
Route::get('/categories/{id}', [CategoriesController::class, 'show']);
Route::post('/categories', [CategoriesController::class, 'store']);
Route::put('/categories/{id}', [CategoriesController::class, 'update']);
Route::delete('/categories/{id}', [CategoriesController::class, 'destroy']);

Route::get('/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::get('/products/search', [ProductsController::class, 'search']);
Route::get('/products/filter-options', [ProductsController::class, 'getFilterOptions']);
Route::get('/products', [ProductsController::class, 'index']);
Route::post('/products', [ProductsController::class, 'store']);
Route::put('/products/{id}', [ProductsController::class, 'update']);
Route::get('/products/slug/{slug}', [ProductsController::class, 'getProductBySlug']);
Route::get('/products/{id}', [ProductsController::class, 'getProductById']);
Route::delete('/products/{id}', [ProductsController::class, 'destroy']);
Route::get('/products/{id}/favorite', [ProductsController::class, 'favorite']);

Route::get('/inventory', [InventoryController::class, 'index']);

Route::get('/variants', [VariantController::class, 'index']);

Route::get('/addresses', [AddressController::class, 'index']);
Route::post('/addresses', [AddressController::class, 'store']);
Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
Route::put('/addresses/{id}', [AddressController::class, 'update']);

Route::get('/guest-cart', [CartController::class, 'index']);
Route::post('/guest-cart', [CartController::class, 'store']);
Route::put('/guest-cart/{id}', [CartController::class, 'update']);
Route::delete('/guest-cart/{id}', [CartController::class, 'destroy']);

Route::get('/coupons', [CouponsController::class, 'index']);
Route::post('/coupons', [CouponsController::class, 'store']);
Route::get('/coupons/{id}', [CouponsController::class, 'show']);
Route::put('/coupons/{id}', [CouponsController::class, 'update']);
Route::delete('/coupons/{id}', [CouponsController::class, 'destroy']);
Route::post('/coupons/validate', [CouponsController::class, 'validate']);


Route::get('/product-reviews', [ProductReviewController::class, 'index']);
Route::post('/product-reviews', [ProductReviewController::class, 'store']);
Route::get('/product-reviews/{id}', [ProductReviewController::class, 'show']);
Route::put('/product-reviews/{id}', [ProductReviewController::class, 'update']);
Route::delete('/product-reviews/{id}', [ProductReviewController::class, 'destroy']);
Route::get('/product-reviews/product/{slug}', [ProductReviewController::class, 'getByProductSlug']);
Route::get('/product-reviews/check/{userId}/{productSlug}', [ProductReviewController::class, 'checkUserReview']);
Route::post('/product-reviews/{id}/admin-reply', [ProductReviewController::class, 'adminReply']);

