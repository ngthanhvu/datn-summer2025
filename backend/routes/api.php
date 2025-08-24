<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\Products;
use App\Models\Blogs;
use App\Models\Pages;

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
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MessengerController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\FlashSaleController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\AIChatController;
use App\Http\Controllers\BlogCategoryController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\CouponController;

// AI Chatbot routes
Route::post('/ai/chat', [AIChatController::class, 'chat']);
Route::get('/ai/search-products', [AIChatController::class, 'searchProducts']);
Route::post('/ai/search-by-price', [AIChatController::class, 'searchProductsByPrice']);
Route::get('/ai/product-variants', [AIChatController::class, 'getProductVariants']);
Route::get('/ai/coupons', [AIChatController::class, 'getAvailableCoupons']);
Route::get('/ai/flash-sales', [AIChatController::class, 'getActiveFlashSales']);

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
    Route::post('/admin/user', [AuthController::class, 'createUserByAdmin']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
    Route::delete('/admin/user/{id}', [AuthController::class, 'destroy']);
    Route::delete('/admin/user/{id}/simple', [AuthController::class, 'simpleDelete']); // Simple delete for testing
    Route::get('/admin/user/{id}/test', [AuthController::class, 'testDelete']); // Test route
    Route::post('/reset-password-profile', [AuthController::class, 'resetPasswordProfile']);

    Route::get('/inventory', [InventoryController::class, 'index']);

    Route::post('/blogs', [BlogsController::class, 'store']);
    Route::put('/blogs/{id}', [BlogsController::class, 'update']);
    Route::delete('/blogs/{id}', [BlogsController::class, 'destroy']);
    Route::get('/blogs/{id}', [BlogsController::class, 'show']);

    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::post('/cart/transfer-session-to-user', [CartController::class, 'transferCartFromSessionToUser']);
    Route::post('/cart/cleanup-old-items', [CartController::class, 'cleanupOldCartItems']);

    Route::get('/orders', [OrdersController::class, 'index']);
    Route::get('/user/orders', [OrdersController::class, 'userOrders']);
    Route::get('/orders/{id}', [OrdersController::class, 'show']);
    Route::post('/orders', [OrdersController::class, 'store']);
    Route::get('/orders/track/{tracking_code}', [OrdersController::class, 'getOrderByTrackingCode']);
    Route::post('/orders/{id}/return', [OrdersController::class, 'requestReturn']);
    Route::put('/orders/{id}/status', [OrdersController::class, 'updateStatus']);
    Route::post('/orders/{id}/return/approve', [OrdersController::class, 'approveReturn']);
    Route::post('/orders/{id}/return/reject', [OrdersController::class, 'rejectReturn']);
    Route::post('/orders/{id}/cancel', [OrdersController::class, 'cancel']);
    Route::post('/orders/{id}/reorder', [OrdersController::class, 'reorder']);
    Route::post('/orders/clear-cart-after-payment', [OrdersController::class, 'clearCartAfterPayment']);

    Route::get('/me/address', [AddressController::class, 'getMyAddress']);
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);

    Route::get('/favorites', [FavoriteProductController::class, 'index']);
    Route::post('/favorites', [FavoriteProductController::class, 'store']);
    Route::get('/favorites/check/{slug}', [FavoriteProductController::class, 'check']);
    Route::delete('/favorites/{product_slug}', [FavoriteProductController::class, 'destroy']);
    Route::post('/settings', [SettingController::class, 'update']);

    // Pages routes (Admin only)
    Route::get('/pages', [PagesController::class, 'index']);
    Route::post('/pages', [PagesController::class, 'store']);
    Route::get('/pages/{id}', [PagesController::class, 'show']);
    Route::put('/pages/{id}', [PagesController::class, 'update']);
    Route::delete('/pages/{id}', [PagesController::class, 'destroy']);
    Route::put('/pages/{id}/status', [PagesController::class, 'updateStatus']);

    // Chat/Messenger routes
    Route::prefix('chat')->group(function () {
        Route::get('/conversations', [MessengerController::class, 'getConversations']);
        Route::get('/messages/{userId}', [MessengerController::class, 'getMessages']);
        Route::post('/send', [MessengerController::class, 'sendMessage']);
        Route::put('/read/{messageId}', [MessengerController::class, 'markAsRead']);
        Route::get('/unread-count', [MessengerController::class, 'getUnreadCount']);
        Route::get('/search-users', [MessengerController::class, 'searchUsers']);
        Route::delete('/message/{messageId}', [MessengerController::class, 'deleteMessage']);
        Route::get('/admins', [MessengerController::class, 'getAdmins']);
    });

    Route::post('/coupons/{id}/claim', [CouponsController::class, 'claim']);
    Route::post('/coupons/{id}/use', [CouponsController::class, 'use']);
    Route::get('/coupons/my-coupons', [CouponsController::class, 'myCoupons']);

    //update infomation by admin
    Route::put('/admin/user/{id}', [AuthController::class, 'updateUserByAdmin']);

    Route::get('/notifications', function (Request $request) {
        return $request->user()->notifications()->orderBy('created_at', 'desc')->take(20)->get();
    });
});
Route::get('/settings', [SettingController::class, 'index']);

