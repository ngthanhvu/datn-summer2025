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
          <button class="header-btn"><i class="fas fa-bell"></i><span class="badge">3</span></button>
          <div class="avatar-dropdown">
            <div class="avatar-wrapper" @click="isDropdownOpen = !isDropdownOpen">
              <img :src="user?.avatar" alt="User Avatar" class="avatar">
              <span class="user-name">Xin chào <b>{{ user?.username }}</b></span>
              <i class="fas fa-chevron-down"></i>
            </div>
            <div class="dropdown-menu menu-drop" :class="{ 'show': isDropdownOpen }">
              <div class="user-info">
                <span class="name">{{ user?.username }}</span>
                <span class="email">{{ user?.email }}</span>
              </div>
              <div class="dropdown-item tw-cursor-pointer" @click="handleBackHome">
                <i class="fas fa-sign-out-alt"></i>
                <span>Trở về trang chủ</span>
              </div>
            </div>
          </div>
        </div>
      </header>
      <main class="content">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
const { isAuthenticated, isAdmin, checkAuth, checkAdmin } = useAuth()
import SidebarAdmin from '~/components/admin/SidebarAdmin.vue'

const isDropdownOpen = ref(false)

const user = useCookie('user')

onMounted(() => {
  if (!isAuthenticated.value) {
    checkAuth()
  }
  if (isAuthenticated.value && !isAdmin.value) {
    checkAdmin()
  }

  document.addEventListener('click', (e) => {
    const dropdown = document.querySelector('.avatar-dropdown')
    if (dropdown && !dropdown.contains(e.target)) {
      isDropdownOpen.value = false
    }
  })
})

const handleBackHome = () => {
  navigateTo('/')
}
</script>

<style scoped>
.menu-drop {
  position: absolute;
  top: 60px;
  right: 0;
}

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
  padding: 1.5rem 2rem 1rem 2rem;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  position: sticky;
  top: 0;
  z-index: 10;
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
  font-size: 1rem;
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

.header-btn {
  background: none;
  border: none;
  position: relative;
  font-size: 1.2rem;
  color: #253d4e;
  cursor: pointer;
}

.header-btn .badge {
  position: absolute;
  top: -6px;
  right: -6px;
  background: #3bb77e;
  color: #fff;
  border-radius: 50%;
  font-size: 0.7rem;
  padding: 2px 6px;
}

.avatar-dropdown {
  position: relative;
}

.avatar-wrapper {
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 8px;
  border-radius: 6px;
  transition: background-color 0.2s;
}

.avatar-wrapper:hover {
  background-color: #f3f4f6;
}

.user-name {
  font-size: 14px;
  font-weight: 500;
  color: #1f2937;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
}

.dropdown-menu {
  min-width: 240px;
  padding: 8px 0;
}

.user-info {
  padding: 12px 16px;
}

.user-info .name {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #1f2937;
}

.user-info .email {
  display: block;
  font-size: 13px;
  color: #6b7280;
  margin-top: 2px;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 16px;
  color: #4b5563;
  font-size: 14px;
  transition: background-color 0.2s;
}

.dropdown-item i {
  font-size: 16px;
  color: #6b7280;
}

.dropdown-divider {
  margin: 8px 0;
}
</style>