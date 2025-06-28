<template>
  <div class="dashboard-page">
    <div class="page-header">
      <h1>Tổng quan</h1>
      <p class="text-gray-600">Thống kê hoạt động kinh doanh</p>
    </div>

    <!-- Stats Cards Section -->
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-6">
      <template v-if="loading">
        <StatsCardSkeleton v-for="i in 4" :key="i" />
      </template>
      <template v-else>
        <StatsCard title="Doanh thu tháng này" :value="formatCurrency(statistics.monthly_revenue || 0)"
          :growth="revenueGrowth" icon="fas fa-dollar-sign" iconColor="primary" />
        <StatsCard title="Đơn hàng tháng này" :value="statistics.monthly_orders || 0" :growth="ordersGrowth"
          icon="fas fa-shopping-cart" iconColor="blue" />
        <StatsCard title="Tổng khách hàng" :value="statistics.total_customers || 0" :growth="customersGrowth"
          icon="fas fa-users" iconColor="yellow" />
        <StatsCard title="Tổng sản phẩm" :value="statistics.total_products || 0" icon="fas fa-box" iconColor="purple" />
      </template>
    </div>

    <!-- Charts Section -->
    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-6 tw-mb-6">
      <template v-if="loading">
        <ChartSkeleton />
        <ChartSkeleton />
      </template>
      <template v-else>
        <RevenueChart :data="revenueData" />
        <OrdersChart :data="ordersData" />
      </template>
    </div>

    <!-- Recent Orders Section -->
    <template v-if="loading">
      <RecentOrdersSkeleton />
    </template>
    <template v-else>
      <RecentOrders :orders="recentOrders" />
    </template>
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
import StatsCardSkeleton from '~/components/admin/dashboard/StatsCardSkeleton.vue'
import RevenueChart from '~/components/admin/dashboard/RevenueChart.vue'
import OrdersChart from '~/components/admin/dashboard/OrdersChart.vue'
import ChartSkeleton from '~/components/admin/dashboard/ChartSkeleton.vue'
import RecentOrders from '~/components/admin/dashboard/RecentOrders.vue'
import RecentOrdersSkeleton from '~/components/admin/dashboard/RecentOrdersSkeleton.vue'

const {
  getStats,
  getYearlyRevenue,
  getOrdersByStatus,
  getRecentOrders,
  formatCurrency,
  formatNumber
} = useDashboard()

const loading = ref(true)
const statistics = ref({})
const revenueData = ref({})
const ordersData = ref({})
const recentOrders = ref([])

const revenueGrowth = computed(() => {
  return 12.5
})

const ordersGrowth = computed(() => {
  return 8.3
})

const customersGrowth = computed(() => {
  return 5.2
})

const fetchDashboardData = async () => {
  try {
    loading.value = true

    const statsResponse = await getStats()
    if (statsResponse.success) {
      statistics.value = statsResponse.data
    }

    const revenueResponse = await getYearlyRevenue()
    if (revenueResponse.success) {
      revenueData.value = revenueResponse.data
    }

    const ordersResponse = await getOrdersByStatus()
    if (ordersResponse.success) {
      ordersData.value = ordersResponse.data
    }

    const recentOrdersResponse = await getRecentOrders({ limit: 5 })
    if (recentOrdersResponse.success) {
      recentOrders.value = recentOrdersResponse.data
    } else {
      recentOrders.value = []
    }

  } catch (error) {
    console.error('Error fetching dashboard data:', error)
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