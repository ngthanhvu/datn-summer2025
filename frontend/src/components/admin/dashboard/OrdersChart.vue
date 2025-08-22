<template>
    <div class="bg-white rounded-lg shadow px-8 py-9.5">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold">Đơn hàng theo trạng thái</h3>
            <select v-model="selectedPeriod" @change="handlePeriodChange"
                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                <option value="7">1 tuần</option>
                <option value="30">1 tháng</option>
                <option value="365">1 năm</option>
            </select>
        </div>
        <div class="h-80">
            <div v-if="loading" class="flex justify-center items-center h-full">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            </div>
            <div v-else-if="!data || !data.apex_chart_data" class="flex justify-center items-center h-full">
                <p class="text-gray-500">Không có dữ liệu</p>
            </div>
            <div v-else ref="chartContainer" class="h-full"></div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'

const props = defineProps({
    data: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['period-change'])

import { useDashboard } from '../../../composable/useDashboard'

const { createOrdersStatusChartOptions, getApexCharts } = useDashboard()

const chartContainer = ref(null)
const selectedPeriod = ref('30') // Mặc định 1 tháng
const loading = ref(false)
let chart = null

const handlePeriodChange = async () => {
    loading.value = true
    try {
        emit('period-change', selectedPeriod.value)
    } catch (error) {
        console.error('Lỗi khi thay đổi thời gian:', error)
    } finally {
        loading.value = false
    }
}

const initChart = async () => {
    if (!props.data || !props.data.apex_chart_data || !chartContainer.value) return

    const ApexCharts = await getApexCharts()
    if (!ApexCharts) {
        console.error('ApexCharts not available')
        return
    }

    const options = createOrdersStatusChartOptions(props.data)

    if (chart) {
        chart.destroy()
    }

    chart = new ApexCharts(chartContainer.value, options)
    chart.render()
}

watch(() => props.data, () => {
    nextTick(() => {
        initChart()
    })
}, { deep: true })

onMounted(() => {
    initChart()
})
</script>