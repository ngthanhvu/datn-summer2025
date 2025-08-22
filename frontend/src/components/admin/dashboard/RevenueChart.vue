<template>
    <div class="bg-white rounded-lg shadow px-4 sm:px-8 py-4 sm:py-8">
        <div class="chart-header mb-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-lg mb-1">Doanh thu</h3>
                    <p class="text-sm text-gray-600">Biểu đồ doanh thu theo thời gian</p>
                </div>

                <!-- Date Range Filter - Responsive Layout -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full sm:w-auto">
                    <!-- Mobile: Stacked layout -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full sm:w-auto">
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <label class="text-xs font-medium text-gray-600 whitespace-nowrap">Từ:</label>
                            <input type="date" v-model="startDate" @change="handleDateChange"
                                class="px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 w-full sm:w-32" />
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <label class="text-xs font-medium text-gray-600 whitespace-nowrap">Đến:</label>
                            <input type="date" v-model="endDate" @change="handleDateChange"
                                class="px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 w-full sm:w-32" />
                        </div>
                    </div>

                    <!-- Action buttons - Mobile: Full width, Desktop: Auto width -->
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button @click="applyDateFilter"
                            class="flex-1 sm:flex-none px-3 py-2 sm:py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            Áp dụng
                        </button>
                        <button @click="resetDateFilter"
                            class="flex-1 sm:flex-none px-3 py-2 sm:py-1 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 focus:outline-none focus:ring-1 focus:ring-gray-500">
                            Đặt lại
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="h-64 sm:h-80">
            <div v-if="!chartData || !chartData.apex_chart_data" class="flex justify-center items-center h-full">
                <p class="text-gray-500">Không có dữ liệu</p>
            </div>
            <div v-else ref="chartContainer" class="h-full"></div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick, onBeforeUnmount } from 'vue'
import { useDashboard } from '../../../composable/useDashboard'

const props = defineProps({
    data: {
        type: Object,
        default: () => ({})
    }
})

const { createRevenueChartOptions, getApexCharts, getRevenueByDateRange } = useDashboard()

const chartContainer = ref(null)
let chart = null

const startDate = ref('')
const endDate = ref('')

const chartData = ref(props.data)

const initializeDefaultDates = () => {
    const now = new Date()
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0)

    startDate.value = firstDay.toISOString().split('T')[0]
    endDate.value = lastDay.toISOString().split('T')[0]
}

const handleDateChange = () => {
    if (startDate.value && endDate.value && startDate.value > endDate.value) {
        alert('Ngày bắt đầu không thể lớn hơn ngày kết thúc')
        return
    }
}

const applyDateFilter = async () => {
    if (!startDate.value || !endDate.value) {
        alert('Vui lòng chọn khoảng thời gian')
        return
    }

    try {
        const response = await getRevenueByDateRange({
            start_date: startDate.value,
            end_date: endDate.value
        })

        if (response.success) {
            chartData.value = response.data
            emit('dateRangeChanged', response.data)
        }
    } catch (error) {
        console.error('Error applying date filter:', error)
    }
}

const resetDateFilter = () => {
    initializeDefaultDates()
    chartData.value = props.data
    emit('dateRangeChanged', props.data)
}

const emit = defineEmits(['dateRangeChanged'])

const initChart = async () => {
    if (!chartContainer.value || !chartData.value || !chartData.value.apex_chart_data) {
        return
    }

    try {
        const ApexCharts = await getApexCharts()
        if (!ApexCharts) {
            console.error('ApexCharts not available')
            return
        }

        if (chart) {
            chart.destroy()
            chart = null
        }

        const options = createRevenueChartOptions(chartData.value)
        chart = new ApexCharts(chartContainer.value, options)
        await chart.render()
    } catch (error) {
        console.error('Error initializing chart:', error)
    }
}

const destroyChart = () => {
    if (chart) {
        try {
            chart.destroy()
            chart = null
        } catch (error) {
            console.error('Error destroying chart:', error)
        }
    }
}

watch([() => props.data, () => chartData.value], async () => {
    await nextTick()
    setTimeout(() => {
        initChart()
    }, 100)
}, { deep: true })

onMounted(async () => {
    initializeDefaultDates()
    chartData.value = props.data

    await nextTick()
    setTimeout(() => {
        initChart()
    }, 100)
})

onBeforeUnmount(() => {
    destroyChart()
})
</script>