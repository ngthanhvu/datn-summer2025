<template>
    <div class="admin-layout">
        <!-- Desktop Sidebar - hidden on mobile -->
        <SidebarAdmin class="desktop-sidebar" />

        <!-- Mobile Sidebar -->
        <MobileSidebar :isOpen="isMobileOpen" @close="closeSidebar" />

        <!-- Main Content -->
        <div class="main-content">
            <header class="header">
                <div class="header-left">
                    <button class="menu-toggle" @click="toggleSidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="search-bar">
                        <input type="text" placeholder="Search term" />
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                <div class="header-right">
                    <div class="flex items-center space-x-2">
                        <div class="relative">
                            <NotificationDropdown :notifications="notifications" />
                            <div v-if="loading" class="absolute -top-1 -right-1">
                                <div
                                    class="w-3 h-3 border-2 border-blue-500 border-t-transparent rounded-full animate-spin">
                                </div>
                            </div>
                        </div>
                        <RouterLink to="/admin/settings" class="p-2 hover:bg-gray-100 rounded-lg" title="Cài đặt">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </RouterLink>
                        <RouterLink to="/" class="p-2 hover:bg-gray-100 rounded-lg text-red-600"
                            title="Trở về trang chủ">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                        </RouterLink>
                    </div>
                </div>
            </header>
            <main class="content bg-[#F5F7FB] screen">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup>
import SidebarAdmin from '../components/admin/layouts/Sidebar.vue'
import MobileSidebar from '../components/admin/layouts/MobileSidebar.vue'
import NotificationDropdown from '../components/common/NotificationDropdown.vue'
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { useNotification } from '../composable/useNotification'

const { notifications, fetchNotifications, loading } = useNotification()
let intervalId = null

const isMobileOpen = ref(false)

const toggleSidebar = () => {
    isMobileOpen.value = !isMobileOpen.value
}

const closeSidebar = () => {
    isMobileOpen.value = false
}

onMounted(() => {
    fetchNotifications()
    intervalId = setInterval(() => {
        fetchNotifications()
    }, 5000)
})

onUnmounted(() => {
    clearInterval(intervalId)
})
</script>

<style scoped>
/* Hide desktop sidebar on mobile */
@media (max-width: 768px) {
    .desktop-sidebar {
        display: none;
    }
}

.main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    margin-left: 250px;
    transition: margin-left 0.3s ease-in-out;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
    }
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 2rem;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    position: sticky;
    top: 0;
    z-index: 10;
    height: 64px;
}

@media (max-width: 768px) {
    .header {
        padding: 0.75rem 1rem;
    }
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .header-left {
        gap: 1rem;
    }
}

.menu-toggle {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: #6b7280;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.375rem;
    transition: background-color 0.2s;
    display: none;
}

@media (max-width: 768px) {
    .menu-toggle {
        display: block;
    }
}

.menu-toggle:hover {
    background-color: #f3f4f6;
}

.search-bar {
    position: relative;
    width: 300px;
}

@media (max-width: 768px) {
    .search-bar {
        width: 200px;
    }
}

@media (max-width: 480px) {
    .search-bar {
        width: 150px;
    }
}

.search-bar input {
    width: 100%;
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #f7f8fa;
    font-size: 0.875rem;
    height: 40px;
}

.search-bar i {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #b6b6b6;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

@media (max-width: 480px) {
    .header-right {
        gap: 0.5rem;
    }
}

.screen {
    min-height: calc(100vh - 64px);
}

.notification-dropdown {
    position: absolute;
    right: 0;
    top: 120%;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    border-radius: 8px;
    min-width: 250px;
    z-index: 100;
    animation: fadeIn 0.2s;
}

.notification-item {
    padding: 1rem;
    border-bottom: 1px solid #eee;
    font-size: 0.95rem;
}

.notification-item:last-child {
    border-bottom: none;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
