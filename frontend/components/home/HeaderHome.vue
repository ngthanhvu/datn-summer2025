<template>
  <TopBar />
  <nav class="navbar navbar-expand-lg navbar-light tw-sticky tw-top-0 tw-z-50 tw-bg-white tw-shadow-sm">
    <div class="container">
      <NuxtLink to="/" class="navbar-brand">
        <img :src="siteLogo" alt="EGA MEN" class="tw-w-20">
      </NuxtLink>

      <!-- Desktop Navigation -->
      <div class="navbar-collapse d-none d-lg-flex justify-content-center">
        <ul class="navbar-nav">
          <li class="nav-item">
            <NuxtLink to="/" class="nav-link tw-font-medium">Trang chủ</NuxtLink>
          </li>
          <li class="nav-item has-megamenu">
            <NuxtLink to="/san-pham" class="nav-link tw-font-medium" id="productDropdown">
              Sản phẩm
            </NuxtLink>
            <MegaMenu />
          </li>
          <li class="nav-item">
            <NuxtLink to="/gioi-thieu" class="nav-link tw-font-medium">Giới thiệu</NuxtLink>
          </li>
          <li class="nav-item">
            <NuxtLink to="/tin-tuc" class="nav-link tw-font-medium">Tin tức</NuxtLink>
          </li>
          <li class="nav-item">
            <NuxtLink to="/lien-he" class="nav-link tw-font-medium">Liên hệ</NuxtLink>
          </li>
          <li class="nav-item">
            <NuxtLink to="/tra-cuu-don-hang" class="nav-link tw-font-medium">Kiểm tra đơn hàng</NuxtLink>
          </li>
        </ul>
      </div>

      <!-- Mobile menu button -->
      <button class="navbar-toggler tw-border-0 tw-p-0 d-lg-none" type="button" @click="toggleMobileMenu">
        <i class="bi bi-list" style="font-size: 1.5rem;"></i>
      </button>

      <!-- Desktop Icons -->
      <div class="d-none d-lg-flex align-items-center gap-3">
        <button class="tw-bg-transparent tw-border-0">
          <i class="bi bi-search tw-text-lg"></i>
        </button>
        <NuxtLink to="/san-pham-yeu-thich" class="tw-text-gray-700 tw-relative">
          <i class="bi bi-heart tw-text-xl"></i>
          <span
            class="tw-absolute -tw-top-2 -tw-right-2 tw-bg-red-500 tw-text-white tw-rounded-full tw-w-5 tw-h-5 tw-flex tw-items-center tw-justify-center tw-text-xs">0</span>
        </NuxtLink>
        <div class="cart-dropdown">
          <div class="cart-toggle tw-bg-transparent tw-border-0 tw-text-gray-700 tw-relative tw-cursor-pointer"
            @click="toggleCart">
            <i class="bi bi-cart tw-text-xl"></i>
            <span
              class="tw-absolute -tw-top-2 -tw-right-2 tw-bg-red-500 tw-text-white tw-rounded-full tw-w-5 tw-h-5 tw-flex tw-items-center tw-justify-center tw-text-xs">{{
                cartStore.cart?.length || 0 }}</span>
          </div>
          <CartPanel :is-open="isCartOpen" @close="toggleCart" />
        </div>
        <div class="auth-dropdown">
          <div class="auth-toggle tw-bg-transparent tw-border-0 tw-text-gray-700 tw-relative tw-cursor-pointer">
            <i class="bi bi-person tw-text-2xl"></i>
          </div>
          <div v-if="!token" class="tw-absolute tw-top-full tw-left-0 tw-w-full">
            <AuthMenu />
          </div>
          <div v-else class="tw-absolute tw-top-full tw-left-0 tw-w-full">
            <UserMenu />
          </div>
        </div>
      </div>

      <!-- Mobile Menu Component -->
      <MobileMenu :is-open="isMobileMenuOpen" @close="closeMobileMenu" />
    </div>
  </nav>
</template>

<script setup>
import TopBar from './TopBar.vue'
import MegaMenu from './MegaMenu.vue'
import CartPanel from './CartPanel.vue'
import AuthMenu from './AuthMenu.vue'
import UserMenu from './UserMenu.vue'
import MobileMenu from './MobileMenu.vue'
import { useCartStore } from '~/stores/useCartStore'
import { ref, onMounted } from 'vue'

const cartStore = useCartStore()
const isCartOpen = ref(false)
const isMobileMenuOpen = ref(false)
const token = useCookie('token')
const userInfo = useCookie('user')

onMounted(async () => {
  if (!cartStore.cart.length) {
    await cartStore.fetchCart()
  }
})

const toggleCart = async () => {
  isCartOpen.value = !isCartOpen.value
  if (isCartOpen.value) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
    await cartStore.fetchCart()
  }
}

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
  if (isMobileMenuOpen.value) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
  document.body.style.overflow = ''
}
defineProps({
  siteLogo: {
    type: String,
    default: '/logo.png'
  }
})

</script>

<style scoped>
.nav-link {
  padding: 0.5rem 1rem;
  color: #374151;
}

.nav-link:hover {
  color: #81aacc;
}

.has-megamenu {
  position: static !important;
}

.has-megamenu:hover .megamenu {
  visibility: visible;
  opacity: 1;
  transform: translateX(-50%) translateY(0);
}

.cart-dropdown {
  position: relative;
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