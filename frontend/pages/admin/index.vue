<template>
  <div class="dashboard-page">
    <div class="page-header">
      <h1>Tổng quan</h1>
      <p class="text-gray-600">Thống kê hoạt động kinh doanh</p>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="tw-flex tw-justify-center tw-items-center tw-h-64">
      <div class="tw-animate-spin tw-rounded-full tw-h-12 tw-w-12 tw-border-b-2 tw-border-primary"></div>
    </div>

    <!-- Stats Cards -->
    <div v-else class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-6">
      <StatsCard title="Doanh thu tháng này" :value="formatCurrency(statistics.monthly_revenue || 0)"
        :growth="revenueGrowth" icon="fas fa-dollar-sign" iconColor="primary" />
      <StatsCard title="Đơn hàng tháng này" :value="statistics.monthly_orders || 0" :growth="ordersGrowth"
        icon="fas fa-shopping-cart" iconColor="blue" />
      <StatsCard title="Tổng khách hàng" :value="statistics.total_customers || 0" :growth="customersGrowth"
        icon="fas fa-users" iconColor="yellow" />
      <StatsCard title="Tổng sản phẩm" :value="statistics.total_products || 0" icon="fas fa-box" iconColor="purple" />
    </div>

    <!-- Charts -->
    <div v-if="!loading" class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-6 tw-mb-6">
      <RevenueChart :data="revenueData" />
      <OrdersChart :data="ordersData" />
    </div>

    <!-- Recent Orders -->
    <RecentOrders v-if="!loading" :orders="recentOrders" />
  </div>
</template>

<script setup>
useHead({
  title: "Bảng điều khiển",
  meta: [
    {
      name: "description",
      content: "Bảng điều khiển quản lý hệ thống"
    }
  ]
})
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

import { ref, onMounted, computed } from 'vue'
import StatsCard from '~/components/admin/dashboard/StatsCard.vue'
import RevenueChart from '~/components/admin/dashboard/RevenueChart.vue'
import OrdersChart from '~/components/admin/dashboard/OrdersChart.vue'
import RecentOrders from '~/components/admin/dashboard/RecentOrders.vue'

const {
  getStats,
  getYearlyRevenue,
  getOrdersByStatus,
  getRecentOrders,
  formatCurrency,
  formatNumber
} = useDashboard()

// Reactive data
const loading = ref(true)
const statistics = ref({})
const revenueData = ref({})
const ordersData = ref({})
const recentOrders = ref([])

// Computed properties for growth calculations
const revenueGrowth = computed(() => {
  // Mock growth calculation - in real app, you'd compare with previous month
  return 12.5
})

const ordersGrowth = computed(() => {
  // Mock growth calculation
  return 8.3
})

const customersGrowth = computed(() => {
  // Mock growth calculation
  return 5.2
})

// Fetch dashboard data
const fetchDashboardData = async () => {
  try {
    loading.value = true

    // Fetch main statistics
    const statsResponse = await getStats()
    if (statsResponse.success) {
      statistics.value = statsResponse.data
    }

    // Fetch yearly revenue data for chart
    const revenueResponse = await getYearlyRevenue()
    if (revenueResponse.success) {
      revenueData.value = revenueResponse.data
    }

    // Fetch orders by status data for chart
    const ordersResponse = await getOrdersByStatus()
    if (ordersResponse.success) {
      ordersData.value = ordersResponse.data
    }

    // Fetch recent orders from API
    const recentOrdersResponse = await getRecentOrders({ limit: 5 })
    if (recentOrdersResponse.success) {
      recentOrders.value = recentOrdersResponse.data
    } else {
      // Fallback to empty array if API fails
      recentOrders.value = []
    }

  } catch (error) {
    console.error('Error fetching dashboard data:', error)
    // Set empty data on error
    recentOrders.value = []
  } finally {
    loading.value = false
  }
}

// Load data on mount
onMounted(() => {
  fetchDashboardData()
})
</script>

<style scoped>
.dashboard-page {
  padding: 1.5rem;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 1.875rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.5rem;
}

.tw-bg-primary {
  background-color: #3bb77e;
}

.tw-text-primary {
  color: #3bb77e;
}

.hover\:tw-text-primary-dark:hover {
  color: #2ea16d;
}
</style>