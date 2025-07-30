<template>
    <div class="dashboard-page">
        <div class="page-header">
            <h1>Tổng quan</h1>
            <p class="text-gray-600">Thống kê hoạt động kinh doanh</p>
        </div>

        <!-- Stats Cards Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
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
                <StatsCard title="Tổng sản phẩm" :value="statistics.total_products || 0" icon="fas fa-box"
                    iconColor="purple" />
            </template>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
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
import { ref, onMounted, computed } from 'vue'
import StatsCard from './StatsCard.vue'
import StatsCardSkeleton from './StatsCardSkeleton.vue'
import RevenueChart from './RevenueChart.vue'
import OrdersChart from './OrdersChart.vue'
import ChartSkeleton from './ChartSkeleton.vue'
import RecentOrders from './RecentOrders.vue'
import RecentOrdersSkeleton from './RecentOrdersSkeleton.vue'
import { useDashboard } from '../../../composable/useDashboard'

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

        // Gọi song song các API để tối ưu tốc độ load dashboard
        const [
            statsResponse,
            revenueResponse,
            ordersResponse,
            recentOrdersResponse
        ] = await Promise.all([
            getStats(),
            getYearlyRevenue(),
            getOrdersByStatus(),
            getRecentOrders({ limit: 5 })
        ])

        if (statsResponse.success) {
            statistics.value = statsResponse.data
        }
        if (revenueResponse.success) {
            revenueData.value = revenueResponse.data
        }
        if (ordersResponse.success) {
            ordersData.value = ordersResponse.data
        }
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

.bg-primary {
    background-color: #3bb77e;
}

.text-primary {
    color: #3bb77e;
}

.hover\:text-primary-dark:hover {
    color: #2ea16d;
}
</style>