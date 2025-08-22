<template>
    <aside class="sidebar">
        <div class="sidebar-logo">
            <!-- <img src="https://assets.market-storefront.envato-static.com/storefront/assets/favicons/favicon-683776860a73328fbfb90221f38228ae31fd55217f17b0fdba2daa1727c47dbd.ico"
                alt="Logo" /> -->
            <router-link to="/admin" class="flex items-center">
                <i class="fa-solid fa-chart-column text-[30px] font-bold text-[#3bb77e] mr-2"></i>
                <span class="text-[#3bb77e] font-bold text-lg">DEVGANG ADMIN</span>
            </router-link>
        </div>
        <nav class="sidebar-nav">
            <!-- Tổng quan -->
            <div class="nav-section">
                <div class="section-title font-bold text-sm text-gray-500">Tổng quan</div>

                <RouterLink to="/admin" v-slot="{ href, isActive, isExactActive }">
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
                    <RouterLink to="/admin/products" class="nav-sub-item">Tất cả sản phẩm</RouterLink>
                    <RouterLink to="/admin/categories" class="nav-sub-item">Danh mục</RouterLink>
                    <RouterLink to="/admin/brands" class="nav-sub-item">Thương hiệu</RouterLink>
                </div>

                <RouterLink to="/admin/orders" class="nav-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Đơn hàng</span>
                </RouterLink>
                <RouterLink to="/admin/customers" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Khách hàng</span>
                </RouterLink>
                <RouterLink to="/admin/coupons" class="nav-item">
                    <i class="fa-solid fa-ticket"></i>
                    <span>Khuyến mãi</span>
                </RouterLink>
                <RouterLink to="/admin/flashsale" class="nav-item">
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
                    <RouterLink to="/admin/inventory" class="nav-sub-item">Tổng quan kho</RouterLink>
                    <RouterLink to="/admin/inventory/import" class="nav-sub-item">Nhập hàng</RouterLink>
                    <RouterLink to="/admin/inventory/history" class="nav-sub-item">Hoá đơn</RouterLink>
                </div>
            </div>

            <!-- Giao tiếp -->
            <div class="nav-section">
                <div class="section-title font-bold text-sm text-gray-500">Giao tiếp</div>

                <RouterLink to="/admin/blogs" class="nav-item">
                    <i class="fa-solid fa-newspaper"></i>
                    <span>Bài viết</span>
                </RouterLink>
                <RouterLink to="/admin/messages" class="nav-item">
                    <i class="fas fa-envelope"></i>
                    <span>Tin nhắn</span>
                </RouterLink>
                <RouterLink to="/admin/comments" class="nav-item">
                    <i class="fas fa-comments"></i>
                    <span>Đánh giá</span>
                </RouterLink>
                <RouterLink to="/admin/contacts" class="nav-item">
                    <i class="fas fa-address-book"></i>
                    <span>Liên hệ</span>
                </RouterLink>
                <RouterLink to="/admin/pages" class="nav-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Quản lý trang</span>
                </RouterLink>
            </div>

            <!-- Hệ thống -->
            <div class="nav-section">
                <div class="section-title font-bold text-sm text-gray-500">Hệ thống</div>
                <RouterLink to="/admin/settings" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Cài đặt</span>
                </RouterLink>
                <RouterLink to="/admin/health" class="nav-item">
                    <i class="fas fa-heartbeat"></i>
                    <span>Trạng thái</span>
                </RouterLink>
            </div>
        </nav>
    </aside>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useContact } from '../../../composable/useContact'

const showProductsMenu = ref(false)
const showInventoryMenu = ref(false)

const { getContactStats } = useContact()
const contactStats = ref({
    total: 0,
    replied: 0,
    unreplied: 0
})

const loadContactStats = async () => {
    try {
        const stats = await getContactStats()
        contactStats.value = stats
    } catch (error) {
        console.error('Error loading contact stats:', error)
        contactStats.value = {
            total: 0,
            replied: 0,
            unreplied: 0
        }
    }
}

let statsInterval = null

onMounted(() => {
    loadContactStats()

    statsInterval = setInterval(() => {
        loadContactStats()
    }, 30000)
})

onUnmounted(() => {
    if (statsInterval) {
        clearInterval(statsInterval)
    }
})
</script>

<style scoped>
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

.sidebar {
    width: 250px;
    background: #fff;
    border-right: 1px solid #e5e7eb;
    padding: 1.5rem 1rem;
    display: flex;
    flex-direction: column;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 50;
    transition: transform 0.3s ease-in-out;
    /* box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1); */
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 2rem;
    position: relative;
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
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
    margin-top: calc(var(--spacing) * 1)
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
    min-width: 18px;
    text-align: center;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 18px;
}

.nav-item.router-link-exact-active {
    background: #e9f6ef;
    color: #3bb77e;
}
</style>