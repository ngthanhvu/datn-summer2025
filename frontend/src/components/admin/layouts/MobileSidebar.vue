<template>
    <div class="mobile-sidebar-container" :class="{ 'mobile-sidebar-open': isOpen }">
        <!-- Mobile overlay -->
        <div class="mobile-overlay" @click="$emit('close')"></div>

        <!-- Mobile sidebar -->
        <aside class="mobile-sidebar">
            <div class="sidebar-logo">
                <span class="logo-text">Admin Panel</span>
                <button class="mobile-close-btn" @click="$emit('close')">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                <!-- Tổng quan -->
                <div class="nav-section">
                    <div class="section-title font-bold text-sm text-gray-500">Tổng quan</div>

                    <RouterLink to="/admin" v-slot="{ href, isActive, isExactActive }" @click="$emit('close')">
                        <a :href="href" class="nav-item" :class="{ 'router-link-exact-active': isExactActive }">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Bảng điều khiển</span>
                        </a>
                    </RouterLink>

                    <!-- Sản phẩm -->
                    <div class="nav-item dropdown-toggle mt-1" @click="showProductsMenu = !showProductsMenu">
                        <i class="fa-solid fa-tags"></i>
                        <span class="flex items-center justify-between w-full">
                            Sản phẩm
                            <i class="fas fa-caret-down transition-transform duration-300"
                                :class="{ 'rotate-180': showProductsMenu }"></i>
                        </span>
                    </div>
                    <div v-show="showProductsMenu" class="submenu">
                        <RouterLink to="/admin/products" class="nav-sub-item" @click="$emit('close')">Tất cả sản phẩm
                        </RouterLink>
                        <RouterLink to="/admin/categories" class="nav-sub-item" @click="$emit('close')">Danh mục
                        </RouterLink>
                        <RouterLink to="/admin/brands" class="nav-sub-item" @click="$emit('close')">Thương hiệu
                        </RouterLink>
                    </div>

                    <RouterLink to="/admin/orders" class="nav-item" @click="$emit('close')">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Đơn hàng</span>
                    </RouterLink>
                    <RouterLink to="/admin/customers" class="nav-item" @click="$emit('close')">
                        <i class="fas fa-users"></i>
                        <span>Khách hàng</span>
                    </RouterLink>
                    <RouterLink to="/admin/coupons" class="nav-item" @click="$emit('close')">
                        <i class="fa-solid fa-ticket"></i>
                        <span>Khuyến mãi</span>
                    </RouterLink>
                    <RouterLink to="/admin/flashsale" class="nav-item" @click="$emit('close')">
                        <i class="fa-solid fa-bolt"></i>
                        <span>Flash Sale</span>
                    </RouterLink>

                    <!-- Quản lý kho -->
                    <div class="nav-item dropdown-toggle" @click="showInventoryMenu = !showInventoryMenu">
                        <i class="fas fa-warehouse"></i>
                        <span class="flex items-center justify-between w-full">
                            Quản lý kho
                            <i class="fas fa-caret-down transition-transform duration-300"
                                :class="{ 'rotate-180': showInventoryMenu }"></i>
                        </span>
                    </div>
                    <div v-show="showInventoryMenu" class="submenu">
                        <RouterLink to="/admin/inventory" class="nav-sub-item" @click="$emit('close')">Tổng quan kho
                        </RouterLink>
                        <RouterLink to="/admin/inventory/import" class="nav-sub-item" @click="$emit('close')">Nhập hàng
                        </RouterLink>
                        <RouterLink to="/admin/inventory/history" class="nav-sub-item" @click="$emit('close')">Hoá đơn
                        </RouterLink>
                    </div>
                </div>

                <!-- Giao tiếp -->
                <div class="nav-section">
                    <div class="section-title font-bold text-sm text-gray-500">Giao tiếp</div>

                    <RouterLink to="/admin/blogs" class="nav-item" @click="$emit('close')">
                        <i class="fa-solid fa-newspaper"></i>
                        <span>Bài viết</span>
                    </RouterLink>
                    <RouterLink to="/admin/messages" class="nav-item" @click="$emit('close')">
                        <i class="fas fa-envelope"></i>
                        <span>Tin nhắn</span>
                        <span v-if="unreadMessages > 0" class="badge">{{ unreadMessages }}</span>
                    </RouterLink>
                    <RouterLink to="/admin/comments" class="nav-item" @click="$emit('close')">
                        <i class="fas fa-comments"></i>
                        <span>Đánh giá</span>
                        <span v-if="unapprovedReviews > 0" class="badge">{{ unapprovedReviews }}</span>
                    </RouterLink>
                    <RouterLink to="/admin/contacts" class="nav-item" @click="$emit('close')">
                        <i class="fas fa-address-book"></i>
                        <span>Liên hệ</span>
                        <span v-if="unreadContacts > 0" class="badge">{{ unreadContacts }}</span>
                    </RouterLink>
                    <RouterLink to="/admin/pages" class="nav-item" @click="$emit('close')">
                        <i class="fas fa-file-alt"></i>
                        <span>Quản lý trang</span>
                    </RouterLink>
                </div>

                <!-- Hệ thống -->
                <div class="nav-section">
                    <div class="section-title font-bold text-sm text-gray-500">Hệ thống</div>
                    <RouterLink to="/admin/settings" class="nav-item" @click="$emit('close')">
                        <i class="fas fa-cog"></i>
                        <span>Cài đặt</span>
                    </RouterLink>
                </div>
            </nav>
        </aside>
    </div>
