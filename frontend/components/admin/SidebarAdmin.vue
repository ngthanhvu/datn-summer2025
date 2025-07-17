<template>
  <aside class="sidebar">
    <div class="sidebar-logo">
      <!-- <img src="https://i.imgur.com/1X6hF8g.png" alt="Logo" /> -->
      <span class="logo-text">Admin Panel</span>
    </div>
    <nav class="sidebar-nav">
      <!-- Overview Section -->
      <div class="nav-section">
        <div class="section-title text-muted tw-font-bold tw-text-[14px]">Tổng quan</div>
        <NuxtLink to="/admin" class="nav-item">
          <i class="fas fa-tachometer-alt"></i>
          <span>Bảng điều khiển</span>
        </NuxtLink>

        <!-- Products Dropdown -->
        <div class="nav-item dropdown-toggle" @click="showProductsMenu = !showProductsMenu">
          <i class="fa-solid fa-cube"></i>
          <span>Sản phẩm</span>
        </div>
        <div v-show="showProductsMenu" class="submenu">
          <NuxtLink to="/admin/products" class="nav-sub-item">Tất cả sản phẩm</NuxtLink>
          <NuxtLink to="/admin/categories" class="nav-sub-item">Danh mục</NuxtLink>
          <NuxtLink to="/admin/brands" class="nav-sub-item">Thương hiệu</NuxtLink>
        </div>

        <NuxtLink to="/admin/orders" class="nav-item">
          <i class="fas fa-shopping-cart"></i>
          <span>Đơn hàng</span>
        </NuxtLink>
        <NuxtLink to="/admin/customers" class="nav-item">
          <i class="fas fa-users"></i>
          <span>Khách hàng</span>
        </NuxtLink>
        <NuxtLink to="/admin/promotions" class="nav-item">
          <i class="fa-solid fa-ticket"></i>
          <span>Khuyến mãi</span>
        </NuxtLink>
        <NuxtLink to="/admin/flashsale" class="nav-item">
          <i class="fa-solid fa-bolt"></i>
          <span>Flash Sale</span>
        </NuxtLink>

        <!-- Inventory Management -->
        <div class="nav-item dropdown-toggle" @click="showInventoryMenu = !showInventoryMenu">
          <i class="fas fa-warehouse"></i>
          <span>Quản lý kho</span>
        </div>
        <div v-show="showInventoryMenu" class="submenu">
          <NuxtLink to="/admin/inventory" class="nav-sub-item">Tổng quan kho</NuxtLink>
          <NuxtLink to="/admin/inventory/import" class="nav-sub-item">Nhập hàng</NuxtLink>
          <NuxtLink to="/admin/inventory/history" class="nav-sub-item">Hoá đơn</NuxtLink>
        </div>
      </div>

      <!-- Communication Section -->
      <div class="nav-section">
        <div class="section-title text-muted tw-font-bold tw-text-[14px]">Giao tiếp</div>
        <NuxtLink to="/admin/blogs" class="nav-item">
          <i class="fa-solid fa-newspaper"></i>
          Bài viết
        </NuxtLink>
        <NuxtLink to="/admin/messages" class="nav-item">
          <i class="fas fa-envelope"></i>
          <span>Tin nhắn</span>
          <span v-if="unreadMessages > 0" class="badge">{{ unreadMessages }}</span>
        </NuxtLink>
        <NuxtLink to="/admin/comments" class="nav-item">
          <i class="fas fa-comments"></i>
          <span>Đánh giá</span>
          <span v-if="unapprovedReviews > 0" class="badge">{{ unapprovedReviews }}</span>
        </NuxtLink>
      </div>

      <!-- System Section -->
      <div class="nav-section">
        <div class="section-title text-muted tw-font-bold tw-text-[14px]">Hệ thống</div>
        <NuxtLink to="/admin/settings" class="nav-item">
          <i class="fas fa-cog"></i>
          <span>Cài đặt</span>
        </NuxtLink>
      </div>
    </nav>
  </aside>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useChat } from '~/composables/useChat'
import { useAdminReviews } from '~/composables/useAdminReviews'

const showProductsMenu = ref(false)
const showInventoryMenu = ref(false)

const unreadMessages = ref(0)
const unapprovedReviews = ref(0)

const { getUnreadCount } = useChat()
const { getAllReviews } = useAdminReviews()

onMounted(async () => {
  // Lấy số tin nhắn chưa đọc
  try {
    const res = await getUnreadCount()
    unreadMessages.value = res.unread_count || 0
  } catch { }

  // Lấy số đánh giá chưa duyệt
  try {
    const reviews = await getAllReviews(1, 100)
    // Nếu API trả về {data: [...]}, còn không thì sửa lại cho đúng
    unapprovedReviews.value = (reviews.data || reviews).filter(r => !r.is_approved).length
  } catch { }
})

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

.dropdown-toggle i.ml-auto {
  transition: transform 0.2s ease;
}

.dropdown-toggle i.rotate-180 {
  transform: rotate(180deg);
}

.badge {
  background: #3bb77e;
  color: white;
  padding: 2px 6px;
  border-radius: 10px;
  font-size: 0.75rem;
  margin-left: auto;
}
</style>