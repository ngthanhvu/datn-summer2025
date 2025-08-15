<template>
    <div class="auth-menu">
        <div class="w-[250px] py-3">
            <div class="px-4 pb-2 mb-2 border-b border-gray-200">
                <h6 class="font-medium text-gray-900 mb-1">Xin chào!</h6>
                <p class="text-sm text-gray-600">
                    Chào mừng <strong>{{ user?.username }}</strong> đến với DEVGANG
                </p>
            </div>
            <div class="space-y-1">
                <router-link v-if="isAdmin" to="/admin"
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-[#81AACC]">
                    <i class="bi bi-gear-wide-connected mr-3"></i>
                    <span>Trang quản trị</span>
                </router-link>
                <router-link to="/trang-ca-nhan"
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-[#81AACC]">
                    <i class="bi bi-person-circle mr-3"></i>
                    <span>Trang cá nhân</span>
                </router-link>
                <router-link to="/kho-voucher"
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-[#81AACC]">
                    <i class="fa-solid fa-ticket mr-3"></i>
                    <span>Kho Voucher</span>
                </router-link>
                <button @click="handleLogout"
                    class="flex w-full items-center px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-[#81AACC] cursor-pointer">
                    <i class="bi bi-box-arrow-in-right mr-3"></i>
                    <span>Đăng xuất</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useAuth } from '../../composable/useAuth'
import { nextTick } from 'vue'

const router = useRouter()
const authStore = useAuthStore()
const { logout } = useAuth()

const user = authStore.user
const isAdmin = user && (user.role === 'admin' || user.role === 'master_admin');

const handleLogout = async () => {
    logout()
    authStore.clearUser()
    await nextTick()
    router.push('/login')
}
</script>

<style>
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
