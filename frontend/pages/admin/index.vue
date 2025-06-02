<template>
  <div class="dashboard-page">
    <div class="page-header">
      <h1>Tổng quan</h1>
      <p class="text-gray-600">Thống kê hoạt động kinh doanh</p>
    </div>

    <!-- Stats Cards -->
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-6">
      <StatsCard title="Doanh thu tháng này" :value="formatPrice(statistics.monthlyRevenue)"
        :growth="statistics.revenueGrowth" icon="fas fa-dollar-sign" iconColor="primary" />
      <StatsCard title="Đơn hàng tháng này" :value="statistics.monthlyOrders" :growth="statistics.ordersGrowth"
        icon="fas fa-shopping-cart" iconColor="blue" />
      <StatsCard title="Khách hàng mới" :value="statistics.newCustomers" :growth="statistics.customersGrowth"
        icon="fas fa-users" iconColor="yellow" />
      <StatsCard title="Sản phẩm" :value="statistics.totalProducts"
        :subText="`${statistics.outOfStock} sản phẩm hết hàng`" icon="fas fa-box" iconColor="purple" />
    </div>

    <!-- Charts -->
    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-6 tw-mb-6">
      <RevenueChart />
      <OrdersChart />
    </div>

    <!-- Recent Orders -->
    <RecentOrders :orders="recentOrders" />
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

import { ref } from 'vue'
import StatsCard from '~/components/admin/dashboard/StatsCard.vue'
import RevenueChart from '~/components/admin/dashboard/RevenueChart.vue'
import OrdersChart from '~/components/admin/dashboard/OrdersChart.vue'
import RecentOrders from '~/components/admin/dashboard/RecentOrders.vue'

// Mock statistics data
const statistics = ref({
  monthlyRevenue: 45990000,
  revenueGrowth: 12.5,
  monthlyOrders: 156,
  ordersGrowth: 8.3,
  newCustomers: 48,
  customersGrowth: -2.1,
  totalProducts: 234,
  outOfStock: 12
})

// Mock recent orders
const recentOrders = ref([
  {
    id: 'ORD001',
    customer: 'Nguyễn Văn A',
    items: 3,
    total: 2990000,
    status: 'Đang xử lý',
    date: '2024-03-15T08:30:00'
  },
  {
    id: 'ORD002',
    customer: 'Trần Thị B',
    items: 2,
    total: 1590000,
    status: 'Đã giao',
    date: '2024-03-14T15:45:00'
  },
  {
    id: 'ORD003',
    customer: 'Lê Văn C',
    items: 1,
    total: 990000,
    status: 'Đang giao',
    date: '2024-03-14T11:20:00'
  },
  {
    id: 'ORD004',
    customer: 'Phạm Thị D',
    items: 4,
    total: 3990000,
    status: 'Chờ thanh toán',
    date: '2024-03-14T09:15:00'
  },
  {
    id: 'ORD005',
    customer: 'Hoàng Văn E',
    items: 2,
    total: 1890000,
    status: 'Đã hủy',
    date: '2024-03-13T16:50:00'
  }
])

// Utility functions
const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price)
}
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