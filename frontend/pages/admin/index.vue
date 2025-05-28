<template>
  <div class="dashboard-page">
    <div class="page-header">
      <h1>Tổng quan</h1>
      <p class="text-gray-600">Thống kê hoạt động kinh doanh</p>
    </div>

    <!-- Stats Cards -->
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-6">
      <!-- Revenue -->
      <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-start">
          <div>
            <p class="tw-text-gray-500 tw-text-sm">Doanh thu tháng này</p>
            <h3 class="tw-text-2xl tw-font-bold tw-mt-1">{{ formatPrice(statistics.monthlyRevenue) }}</h3>
            <p class="tw-text-sm tw-mt-1"
              :class="statistics.revenueGrowth >= 0 ? 'tw-text-green-600' : 'tw-text-red-600'">
              <i :class="['fas', statistics.revenueGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down']"></i>
              {{ Math.abs(statistics.revenueGrowth) }}% so với tháng trước
            </p>
          </div>
          <div class="tw-bg-primary/10 tw-p-3 tw-rounded-full">
            <i class="fas fa-dollar-sign tw-text-primary"></i>
          </div>
        </div>
      </div>

      <!-- Orders -->
      <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-start">
          <div>
            <p class="tw-text-gray-500 tw-text-sm">Đơn hàng tháng này</p>
            <h3 class="tw-text-2xl tw-font-bold tw-mt-1">{{ statistics.monthlyOrders }}</h3>
            <p class="tw-text-sm tw-mt-1"
              :class="statistics.ordersGrowth >= 0 ? 'tw-text-green-600' : 'tw-text-red-600'">
              <i :class="['fas', statistics.ordersGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down']"></i>
              {{ Math.abs(statistics.ordersGrowth) }}% so với tháng trước
            </p>
          </div>
          <div class="tw-bg-blue-500/10 tw-p-3 tw-rounded-full">
            <i class="fas fa-shopping-cart tw-text-blue-500"></i>
          </div>
        </div>
      </div>

      <!-- Customers -->
      <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-start">
          <div>
            <p class="tw-text-gray-500 tw-text-sm">Khách hàng mới</p>
            <h3 class="tw-text-2xl tw-font-bold tw-mt-1">{{ statistics.newCustomers }}</h3>
            <p class="tw-text-sm tw-mt-1"
              :class="statistics.customersGrowth >= 0 ? 'tw-text-green-600' : 'tw-text-red-600'">
              <i :class="['fas', statistics.customersGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down']"></i>
              {{ Math.abs(statistics.customersGrowth) }}% so với tháng trước
            </p>
          </div>
          <div class="tw-bg-yellow-500/10 tw-p-3 tw-rounded-full">
            <i class="fas fa-users tw-text-yellow-500"></i>
          </div>
        </div>
      </div>

      <!-- Products -->
      <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-start">
          <div>
            <p class="tw-text-gray-500 tw-text-sm">Sản phẩm</p>
            <h3 class="tw-text-2xl tw-font-bold tw-mt-1">{{ statistics.totalProducts }}</h3>
            <p class="tw-text-sm tw-mt-1 tw-text-gray-500">
              {{ statistics.outOfStock }} sản phẩm hết hàng
            </p>
          </div>
          <div class="tw-bg-purple-500/10 tw-p-3 tw-rounded-full">
            <i class="fas fa-box tw-text-purple-500"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-6 tw-mb-6">
      <!-- Revenue Chart -->
      <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <h3 class="tw-font-semibold tw-mb-4">Doanh thu 7 ngày qua</h3>
        <div class="tw-h-80">
          <!-- TODO: Add revenue chart -->
        </div>
      </div>

      <!-- Orders Chart -->
      <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <h3 class="tw-font-semibold tw-mb-4">Đơn hàng theo trạng thái</h3>
        <div class="tw-h-80">
          <!-- TODO: Add orders chart -->
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
      <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
        <h3 class="tw-font-semibold">Đơn hàng gần đây</h3>
        <NuxtLink to="/admin/orders" class="tw-text-primary hover:tw-text-primary-dark">
          Xem tất cả
        </NuxtLink>
      </div>
      <div class="tw-overflow-x-auto">
        <table class="tw-w-full">
          <thead>
            <tr class="tw-border-b tw-bg-gray-50">
              <th class="tw-px-4 tw-py-3 tw-text-left">Mã đơn</th>
              <th class="tw-px-4 tw-py-3 tw-text-left">Khách hàng</th>
              <th class="tw-px-4 tw-py-3 tw-text-left">Sản phẩm</th>
              <th class="tw-px-4 tw-py-3 tw-text-left">Tổng tiền</th>
              <th class="tw-px-4 tw-py-3 tw-text-left">Trạng thái</th>
              <th class="tw-px-4 tw-py-3 tw-text-left">Ngày đặt</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in recentOrders" :key="order.id" class="tw-border-b hover:tw-bg-gray-50">
              <td class="tw-px-4 tw-py-3">#{{ order.id }}</td>
              <td class="tw-px-4 tw-py-3">{{ order.customer }}</td>
              <td class="tw-px-4 tw-py-3">{{ order.items }} sản phẩm</td>
              <td class="tw-px-4 tw-py-3">{{ formatPrice(order.total) }}</td>
              <td class="tw-px-4 tw-py-3">
                <span :class="orderStatusClass(order.status)">
                  {{ order.status }}
                </span>
              </td>
              <td class="tw-px-4 tw-py-3">{{ formatDate(order.date) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'admin'
})

import { ref } from 'vue'

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

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const orderStatusClass = (status) => {
  switch (status) {
    case 'Đã giao':
      return 'tw-bg-green-100 tw-text-green-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
    case 'Đang giao':
      return 'tw-bg-blue-100 tw-text-blue-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
    case 'Đang xử lý':
      return 'tw-bg-yellow-100 tw-text-yellow-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
    case 'Chờ thanh toán':
      return 'tw-bg-orange-100 tw-text-orange-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
    case 'Đã hủy':
      return 'tw-bg-red-100 tw-text-red-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
    default:
      return 'tw-bg-gray-100 tw-text-gray-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
  }
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