</template>

<script setup>
import { ref } from 'vue'

// Define props and emits
defineProps({
    isOpen: {
        type: Boolean,
        default: false
    }
})

defineEmits(['close'])

const showProductsMenu = ref(false)
const showInventoryMenu = ref(false)
const unreadMessages = ref(0)
const unapprovedReviews = ref(0)
const unreadContacts = ref(0)

// Gắn dữ liệu thực tế sau nếu cần
unreadMessages.value = 3
unapprovedReviews.value = 2
</script>

<style scoped>
.mobile-sidebar-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 1000;
    pointer-events: none;
    transition: all 0.3s ease-in-out;
}

.mobile-sidebar-container.mobile-sidebar-open {
    pointer-events: all;
}

.mobile-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
}

.mobile-sidebar-container.mobile-sidebar-open .mobile-overlay {
    opacity: 1;
}

.mobile-sidebar {
    position: absolute;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    background: #fff;
    border-right: 1px solid #e5e7eb;
    padding: 1.5rem 1rem;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    overflow-x: hidden;
    transform: translateX(-100%);
    transition: transform 0.3s ease-in-out;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
}

.mobile-sidebar-container.mobile-sidebar-open .mobile-sidebar {
    transform: translateX(0);
}

.sidebar-logo {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 2rem;
    position: relative;
}

.logo-text {
    font-size: 1.5rem;
    font-weight: bold;
    color: #3bb77e;
    letter-spacing: 1px;
}

.mobile-close-btn {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: #6b7280;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 0.25rem;
    transition: color 0.2s;
}

.mobile-close-btn:hover {
    color: #3bb77e;
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.nav-section {
    margin-bottom: 1.5rem;
}

.section-title {
    margin-bottom: 0.5rem;
    padding-left: 0.5rem;
}

.submenu {
    padding-left: 2rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin-top: 0.25rem;
    position: relative;
}

.submenu::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 1px;
    background: #d1d5db;
}

.nav-sub-item {
    font-size: 0.9rem;
    color: #4b5563;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    transition: background 0.2s, color 0.2s;
    text-decoration: none;
    position: relative;
}

.nav-sub-item::before {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 50%;
    width: 1rem;
    height: 1px;
    background: #d1d5db;
    transform: translateY(-50%);
}

.nav-sub-item::after {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 50%;
    width: 1px;
    height: 0.5rem;
    background: #d1d5db;
    transform: translateY(-50%);
}

.nav-sub-item:hover,
.nav-sub-item.router-link-active {
    background: #e9f6ef;
    color: #3bb77e;
}

.nav-sub-item:hover::before,
.nav-sub-item.router-link-active::before {
    background: #3bb77e;
}

.nav-sub-item:hover::after,
.nav-sub-item.router-link-active::after {
    background: #3bb77e;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #253d4e;
    text-decoration: none;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: background 0.2s, color 0.2s;
    cursor: pointer;
}

.nav-item i {
    width: 20px;
    text-align: center;
}

.nav-item.router-link-active,
.nav-item:hover {
    background: #e9f6ef;
    color: #3bb77e;
}

.badge {
    background: #3bb77e;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.75rem;
    margin-left: auto;
}

.nav-item.router-link-exact-active {
    background: #e9f6ef;
    color: #3bb77e;
}

.dropdown-toggle {
    cursor: pointer;
}
</style>