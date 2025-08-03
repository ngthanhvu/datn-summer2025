<template>
    <div class="admin-layout">
        <!-- Sidebar -->
        <SidebarAdmin />

        <!-- Main Content -->
        <div class="main-content">
            <header class="header">
                <div class="header-left">
                    <button class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="search-bar">
                        <input type="text" placeholder="Search term" />
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                <div class="header-right">
                    <div class="flex items-center space-x-2">
                        <RouterLink to="/" class="p-2 hover:bg-gray-100 rounded-lg" title="Cài đặt">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </RouterLink>
                        <RouterLink to="/" class="p-2 hover:bg-gray-100 rounded-lg text-red-600" title="Đăng xuất">
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
</script>

<style scoped>
.main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    margin-left: 250px;
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

.header-left {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.search-bar {
    position: relative;
    width: 300px;
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

.screen {
    min-height: calc(100vh - 64px);
}
</style>
