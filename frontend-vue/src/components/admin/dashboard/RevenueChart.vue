<template>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold mb-4">Doanh thu 7 ngày qua</h3>
        <div class="h-80">
            <div v-if="!data || !data.apex_chart_data" class="flex justify-center items-center h-full">
                <p class="text-gray-500">Không có dữ liệu</p>
            </div>
            <div v-else ref="chartContainer" class="h-full"></div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import { useDashboard } from '../../../composable/useDashboard'

const props = defineProps({
    data: {
        type: Object,
        default: () => ({})
    }
})

const { createRevenueChartOptions, getApexCharts } = useDashboard()

const chartContainer = ref(null)
let chart = null

const initChart = async () => {
    if (!props.data || !props.data.apex_chart_data || !chartContainer.value) return

    const ApexCharts = await getApexCharts()
    if (!ApexCharts) {
        console.error('ApexCharts not available')
        return
    }

    const options = createRevenueChartOptions(props.data)

    // Destroy existing chart if any
    if (chart) {
        chart.destroy()
    }

    chart = new ApexCharts(chartContainer.value, options)
    chart.render()
}

// Watch for data changes
watch(() => props.data, () => {
    nextTick(() => {
        initChart()
    })
}, { deep: true })

onMounted(() => {
    initChart()
})
</script>