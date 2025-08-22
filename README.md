# DEVGANG Store - Website Bán Hàng Thời Trang

![DEVGANG Store Preview](https://i.imgur.com/ZmCHmgn.png)

Dự án website thương mại điện tử bán hàng thời trang được xây dựng bằng Laravel (Backend API) và Vue 3 (Frontend). Hệ thống hỗ trợ đầy đủ các tính năng cơ bản của một trang web bán hàng.

## Công nghệ sử dụng

- **Backend**: Laravel 10.x
- **Frontend**: Vue 3 + Composition API, Pinia (State Management), Vue Router
- **Database**: MySQL
- **Authentication**: JWT (JSON Web Token)
- **UI Framework**: Tailwind CSS hoặc Vuetify (tuỳ bạn dùng cái nào)
- **Build Tool**: Vite

## Tính năng chính
### Khách hàng
- Đăng ký, đăng nhập, quên mật khẩu
- Duyệt sản phẩm theo danh mục
- Tìm kiếm, lọc, sắp xếp sản phẩm
- Giỏ hàng và thanh toán
- Theo dõi đơn hàng
- Đánh giá sản phẩm

### Quản trị viên
- Quản lý sản phẩm, danh mục
- Quản lý đơn hàng
- Quản lý người dùng
- Thống kê doanh thu

## Yêu cầu hệ thống

- PHP >= 8.1
- Composer
- Node.js >= 16.x
- MySQL >= 5.7
- NPM/Yarn

## Cài đặt dự án

### 1. Clone dự án

```bash
git clone https://github.com/ngthanhvu/datn-summer2025.git
cd fashion-store
```

### 2. Cài đặt backend (Laravel)

```bash
# Copy file env mẫu
cp .env.example .env

# Cài đặt dependencies
composer install

# Tạo key ứng dụng
php artisan key:generate

# Cấu hình JWT Secret Key
php artisan jwt:secret
```

# Cấu hình database trong file .env
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

```bash
# Chạy migrations
php artisan migrate
```

### 3. Cài đặt frontend (Vue 3)

```bash
cd frontend # Nếu bạn dùng cấu trúc tách biệt frontend/backend
npm install

# Hoặc nếu dùng cấu trúc tích hợp
npm install
```

### 4. Chạy ứng dụng

**Backend:**

```bash
php artisan serve && php artisan queue:work redis
```

**Frontend:**

```bash
npm run dev
```

Truy cập ứng dụng tại: http://localhost:5173

## Tài khoản thanh toán online ( test )
**VNPAY**
```bash
Ngân hàng: NCB
Số thẻ: 9704198526191432198
Tên chủ thẻ:NGUYEN VAN A
Ngày phát hành:07/15
Mật khẩu OTP:123456
```
**MOMO**
```bash
NGUYEN VAN A
9704 0000 0000 0018	
03/07	
OTP
```
## Cấu trúc thư mục (nếu cần)

```
datn-summer2025/
├── .gitignore
├── README.md
├── backend
│   ├── app
│   │   ├── Helpers
│   │   │   └── EnvHelper.php
│   │   ├── Http
│   │   │   └── Controllers
│   │   │       ├── AddressController.php
│   │   │       ├── AuthController.php
│   │   │       ├── BlogsController.php
│   │   │       ├── BrandsController.php
│   │   │       ├── CartController.php
│   │   │       ├── CategoriesController.php
│   │   │       ├── ContactController.php
│   │   │       ├── Controller.php
│   │   │       ├── CouponsController.php
│   │   │       ├── DashboardController.php
│   │   │       ├── FavoriteProductController.php
│   │   │       ├── FlashSaleController.php
│   │   │       ├── InventoryController.php
│   │   │       ├── MessengerController.php
│   │   │       ├── OrdersController.php
│   │   │       ├── PaymentController.php
│   │   │       ├── ProductImportController.php
│   │   │       ├── ProductReviewController.php
│   │   │       ├── ProductsController.php
│   │   │       ├── SettingController.php
│   │   │       ├── StockMovementController.php
│   │   │       └── VariantController.php
│   │   ├── Imports
│   │   │   └── ProductImport.php
│   │   ├── Mail
│   │   │   ├── ContactDeleted.php
│   │   │   ├── ContactReply.php
│   │   │   ├── OtpEmail.php
│   │   │   ├── PaymentConfirmation.php
│   │   │   ├── ReturnApproved.php
│   │   │   ├── ReturnRejected.php
│   │   │   └── WelcomeEmail.php
│   │   ├── Models
│   │   │   ├── Address.php
│   │   │   ├── Blogs.php
│   │   │   ├── Brands.php
│   │   │   ├── Cart.php
│   │   │   ├── Categories.php
│   │   │   ├── Contact.php
│   │   │   ├── Coupons.php
│   │   │   ├── FavoriteProduct.php
│   │   │   ├── FlashSale.php
│   │   │   ├── FlashSaleProduct.php
│   │   │   ├── Images.php
│   │   │   ├── Inventory.php
│   │   │   ├── Messenger.php
│   │   │   ├── Orders.php
│   │   │   ├── Orders_detail.php
│   │   │   ├── ProductReview.php
│   │   │   ├── Products.php
│   │   │   ├── ReviewImage.php
│   │   │   ├── Setting.php
│   │   │   ├── StockMovement.php
│   │   │   ├── StockMovementItem.php
│   │   │   ├── User.php
│   │   │   └── Variants.php
│   │   └── Providers
│   │       └── AppServiceProvider.php
│   ├── resources
│   │   └── views
│   │       ├── emails
│   │       │   ├── contact-deleted.blade.php
│   │       │   ├── contact-reply.blade.php
│   │       │   ├── otp.blade.php
│   │       │   ├── payment-confirmation.blade.php
│   │       │   ├── return-approved.blade.php
│   │       │   ├── return-rejected.blade.php
│   │       │   └── welcome.blade.php
│   │       ├── pdf
│   │       │   └── movement-invoice.blade.php
│   ├── routes
│   │   ├── api.php
└── frontend
    ├── .env.example
    ├── .gitignore
    ├── README.md
    ├── index.html
    ├── package-lock.json
    ├── package.json
    ├── src
    │   ├── App.vue
    │   ├── assets
    │   │   ├── product_sale.jpg
    │   │   └── vue.svg
    │   ├── components
    │   │   ├── 404.vue
    │   │   ├── CKEditor.vue
    │   │   ├── admin
    │   │   │   ├── blogs
    │   │   │   ├── brands
    │   │   │   ├── categories
    │   │   │   ├── comments
    │   │   │   ├── contacts
    │   │   │   ├── coupons
    │   │   │   ├── customers
    │   │   │   ├── dashboard
    │   │   │   ├── flashsale
    │   │   │   ├── inventory
    │   │   │   ├── layouts
    │   │   │   ├── messages
    │   │   │   ├── orders
    │   │   │   ├── products
    │   │   │   └── settings
    │   │   ├── auth
    │   │   ├── cart
    │   │   ├── checkout
    │   │   ├── common
    │   │   ├── home
    │   │   ├── products
    │   │   ├── profile
    │   │   ├── site
    │   │   ├── ui
    │   │   ├── vouchers
    │   │   └── wishlist
    │   ├── composable
    │   │   ├── useAddress.js
    │   │   ├── useAdminReviews.js
    │   │   ├── useAuth.js
    │   │   ├── useBlogs.js
    │   │   ├── useBrand.js
    │   │   ├── useCapcha.js
    │   │   ├── useCart.js
    │   │   ├── useCategories.js
    │   │   ├── useChat.js
    │   │   ├── useCheckout.js
    │   │   ├── useContact.js
    │   │   ├── useCoupon.js
    │   │   ├── useDashboard.js
    │   │   ├── useFlashsale.js
    │   │   ├── useInventorie.js
    │   │   ├── useOrder.js
    │   │   ├── usePayment.js
    │   │   ├── useProducts.js
    │   │   ├── useReview.js
    │   │   ├── useReviews.js
    │   │   └── useSettingsApi.js
    │   ├── layouts
    │   │   ├── AdminLayout.vue
    │   │   ├── ChatWidget.vue
    │   │   ├── DefaultLayout.vue
    │   │   ├── FooterHome.vue
    │   │   ├── HeaderHome.vue
    │   │   ├── MobileMenu.vue
    │   │   └── Topbar.vue
    │   ├── main.js
    │   ├── pages
    │   │   ├── about.vue
    │   │   ├── admin
    │   │   │   ├── blogs
    │   │   │   ├── brands
    │   │   │   ├── categories
    │   │   │   ├── comments
    │   │   │   ├── contacts
    │   │   │   ├── coupons
    │   │   │   ├── customers
    │   │   │   ├── flashsale
    │   │   │   ├── index.vue
    │   │   │   ├── inventory
    │   │   │   ├── messages
    │   │   │   ├── orders
    │   │   │   ├── products
    │   │   │   └── settings
    │   │   ├── auth
    │   │   │   ├── forgot.vue
    │   │   │   ├── login.vue
    │   │   │   ├── register.vue
    │   │   │   └── reset.vue
    │   │   ├── blogs.vue
    │   │   ├── blogs_detail.vue
    │   │   ├── cart.vue
    │   │   ├── checkout.vue
    │   │   ├── contacts.vue
    │   │   ├── detail.vue
    │   │   ├── favorite.vue
    │   │   ├── forgotPassword.vue
    │   │   ├── index.vue
    │   │   ├── order-tracking.vue
    │   │   ├── products.vue
    │   │   ├── profile.vue
    │   │   ├── resetPassword.vue
    │   │   ├── status.vue
    │   │   └── voucher.vue
    │   ├── router
    │   │   ├── index.js
    │   │   └── middleware
    │   │       └── auth.js
    │   ├── stores
    │   │   ├── auth.js
    │   │   ├── blogs.js
    │   │   ├── brands.js
    │   │   ├── cart.js
    │   │   ├── categories.js
    │   │   ├── coupons.js
    │   │   ├── orders.js
    │   │   ├── products.js
    │   │   ├── review.js
    │   │   └── wishlist.js
    │   └── style.css
    └── vite.config.js
```
## License
Dự án được phát triển bởi DEVGANG và được cấp phép theo MIT License.