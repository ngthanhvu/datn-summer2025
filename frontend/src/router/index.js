import { createRouter, createWebHistory } from 'vue-router';
import DefaultLayout from '../layouts/DefaultLayout.vue';
import AdminLayout from '../layouts/AdminLayout.vue';

import Home from '../pages/index.vue';
import ProductsPage from '../pages/products.vue';
import ProducDetail from '../pages/detail.vue';
import LoginPage from '../pages/auth/login.vue';
import RegisterPage from '../pages/auth/register.vue';
import GoogleCallback from '../pages/auth/googleCallback.vue';
import ForgotPasswordPage from '../pages/forgotPassword.vue'
import ResetPasswordPage from '../pages/resetPassword.vue'
import ProfilePage from '../pages/profile.vue';
import VoucherPage from '../pages/voucher.vue';
import AboutPage from '../pages/about.vue';
import ContactPage from '../pages/contacts.vue';
import CartPage from '../pages/cart.vue';
import OrderTracking from '../pages/order-tracking.vue';
import BlogPage from '../pages/blogs.vue';
import BlogDetailPage from '../pages/blogs_detail.vue';
import CheckOutPage from '../pages/checkout.vue';
import FavoriteProductPage from '../pages/favorite.vue';
import ShippingCalculatorPage from '../pages/shipping-calculator.vue';
import FlashSalePage from '../pages/flash-sale.vue';
import PageView from '../pages/page.vue';
import CategoryPage from '../pages/category.vue';

import Admin from '../pages/admin/index.vue';
import ProductsPageAdmin from '../pages/admin/products/index.vue';
import ProductsCreate from '../pages/admin/products/create.vue';
import ProductsEdit from '../pages/admin/products/edit.vue';
import CategoriesPageAdmin from '../pages/admin/categories/index.vue';
import CategoriesCreate from '../pages/admin/categories/create.vue';
import CategoriesEdit from '../pages/admin/categories/edit.vue';
import BrandsPageAdmin from '../pages/admin/brands/index.vue';
import BrandsCreate from '../pages/admin/brands/create.vue';
import BrandsEdit from '../pages/admin/brands/edit.vue';
import OrdersPageAdmin from '../pages/admin/orders/index.vue';
import OrderDetailPageAdmin from '../pages/admin/orders/detail.vue';
import CouponsPageAdmin from '../pages/admin/coupons/index.vue';
import CouponsCreate from '../pages/admin/coupons/create.vue';
import CouponsEdit from '../pages/admin/coupons/edit.vue';
import CustomerPageAdmin from '../pages/admin/customers/index.vue';
import InventoryPageAdmin from '../pages/admin/inventory/index.vue';
import InventoryImportPageAdmin from '../pages/admin/inventory/import.vue';
import InventoryHistoryPageAdmin from '../pages/admin/inventory/history.vue';
import BlogsPageAdmin from '../pages/admin/blogs/index.vue';
import BlogsCreate from '../pages/admin/blogs/create.vue';
import BlogsEdit from '../pages/admin/blogs/edit.vue';
import MessagePageAdmin from '../pages/admin/messages/index.vue';
import CommentPageAdmin from '../pages/admin/comments/index.vue';
import FlashSalePageAdmin from '../pages/admin/flashsale/index.vue';
import FlashSaleForm from '../pages/admin/flashsale/create.vue';
import FlashSaleEdit from '../pages/admin/flashsale/edit.vue';
import FlashSaleSelect from '../pages/admin/flashsale/select.vue';
import SettingPageAdmin from '../pages/admin/settings/index.vue';
import ContactsPageAdmin from '../pages/admin/contacts/index.vue';
import PagesPageAdmin from '../pages/admin/page/index.vue';
import PagesCreate from '../pages/admin/page/create.vue';
import PagesEdit from '../pages/admin/page/edit.vue';

import StatusPage from '../pages/status.vue';
import CookieRule from '../components/CookieRule.vue';
import NotFound from '../components/404.vue';
import PagesContent from '../components/admin/newpage/PagesContent.vue';
import HealthPage from '../pages/admin/health/index.vue';
import { authGuard } from './middleware/auth';

