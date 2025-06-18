<template>
  <TopBar />
  <nav class="navbar navbar-expand-lg navbar-light tw-sticky tw-top-0 tw-z-50 tw-bg-white tw-shadow-sm">
    <div class="container">
      <NuxtLink to="/" class="navbar-brand">
        <img src="https://i.imgur.com/rugQu4F.png" alt="EGA MEN" class="tw-w-20">
      </NuxtLink>

      <!-- Desktop Navigation -->
      <div class="navbar-collapse d-none d-lg-flex justify-content-center">
        <ul class="navbar-nav">
          <li class="nav-item">
            <NuxtLink to="/" class="nav-link tw-font-medium">Trang chủ</NuxtLink>
          </li>
          <li class="nav-item has-megamenu">
            <NuxtLink to="/product" class="nav-link tw-font-medium" id="productDropdown">
              Sản phẩm
            </NuxtLink>
            <MegaMenu />
          </li>
          <li class="nav-item">
            <NuxtLink to="/about" class="nav-link tw-font-medium">Giới thiệu</NuxtLink>
          </li>
          <li class="nav-item">
            <NuxtLink to="/blogs" class="nav-link tw-font-medium">Tin tức</NuxtLink>
          </li>
          <li class="nav-item">
            <NuxtLink to="/contact" class="nav-link tw-font-medium">Liên hệ</NuxtLink>
          </li>
          <li class="nav-item">
            <NuxtLink to="/order-tracking" class="nav-link tw-font-medium">Kiểm tra đơn hàng</NuxtLink>
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
        <NuxtLink to="/wishlist" class="tw-text-gray-700 tw-relative">
          <i class="bi bi-heart tw-text-xl"></i>
          <span
            class="tw-absolute -tw-top-2 -tw-right-2 tw-bg-red-500 tw-text-white tw-rounded-full tw-w-5 tw-h-5 tw-flex tw-items-center tw-justify-center tw-text-xs">2</span>
        </NuxtLink>
        <div class="cart-dropdown">
          <div class="cart-toggle tw-bg-transparent tw-border-0 tw-text-gray-700 tw-relative tw-cursor-pointer"
            @click="toggleCart">
            <i class="bi bi-cart tw-text-xl"></i>
            <span
              class="tw-absolute -tw-top-2 -tw-right-2 tw-bg-red-500 tw-text-white tw-rounded-full tw-w-5 tw-h-5 tw-flex tw-items-center tw-justify-center tw-text-xs">2</span>
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

const isCartOpen = ref(false)
const isMobileMenuOpen = ref(false)
const token = useCookie('token')
const userInfo = useCookie('user')

const toggleCart = () => {
  isCartOpen.value = !isCartOpen.value
  if (isCartOpen.value) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
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
</script>

<style scoped>
.navbar {
  padding: 1rem 0;
}

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