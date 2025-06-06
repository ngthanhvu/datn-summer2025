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

    Route::post('/inventory/update', [InventoryController::class, 'updateStock']);
    Route::get('/inventory/movements', [InventoryController::class, 'getMovements']);
});

// Đảm bảo các route addresses nằm ngoài middleware('auth:api') nếu bạn muốn test không cần đăng nhập.
// Nếu muốn bảo vệ bằng token, chỉ để 1 nhóm middleware('auth:api') duy nhất cho addresses.
// Không được lặp lại hoặc khai báo trùng các route addresses.


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

// Variant routes
Route::get('/variants', [VariantController::class, 'index']);

// Đưa các route addresses RA NGOÀI middleware('auth:api')
Route::get('/addresses', [AddressController::class, 'index']);
Route::post('/addresses', [AddressController::class, 'store']);
Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
Route::put('/addresses/{id}', [AddressController::class, 'update']);
