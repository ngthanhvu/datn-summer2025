import { createRouter, createWebHistory } from 'vue-router';
import DefaultLayout from '../layouts/DefaultLayout.vue';
import AdminLayout from '../layouts/AdminLayout.vue';

import Home from '../pages/index.vue';
import ProductsPage from '../pages/products.vue';
import ProducDetail from '../pages/detail.vue';
import LoginPage from '../pages/auth/login.vue';
import RegisterPage from '../pages/auth/register.vue';
import ProfilePage from '../pages/profile.vue';
import VoucherPage from '../pages/voucher.vue';
import AboutPage from '../pages/about.vue';
import ContactPage from '../pages/contacts.vue';
import CartPage from '../pages/cart.vue';
import OrderTracking from '../pages/order-tracking.vue';
import BlogPage from '../pages/blogs.vue';
import CheckOutPage from '../pages/checkout.vue';
import FavoriteProductPage from '../pages/favorite.vue';

import Admin from '../pages/admin/index.vue';
import ProductsPageAdmin from '../pages/admin/products/index.vue';
import ProductsCreate from '../pages/admin/products/create.vue';
import CategoriesPageAdmin from '../pages/admin/categories/index.vue';
import CategoriesCreate from '../pages/admin/categories/create.vue';
import BrandsPageAdmin from '../pages/admin/brands/index.vue';
import BrandsCreate from '../pages/admin/brands/create.vue';
import OrdersPageAdmin from '../pages/admin/orders/index.vue';
import CouponsPageAdmin from '../pages/admin/coupons/index.vue';
import CouponsCreate from '../pages/admin/coupons/create.vue';
import CustomerPageAdmin from '../pages/admin/customers/index.vue';
import InventoryPageAdmin from '../pages/admin/inventory/index.vue';
import InventoryImportPageAdmin from '../pages/admin/inventory/import.vue';
import InventoryHistoryPageAdmin from '../pages/admin/inventory/history.vue';
import BlogsPageAdmin from '../pages/admin/blogs/index.vue';
import BlogsCreate from '../pages/admin/blogs/create.vue';
import MessagePageAdmin from '../pages/admin/messages/index.vue';
import CommentPageAdmin from '../pages/admin/comments/index.vue';
import FlashSalePageAdmin from '../pages/admin/flashsale/index.vue';
import FlashSaleForm from '../pages/admin/flashsale/create.vue';
import FlashSaleSelect from '../pages/admin/flashsale/select.vue';

import NotFound from '../components/404.vue'
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
                path: '/san-pham',
                component: ProductsPage,
            },
            {
                path: '/san-pham/:slug',
                component: ProducDetail
            },
            {
                path: '/trang-ca-nhan',
                component: ProfilePage
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
                path: '/thanh-toan',
                component: CheckOutPage
            },
            {
                path: '/san-pham-yeu-thich',
                component: FavoriteProductPage
            },
            {
                path: '/:pathMatch(.*)*',
                component: NotFound
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
                path: '/admin/categories',
                component: CategoriesPageAdmin
            },
            {
                path: '/admin/categories/create',
                component: CategoriesCreate
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
                path: '/admin/orders',
                component: OrdersPageAdmin
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
                path: '/admin/flashsale/select',
                component: FlashSaleSelect
            }
        ]
        // meta: { requiresAuth: true, requiresAdmin: true }
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach(authGuard)

export default router