// Public blog routes
Route::get('/blogs', [BlogsController::class, 'index']);
Route::get('/blogs/slug/{slug}', [BlogsController::class, 'showBySlug']);
Route::get('/inventory', [InventoryController::class, 'index']);

// Public order tracking route
Route::get('/orders/track/{tracking_code}', [OrdersController::class, 'getOrderByTrackingCode']);

// Brand routes
Route::get('/brands', [BrandsController::class, 'index']);
Route::get('/brands/{id}', [BrandsController::class, 'show']);
Route::post('/brands', [BrandsController::class, 'store']);
Route::put('/brands/{id}', [BrandsController::class, 'update']);
Route::patch('/brands/{id}/status', [BrandsController::class, 'updateStatus']);
Route::delete('/brands/{id}', [BrandsController::class, 'destroy']);
Route::post('/brands/bulk-delete', [BrandsController::class, 'bulkDestroy']);

// Category routes
Route::get('/categories', [CategoriesController::class, 'index']);
Route::get('/categories/{id}', [CategoriesController::class, 'show']);
Route::post('/categories', [CategoriesController::class, 'store']);
Route::put('/categories/{id}', [CategoriesController::class, 'update']);
Route::patch('/categories/{id}/status', [CategoriesController::class, 'updateStatus']);
Route::delete('/categories/{id}', [CategoriesController::class, 'destroy']);
Route::post('/categories/bulk-delete', [CategoriesController::class, 'bulkDestroy']);

