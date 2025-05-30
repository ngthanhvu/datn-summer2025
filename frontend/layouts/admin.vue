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
              <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User Avatar" class="avatar">
            </div>
            <div class="dropdown-menu" :class="{ 'show': isDropdownOpen }">
              <div class="dropdown-item">
                <i class="fas fa-user"></i>
                <span>Profile</span>
              </div>
              <div class="dropdown-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
              </div>
              <div class="dropdown-divider"></div>
              <div
                class="dropdown-item text-danger tw-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-transition-colors hover:tw-bg-red-50 tw-rounded-md tw-mx-2 tw-cursor-pointer"
                @click="handleLogout">
                <i class="fas fa-sign-out-alt tw-text-red-500"></i>
                <span class="tw-font-medium tw-text-red-600">Logout</span>
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
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  min-width: 200px;
  z-index: 1000;
  border: 1px solid #e5e7eb;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.2s ease-in-out;
}

.dropdown-menu.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-item:hover {
  background: #f7f8fa;
}

.dropdown-item i {
  width: 20px;
  text-align: center;
}

.dropdown-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 0.5rem 0;
}

.text-danger {
  color: #ef4444;
}

.text-danger:hover {
  background: #fee2e2;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #3bb77e;
}

.content {
  padding: 2rem 2rem 0 2rem;
  flex: 1;
}

@media (max-width: 900px) {
  .sidebar {
    display: none;
  }

  .main-content {
    margin-left: 0;
    padding-left: 0;
  }
}

.section-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  padding: 0.5rem 1rem;
  margin-bottom: 0.5rem;
}
</style>