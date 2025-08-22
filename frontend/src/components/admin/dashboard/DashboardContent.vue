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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Revenue Chart - chiếm 2/3 -->
            <div class="lg:col-span-2">
                <template v-if="loading">
                    <ChartSkeleton />
                </template>
                <template v-else>
                    <RevenueChart :data="revenueData" />
                </template>
            </div>

            <!-- Orders Status Chart - chiếm 1/3 -->
            <div class="lg:col-span-1">
                <template v-if="loading">
                    <ChartSkeleton />
                </template>
                <template v-else>
                    <OrdersChart :data="ordersData" @period-change="handleOrdersPeriodChange" />
                </template>
            </div>

            <!-- User Growth Chart - hàng dưới trái -->
            <div class="lg:col-span-1">
                <template v-if="loading">
                    <UserGrowthChartSkeleton />
                </template>
                <template v-else>
                    <UserGrowthChart :data="userGrowthData" />
                </template>
            </div>

            <!-- Inventory Chart - hàng dưới phải, chiếm 2/3 -->
            <div class="lg:col-span-2">
                <template v-if="loading">
                    <InventoryChartSkeleton />
                </template>
                <template v-else>
                    <InventoryChart :data="inventoryData" />
                </template>
            </div>
        </div>

        <!-- Recent Orders & Top Selling Section -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
            <div class="lg:col-span-8 col-span-1">
                <template v-if="loading">
                    <RecentOrdersSkeleton />
                </template>
                <template v-else>
                    <RecentOrders :orders="recentOrders" />
                </template>
            </div>
            <div class="lg:col-span-4 col-span-1">
                <template v-if="loading">
                    <TopSellingSkeleton />
                </template>
                <template v-else>
                    <TopSelling />
                </template>
            </div>
        </div>

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
import TopSelling from './TopSelling.vue'
import TopSellingSkeleton from './TopSellingSkeleton.vue'
import InventoryChart from './InventoryChart.vue'
import InventoryChartSkeleton from './InventoryChartSkeleton.vue'
import UserGrowthChart from './UserGrowthChart.vue'
import UserGrowthChartSkeleton from './UserGrowthChartSkeleton.vue'
import { useDashboard } from '../../../composable/useDashboard'

const {
    getStats,
    getYearlyRevenue,
    getOrdersByStatus,
    getRecentOrders,
    formatCurrency,
    formatNumber,
    getInventoryStats,
    getUserGrowthStats
} = useDashboard()

const loading = ref(true)
const statistics = ref({})
const revenueData = ref({})
const ordersData = ref({})
const inventoryData = ref({})
const userGrowthData = ref({})
const recentOrders = ref([])
const revenueGrowth = ref(0)
const ordersGrowth = ref(0)
const customersGrowth = ref(0)
const fetchDashboardData = async () => {
    try {
        loading.value = true

        // Gọi song song các API để tối ưu tốc độ load dashboard
        const [
            statsResponse,
            revenueResponse,
            ordersResponse,
            recentOrdersResponse,
            inventoryResponse,
            userGrowthResponse
        ] = await Promise.all([
            getStats(),
            getYearlyRevenue(),
            getOrdersByStatus(),
            getRecentOrders({ limit: 6 }),
            getInventoryStats(),
            getUserGrowthStats()
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

        if (inventoryResponse && inventoryResponse.success) {
            inventoryData.value = inventoryResponse.data
        } else {
            inventoryData.value = {}
        }

        if (userGrowthResponse && userGrowthResponse.success) {
            userGrowthData.value = userGrowthResponse.data
        } else {
            userGrowthData.value = {}
        }

    } catch (error) {
        console.error('Error fetching dashboard data:', error)
        recentOrders.value = []
    } finally {
        loading.value = false
    }
}

const handleOrdersPeriodChange = async (period) => {
    try {
        const response = await getOrdersByStatus({ period })
        if (response.success) {
            ordersData.value = response.data
        }
    } catch (error) {
        console.error('Error fetching orders data for period:', error)
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