// Google login
Route::get('/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Product routes
Route::get('/products/search', [ProductsController::class, 'search']);
Route::get('/products/filter-options', [ProductsController::class, 'getFilterOptions']);
Route::get('/products', [ProductsController::class, 'index']);
Route::get('/products/bestsellers', [ProductsController::class, 'bestsellers']);
Route::post('/products', [ProductsController::class, 'store']);
Route::put('/products/{id}', [ProductsController::class, 'update']);
Route::get('/products/recommend', [ProductsController::class, 'recommend']);
Route::get('/products/slug/{slug}', [ProductsController::class, 'getProductBySlug']);
Route::get('/products/{id}', [ProductsController::class, 'getProductById']);
Route::delete('/products/{id}', [ProductsController::class, 'destroy']);
Route::delete('/products/delete/bulk-delete', [ProductsController::class, 'bulkDestroy']);
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
Route::get('/reviews/latest', [ProductReviewController::class, 'latestReviews']);
Route::get('/products-reviewed', [ProductReviewController::class, 'getReviewedProducts']);

// Payment routes
Route::prefix('payment')->group(function () {
    Route::post('vnpay', [PaymentController::class, 'generateVnpayUrl']);
    Route::post('momo', [PaymentController::class, 'generateMomoUrl']);

    Route::get('vnpay-callback', [PaymentController::class, 'vnpayCallback']);
    Route::get('momo-callback', [PaymentController::class, 'momoCallback']);
});

// Product import routes
Route::prefix('products')->group(function () {
    Route::get('import', [ProductImportController::class, 'index']);
    Route::post('import', [ProductImportController::class, 'import']);
    Route::get('import/template', [ProductImportController::class, 'downloadTemplate']);
    Route::get('import/history', [ProductImportController::class, 'getImportHistory']);
});

// Dashboard routes
Route::prefix('dashboard')->group(function () {
    Route::get('/stats', [DashboardController::class, 'getStats']);
    Route::get('/revenue', [DashboardController::class, 'getMonthlyRevenue']);
    Route::get('/revenue/yearly', [DashboardController::class, 'getYearlyRevenue']);
    Route::get('/orders', [DashboardController::class, 'getMonthlyOrders']);
    Route::get('/orders/status', [DashboardController::class, 'getOrdersByStatus']);
    Route::get('/customers', [DashboardController::class, 'getCustomersStats']);
    Route::get('/products', [DashboardController::class, 'getProductsStats']);
    Route::get('/recent-orders', [DashboardController::class, 'getRecentOrders']);
    Route::get('/top-selling', [DashboardController::class, 'getTopSelling']);
    Route::get('/inventory', [DashboardController::class, 'getInventoryStats']);
    Route::get('/user-growth', [DashboardController::class, 'getUserGrowthStats']);
});
Route::get('/dashboard/revenue-by-date-range', [DashboardController::class, 'getRevenueByDateRange']);

Route::get('/stock-movement', [StockMovementController::class, 'index']);
Route::get('/stock-movement/{id}', [StockMovementController::class, 'show']);
Route::post('/stock-movement', [StockMovementController::class, 'store']);
Route::delete('/stock-movement/{id}', [StockMovementController::class, 'destroy']);

Route::get('flash-sales', [FlashSaleController::class, 'index']);
Route::post('flash-sales', [FlashSaleController::class, 'store']);
// Specific routes must come before parameterized routes to avoid conflicts
Route::get('flash-sales/statistics', [FlashSaleController::class, 'statistics']);
Route::get('flash-sales/active', [FlashSaleController::class, 'getActiveFlashSales']);
Route::post('flash-sales/process-repeat', [FlashSaleController::class, 'processRepeat']);
Route::get('flash-sales/{id}/statistics', [FlashSaleController::class, 'statistics'])->whereNumber('id');
Route::patch('flash-sales/{id}/status', [FlashSaleController::class, 'updateStatus'])->whereNumber('id');
Route::put('flash-sales/{id}', [FlashSaleController::class, 'update'])->whereNumber('id');
Route::delete('flash-sales/{id}', [FlashSaleController::class, 'destroy'])->whereNumber('id');
Route::get('flash-sales/{id}', [FlashSaleController::class, 'show'])->whereNumber('id');

// Contact
Route::get('/contacts', [ContactController::class, 'index']);
Route::get('/contacts/stats', [ContactController::class, 'stats']);
Route::get('/contacts/{id}', [ContactController::class, 'show']);
Route::post('/contacts', [ContactController::class, 'store']);
Route::post('/contacts/{id}/reply', [ContactController::class, 'reply']);
Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);

// Shipping routes
Route::prefix('shipping')->group(function () {
    Route::get('/config', [ShippingController::class, 'getConfig']);
    Route::get('/provinces', [ShippingController::class, 'getProvinces']);
    Route::get('/districts', [ShippingController::class, 'getDistricts']);
    Route::get('/wards', [ShippingController::class, 'getWards']);
    Route::post('/calculate-fee', [ShippingController::class, 'calculateShippingFee']);
});

// Public pages routes
Route::get('/pages/type/{type}', [PagesController::class, 'getByType']);
Route::get('/pages/slug/{slug}', [PagesController::class, 'getBySlug']);

Route::apiResource('blog-categories', BlogCategoryController::class);

Route::get('/sitemap-data', function () {
    return response()->json([
        'products' => Products::where('is_active', 1)->pluck('slug'),
        'blogs'    => Blogs::where('status', 'published')->pluck('slug'),
        'pages'    => Pages::where('status', 1)->pluck('slug'),
    ]);
});

// Health check
Route::get('/health', [HealthCheckController::class, 'index']);
