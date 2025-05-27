<template>
  <aside class="sidebar">
    <div class="sidebar-logo">
      <!-- <img src="https://i.imgur.com/1X6hF8g.png" alt="Logo" /> -->
      <span class="logo-text">Admin Panel</span>
    </div>
    <nav class="sidebar-nav">
      <!-- Reports Section -->
      <div class="nav-section">
        <div class="section-title">Báo cáo</div>
        <NuxtLink to="/admin" class="nav-item">
          <i class="fas fa-chart-line"></i>
          <span>Dashboard</span>
        </NuxtLink>
        <NuxtLink to="/admin/statistics" class="nav-item">
          <i class="fas fa-chart-bar"></i>
          <span>Thống kê</span>
        </NuxtLink>
        <NuxtLink to="/admin/transactions" class="nav-item">
          <i class="fas fa-dollar-sign"></i>
          <span>Giao dịch</span>
        </NuxtLink>
      </div>

      <!-- Management Section -->
      <div class="nav-section">
        <div class="section-title">Quản lý</div>
        <!-- Products Management -->
        <div class="nav-item dropdown-toggle" @click="showProductsMenu = !showProductsMenu">
          <i class="fas fa-box"></i>
          <span>Sản phẩm</span>
        </div>
        <div v-show="showProductsMenu" class="submenu">
          <NuxtLink to="/admin/products" class="nav-sub-item">Tất cả sản phẩm</NuxtLink>
          <NuxtLink to="/admin/add-product" class="nav-sub-item">Thêm sản phẩm</NuxtLink>
          <NuxtLink to="/admin/brands" class="nav-sub-item">Thương hiệu</NuxtLink>
        </div>

        <NuxtLink to="/admin/orders" class="nav-item">
          <i class="fas fa-shopping-cart"></i>
          <span>Đơn hàng</span>
        </NuxtLink>
        <NuxtLink to="/admin/sellers" class="nav-item">
          <i class="fas fa-store"></i>
          <span>Người bán</span>
        </NuxtLink>
        <NuxtLink to="/admin/reviews" class="nav-item">
          <i class="fas fa-star"></i>
          <span>Đánh giá</span>
        </NuxtLink>
        <NuxtLink to="/admin/account" class="nav-item">
          <i class="fas fa-user"></i>
          <span>Tài khoản</span>
        </NuxtLink>
        <NuxtLink to="/admin/settings" class="nav-item">
          <i class="fas fa-cog"></i>
          <span>Cài đặt</span>
        </NuxtLink>
      </div>
    </nav>
  </aside>
</template>

<script setup>
import { ref } from 'vue'

const showProductsMenu = ref(false)

const handleLogout = () => {
  console.log('Logout clicked')
}
</script>

<style scoped>
.submenu {
  padding-left: 2rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  margin-top: 0.25rem;
}

.nav-sub-item {
  font-size: 0.9rem;
  color: #4b5563;
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
  transition: background 0.2s, color 0.2s;
  text-decoration: none;
}

.nav-sub-item:hover,
.nav-sub-item.router-link-active {
  background: #e9f6ef;
  color: #3bb77e;
}

.menu-toggle {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: #3bb77e;
  cursor: pointer;
  margin-right: 1rem;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #253d4e;
  cursor: pointer;
  transition: background 0.2s;
}

.admin-layout {
  display: flex;
  min-height: 100vh;
  background: #f7f8fa;
}

.sidebar {
  width: 250px;
  background: #fff;
  border-right: 1px solid #e5e7eb;
  padding: 1.5rem 1rem;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  overflow-y: auto;
}

.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 2rem;
}

.sidebar-logo img {
  width: 36px;
  height: 36px;
}

.logo-text {
  font-size: 1.5rem;
  font-weight: bold;
  color: #3bb77e;
  letter-spacing: 1px;
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
</style>