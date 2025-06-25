<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <h3 class="tw-font-semibold tw-mb-4">Doanh thu 7 ngày qua</h3>
        <div class="tw-h-80">
            <div v-if="!data || !data.apex_chart_data" class="tw-flex tw-justify-center tw-items-center tw-h-full">
                <p class="tw-text-gray-500">Không có dữ liệu</p>
            </div>
            <div v-else ref="chartContainer" class="tw-h-full"></div>
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