<template>
  <TopBar />
  <nav class="navbar navbar-expand-lg navbar-light tw-sticky tw-top-0 tw-z-50 tw-bg-white tw-shadow-sm">
    <div class="container">
      <NuxtLink to="/" class="navbar-brand">
        <img src="https://ngthanhvu.github.io/z6626419002677_17d1cb4617e8fe122076281de3bf4722-removebg-preview.png"
          alt="EGA MEN" class="tw-w-20">
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
          <AuthMenu />
        </div>
      </div>

      <!-- Mobile Navigation -->
      <div class="mobile-menu" :class="{ 'tw-block': isMobileMenuOpen, 'tw-hidden': !isMobileMenuOpen }">
        <div class="tw-flex tw-justify-between tw-items-center tw-p-4 tw-border-b">
          <h3 class="tw-m-0 tw-font-medium">Menu</h3>
          <button class="tw-bg-transparent tw-border-0" @click="closeMobileMenu">
            <i class="bi bi-x-lg tw-text-xl"></i>
          </button>
        </div>

        <!-- Mobile Icons -->
        <div class="tw-flex tw-justify-around tw-items-center tw-p-4 tw-border-b">
          <NuxtLink to="/search" class="tw-text-gray-700" @click="closeMobileMenu">
            <i class="bi bi-search tw-text-xl"></i>
          </NuxtLink>
          <NuxtLink to="/wishlist" class="tw-text-gray-700 tw-relative" @click="closeMobileMenu">
            <i class="bi bi-heart tw-text-xl"></i>
            <span
              class="tw-absolute -tw-top-2 -tw-right-2 tw-bg-red-500 tw-text-white tw-rounded-full tw-w-5 tw-h-5 tw-flex tw-items-center tw-justify-center tw-text-xs">2</span>
          </NuxtLink>
          <NuxtLink to="/cart" class="tw-text-gray-700 tw-relative" @click="closeMobileMenu">
            <i class="bi bi-cart tw-text-xl"></i>
            <span
              class="tw-absolute -tw-top-2 -tw-right-2 tw-bg-red-500 tw-text-white tw-rounded-full tw-w-5 tw-h-5 tw-flex tw-items-center tw-justify-center tw-text-xs">2</span>
          </NuxtLink>
          <NuxtLink to="/login" class="tw-text-gray-700" @click="closeMobileMenu">
            <i class="bi bi-person tw-text-xl"></i>
          </NuxtLink>
        </div>

        <ul class="tw-list-none tw-p-0 tw-m-0">
          <li class="tw-py-3 tw-border-b">
            <NuxtLink to="/" class="tw-text-gray-700 tw-font-medium tw-px-4 tw-block" @click="closeMobileMenu">Trang chủ
            </NuxtLink>
          </li>
          <li class="tw-py-3 tw-border-b">
            <NuxtLink to="/product" class="tw-text-gray-700 tw-font-medium tw-px-4 tw-block" @click="closeMobileMenu">
              Sản phẩm</NuxtLink>
          </li>
          <li class="tw-py-3 tw-border-b">
            <NuxtLink to="/about" class="tw-text-gray-700 tw-font-medium tw-px-4 tw-block" @click="closeMobileMenu">Giới
              thiệu</NuxtLink>
          </li>
          <li class="tw-py-3 tw-border-b">
            <NuxtLink to="/blogs" class="tw-text-gray-700 tw-font-medium tw-px-4 tw-block" @click="closeMobileMenu">Tin
              tức</NuxtLink>
          </li>
          <li class="tw-py-3 tw-border-b">
            <NuxtLink to="/contact" class="tw-text-gray-700 tw-font-medium tw-px-4 tw-block" @click="closeMobileMenu">
              Liên hệ</NuxtLink>
          </li>
          <li class="tw-py-3 tw-border-b">
            <NuxtLink to="/order-tracking" class="tw-text-gray-700 tw-font-medium tw-px-4 tw-block"
              @click="closeMobileMenu">Kiểm tra đơn hàng</NuxtLink>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup>
import TopBar from './TopBar.vue'
import MegaMenu from './MegaMenu.vue'
import CartPanel from './CartPanel.vue'
import AuthMenu from './AuthMenu.vue'

const isCartOpen = ref(false)
const isMobileMenuOpen = ref(false)

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
  color: #2563eb;
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

.mobile-menu {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  width: 100%;
  background: white;
  z-index: 1000;
  box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
  overflow-y: auto;
  transform: translateX(100%);
  transition: transform 0.3s ease-in-out;
}

.mobile-menu.tw-block {
  transform: translateX(0);
}

@media (min-width: 992px) {
  .mobile-menu {
    display: none !important;
  }
}
</style>