const routes = [
    {
        path: '/',
        component: DefaultLayout,
        children: [
            {
                path: '',
                component: Home
            },
            {
                path: '/login',
                component: LoginPage,
                meta: { guestOnly: true }
            },
            {
                path: '/register',
                component: RegisterPage,
                meta: { guestOnly: true }
            },
            {
                path: '/auth/google/callback',
                component: GoogleCallback,
                meta: { guestOnly: true }
            },
            {
                path: '/san-pham',
                component: ProductsPage,
            },
            {
                path: '/danh-muc/:slug',
                component: CategoryPage,
            },
            {
                path: '/san-pham/:slug',
                component: ProducDetail
            },
            {
                path: '/trang-ca-nhan',
                component: ProfilePage,
                meta: { requiresAuth: true }
            },
            {
                path: '/kho-voucher',
                component: VoucherPage
            },
            {
                path: '/gioi-thieu',
                component: AboutPage
            },
            {
                path: '/lien-he',
                component: ContactPage
            },
            {
                path: '/gio-hang',
                component: CartPage
            },
            {
                path: '/tra-cuu-don-hang',
                component: OrderTracking
            },
            {
                path: '/tin-tuc',
                component: BlogPage
            },
            {
                path: '/tin-tuc/:slug',
                component: BlogDetailPage
            },
            {
                path: '/thanh-toan',
                component: CheckOutPage
            },
            {
                path: '/san-pham-yeu-thich',
                component: FavoriteProductPage
            },
            {
                path: '/tinh-phi-van-chuyen',
                component: ShippingCalculatorPage
            },
            {
                path: '/flash-sale',
                component: FlashSalePage
            },
            {
                path: '/trang/:slug',
                component: PageView
            },
            {
                path: '/:pathMatch(.*)*',
                component: NotFound
            },
            {
                path: '/status',
                component: StatusPage
            },
            {
                path: '/forgot-password',
                component: ForgotPasswordPage
            },
            {
                path: '/reset',
                component: ResetPasswordPage
            },
            {
                path: '/test-pages',
                component: PagesContent
            },
            {
                path: '/chinh-sach-cookie',
                component: CookieRule
            }

        ]
    },
    {
        path: '/admin',
        component: AdminLayout,
        meta: { requiresAuth: true, requiresAdmin: true },
        children: [
            {
                path: '',
                component: Admin
            },
            {
                path: '/admin/products',
                component: ProductsPageAdmin
            },
            {
                path: '/admin/products/create',
                component: ProductsCreate
            },
            {
                path: '/admin/products/edit/:id',
                component: ProductsEdit
            },
            {
                path: '/admin/categories',
                component: CategoriesPageAdmin
            },
            {
                path: '/admin/categories/create',
                component: CategoriesCreate
            },
            {
                path: '/admin/categories/edit/:id',
                component: CategoriesEdit
            },
            {
                path: '/admin/brands',
                component: BrandsPageAdmin
            },
            {
                path: '/admin/brands/create',
                component: BrandsCreate
            },
            {
                path: '/admin/brands/edit/:id',
                component: BrandsEdit
            },
            {
                path: '/admin/orders',
                component: OrdersPageAdmin
            },
            {
                path: '/admin/orders/:id',
                component: OrderDetailPageAdmin
            },
            {
                path: '/admin/coupons',
                component: CouponsPageAdmin
            },
            {
                path: '/admin/coupons/create',
                component: CouponsCreate
            },
            {
                path: '/admin/coupons/edit/:id',
                component: CouponsEdit
            },
            {
                path: '/admin/customers',
                component: CustomerPageAdmin
            },
            {
                path: '/admin/inventory',
                component: InventoryPageAdmin
            },
            {
                path: '/admin/inventory/import',
                component: InventoryImportPageAdmin
            },
            {
                path: '/admin/inventory/history',
                component: InventoryHistoryPageAdmin
            },
            {
                path: '/admin/blogs',
                component: BlogsPageAdmin
            },
            {
                path: '/admin/blogs/create',
                component: BlogsCreate
            },
            {
                path: '/admin/blogs/edit/:id',
                component: BlogsEdit
            },
            {
                path: '/admin/messages',
                component: MessagePageAdmin
            },
            {
                path: '/admin/comments',
                component: CommentPageAdmin
            },
            {
                path: '/admin/flashsale',
                component: FlashSalePageAdmin
            },
            {
                path: '/admin/flashsale/create',
                component: FlashSaleForm
            },
            {
                path: '/admin/flashsale/:id/edit',
                component: FlashSaleEdit
            },
            {
                path: '/admin/flashsale/select',
                component: FlashSaleSelect
            },
            {
                path: '/admin/settings',
                component: SettingPageAdmin
            },
            {
                path: '/admin/contacts',
                component: ContactsPageAdmin
            },
            {
                path: '/admin/pages',
                component: PagesPageAdmin
            },
            {
                path: '/admin/pages/create',
                component: PagesCreate
            },
            {
                path: '/admin/pages/:id/edit',
                component: PagesEdit
            },
            {
                path: '/admin/health',
                component: HealthPage
            }
        ]
        // meta: { requiresAuth: true, requiresAdmin: true }
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        return { top: 0 }
    }
})

router.beforeEach(authGuard)

export default router
