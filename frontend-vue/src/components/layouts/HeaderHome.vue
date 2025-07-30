<template>
    <TopBar />
    <nav class="sticky top-0 z-50 bg-white shadow-sm py-1">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
            <!-- Logo -->
            <router-link to="/" class="flex items-center">
                <img src="/logo.png" alt="EGA MEN" class="w-20" />
            </router-link>

            <!-- Desktop Navigation -->
            <ul class="hidden lg:flex gap-6 text-md font-semibold text-gray-700">
                <li>
                    <router-link to="/" class="hover:text-[#81aacc]">Trang chủ</router-link>
                </li>
                <li class="relative group">
                    <router-link to="/san-pham" class="hover:text-[#81aacc]">Sản phẩm</router-link>
                    <div
                        class="absolute left-1/2 -translate-x-1/2 mt-2 invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-all">
                        <!-- <MegaMenu /> -->
                    </div>
                </li>
                <li>
                    <router-link to="/gioi-thieu" class="hover:text-[#81aacc]">Giới thiệu</router-link>
                </li>
                <li>
                    <router-link to="/tin-tuc" class="hover:text-[#81aacc]">Tin tức</router-link>
                </li>
                <li>
                    <router-link to="/lien-he" class="hover:text-[#81aacc]">Liên hệ</router-link>
                </li>
                <li>
                    <router-link to="/tra-cuu-don-hang" class="hover:text-[#81aacc]">Kiểm tra đơn
                        hàng</router-link>
                </li>
            </ul>

            <!-- Mobile Menu Button -->
            <button class="lg:hidden text-2xl text-gray-700">
                <i class="bi bi-list"></i>
            </button>

            <!-- Desktop Icons -->
            <div class="hidden lg:flex items-center gap-4 text-gray-700">
                <!-- Search -->
                <button class="text-lg">
                    <i class="bi bi-search"></i>
                </button>

                <!-- Wishlist -->
                <router-link to="/san-pham-yeu-thich" class="relative text-xl">
                    <i class="bi bi-heart"></i>
                    <span
                        class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">
                        0
                    </span>
                </router-link>

                <!-- Cart -->
                <div class="relative cursor-pointer" @click="toggleCart">
                    <i class="bi bi-cart text-xl"></i>
                    <span
                        class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">
                        {{ totalItems }}
                    </span>
                </div>

                <!-- Auth -->
                <div class="auth-dropdown">
                    <div class="auth-toggle bg-transparent border-0 text-gray-700 relative cursor-pointer">
                        <i class="bi bi-person text-2xl"></i>
                    </div>
                    <div v-if="!isLoggedIn" class="absolute top-full left-0 w-full">
                        <AuthMenu />
                    </div>
                    <div v-else class="absolute top-full left-0 w-full">
                        <UserMenu />
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <!-- <MobileMenu :is-open="false" @close="() => { }" /> -->
    </nav>
    <CartPanel :is-open="isCartOpen" @close="toggleCart" />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import TopBar from './Topbar.vue';
import AuthMenu from '../auth/AuthMenu.vue';
import UserMenu from '../auth/UserMenu.vue';
import CartPanel from '../common/CartPanel.vue';
import { useAuthStore } from '../../stores/auth'
import Cookies from 'js-cookie'
import { useCart } from '../../composable/useCart';


const { cart, fetchCart } = useCart()
const isCartOpen = ref(false);
const toggleCart = () => { isCartOpen.value = !isCartOpen.value; };

const authStore = useAuthStore()

const totalItems = computed(() => cart.value.length)


onMounted(async () => {
    if (!authStore.user && Cookies.get('user')) {
        try {
            authStore.setUser(JSON.parse(Cookies.get('user')))
        } catch { }
    }
    await fetchCart();
})

const isLoggedIn = computed(() => !!authStore.user)


</script>
<style scoped>
/* Optional styles to mimic Bootstrap behavior */
.group:hover .group-hover\:visible {
    visibility: visible;
}

.group:hover .group-hover\:opacity-100 {
    opacity: 1;
}

.auth-dropdown {
    position: relative;
}

.auth-dropdown:hover .auth-menu {
    visibility: visible;
    opacity: 1;
    transform: translateY(0);
}
</style>
