<template>
    <div class="auth-menu">
        <div class="tw-w-[250px] tw-py-3">
            <div class="tw-px-4 tw-pb-2 tw-mb-2 tw-border-b">
                <h6 class="tw-font-medium tw-text-gray-900 tw-mb-1">Xin chào!</h6>
                <p class="tw-text-sm tw-text-gray-600">Chào mừng <strong>{{ user?.username }}</strong> đến với DEVGANG
                </p>
            </div>
            <div class="tw-space-y-1">
                <NuxtLink v-if="isAdmin" to="/admin"
                    class="tw-flex tw-items-center tw-px-4 tw-py-2 tw-text-gray-700 hover:tw-bg-gray-50 hover:tw-text-[#81AACC]">
                    <i class="bi bi-gear-wide-connected tw-mr-3"></i>
                    <span>Trang quản trị</span>
                </NuxtLink>
                <a href="/profile"
                    class="tw-flex tw-items-center tw-px-4 tw-py-2 tw-text-gray-700 hover:tw-bg-gray-50 hover:tw-text-[#81AACC]">
                    <i class="bi bi-person-circle tw-mr-3"></i>
                    <span>Trang cá nhân</span>
                </a>
                <a href="/history"
                    class="tw-flex tw-items-center tw-px-4 tw-py-2 tw-text-gray-700 hover:tw-bg-gray-50 hover:tw-text-[#81AACC]">
                    <i class="bi bi-clock-history tw-mr-3"></i>
                    <span>Lịch sử đơn hàng</span>
                </a>
                <button @click="handleLogout"
                    class="tw-flex tw-w-full tw-items-center tw-px-4 tw-py-2 tw-text-gray-700 hover:tw-bg-gray-50 hover:tw-text-[#81AACC]">
                    <i class="bi bi-box-arrow-in-right tw-mr-3"></i>
                    <span>Đăng xuất</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
interface User {
    username: string;
    role: string;
    [key: string]: any;
}

const user = useCookie<User>('user')
import { useAuth } from '~/composables/useAuth'
const { logout, isAdmin } = useAuth()

const handleLogout = () => {
    logout()
    navigateTo('/login')
}
</script>

<style scoped>
.auth-menu {
    visibility: hidden;
    opacity: 0;
    position: absolute;
    right: 0;
    top: 100%;
    margin-top: 10px;
    background-color: white;
    min-width: 250px;
    border-radius: 0.5rem;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
    z-index: 1000;
    transition: all 0.3s ease;
    transform: translateY(10px);
}

.auth-menu::before {
    content: '';
    position: absolute;
    top: -10px;
    left: 0;
    right: 0;
    height: 10px;
}

.auth-menu::after {
    content: '';
    position: absolute;
    top: -8px;
    right: 20px;
    width: 16px;
    height: 16px;
    background-color: white;
    transform: rotate(45deg);
    box-shadow: -2px -2px 5px rgba(0, 0, 0, 0.04);
}

.auth-menu a {
    transition: all 0.2s ease;
}

.auth-menu a:hover i {
    transform: translateX(2px);
}
</style>