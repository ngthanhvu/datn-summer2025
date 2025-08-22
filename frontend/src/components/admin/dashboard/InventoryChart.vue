<template>
    <div class="inventory-chart px-6 py-7">
        <div class="chart-header">
            <div class="chart-title">
                <h3 class="text-lg font-semibold text-gray-800">Thống kê xuất nhập kho</h3>
                <p class="text-sm text-gray-600">Biểu đồ cột theo dõi hoạt động kho hàng (mặc định: 12 tháng gần nhất)
                </p>
            </div>
            <div class="chart-actions">
                <div class="flex space-x-2">
                    <button @click="handlePeriodChange('yearly')" :class="[
                        'px-2 text-sm font-medium rounded-md transition-all duration-200',
                        selectedPeriod === 'yearly'
                            ? 'bg-primary text-white shadow-md'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200 hover:shadow-sm'
                    ]">
                        12 tháng
                    </button>
                    <button @click="handlePeriodChange('0')" :class="[
                        'px-2 text-sm font-medium rounded-md transition-all duration-200',
                        selectedPeriod === '0'
                            ? 'bg-primary text-white shadow-md'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200 hover:shadow-sm'
                    ]">
                        Tháng
                    </button>
                    <button @click="handlePeriodChange('7')" :class="[
                        'px-2 text-sm font-medium rounded-md transition-all duration-200',
                        selectedPeriod === '7'
                            ? 'bg-primary text-white shadow-md'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200 hover:shadow-sm'
                    ]">
                        7 ngày
                    </button>
                </div>
            </div>
        </div>

        <div class="stats-summary mb-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="stat-item bg-green-50 p-3 rounded-lg">
                    <div class="flex items-center">
                        <div class="stat-icon bg-green-100 p-2 rounded-full mr-3">
                            <i class="fas fa-arrow-down text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Tổng nhập kho</p>
                            <p class="text-lg font-semibold text-green-700">{{
                                formatNumber(inventoryData.total_import_quantity || 0) }} sản phẩm</p>
                        </div>
                    </div>
                </div>
                <div class="stat-item bg-red-50 p-3 rounded-lg">
                    <div class="flex items-center">
                        <div class="stat-icon bg-red-100 p-2 rounded-full mr-3">
                            <i class="fas fa-arrow-up text-red-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Tổng xuất kho</p>
                            <p class="text-lg font-semibold text-red-700">{{
                                formatNumber(inventoryData.total_export_quantity || 0) }} sản phẩm</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-container" ref="chartContainer"></div>

        <div class="chart-footer mt-4 flex items-center justify-center">
            <div class="flex items-center text-sm text-gray-600">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                    <span>Nhập kho</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span>Xuất kho</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-orange-500 rounded-full mr-2"></div>
                    <span>Tồn kho</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useDashboard } from '../../../composable/useDashboard'

const props = defineProps({
    data: {
        type: Object,
        default: () => ({})
    }
})

const { getInventoryStats, formatNumber, getApexCharts } = useDashboard()

const chartContainer = ref(null)
const selectedPeriod = ref('yearly')
const inventoryData = ref({})
const loading = ref(false)
let chart = null

const fetchInventoryData = async (period = null) => {
    try {
        loading.value = true
        let params = {}

        if (period === 'yearly') {
            params = { yearly: true }
        } else if (period) {
            params = { period: parseInt(period) }
        }

        const response = await getInventoryStats(params)

        if (response.success) {
            inventoryData.value = response.data
            await nextTick()
            renderChart()
        }
    } catch (error) {
        console.error('Error fetching inventory data:', error)
    } finally {
        loading.value = false
    }
}

const renderChart = async () => {
    if (!chartContainer.value || !inventoryData.value.apex_chart_data) return

    try {
        const ApexCharts = await getApexCharts()
        if (!ApexCharts) return

        if (chart) {
            chart.destroy()
        }

        const options = createInventoryChartOptions(inventoryData.value.apex_chart_data)
        chart = new ApexCharts(chartContainer.value, options)
        chart.render()
    } catch (error) {
        console.error('Error rendering inventory chart:', error)
    }
}

const handlePeriodChange = (period) => {
    selectedPeriod.value = period
    fetchInventoryData(period)
}

const createInventoryChartOptions = (data) => {
    const isYearly = selectedPeriod.value === 'yearly'
    const monthNames = [
        'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
        'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
    ]

    return {
        series: data?.series || [],
        chart: {
            type: 'bar',
            height: 250,
            toolbar: { show: false },
            stacked: false,
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800,
                animateGradually: {
                    enabled: true,
                    delay: 150
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 350
                }
            }
        },
        dataLabels: { enabled: false },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        colors: ['#3B82F6', '#10B981', '#F59E0B'], // Blue, Green, Orange như trong hình
        fill: {
            opacity: 0.8,
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.25,
                gradientToColors: undefined,
                inverseColors: true,
                opacityFrom: 1,
                opacityTo: 0.85,
                stops: [50, 0, 100]
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '70%',
                endingShape: 'rounded',
                borderRadius: 4,
                dataLabels: {
                    position: 'top'
                }
            }
        },
        xaxis: {
            categories: data?.categories || [],
            labels: {
                style: { colors: '#6b7280' },
                rotate: isYearly ? 0 : -45,
                rotateAlways: false
            },
            axisBorder: {
                show: true,
                color: '#E5E7EB'
            },
            axisTicks: {
                show: true,
                color: '#E5E7EB'
            }
        },
        yaxis: {
            title: {
                text: 'Số lượng sản phẩm',
                style: { color: '#6b7280' }
            },
            labels: {
                style: { colors: '#6b7280' },
                formatter: (value) => formatNumber(value)
            },
            grid: {
                color: '#E5E7EB',
                borderColor: '#E5E7EB'
            }
        },
        grid: {
            borderColor: '#E5E7EB',
            strokeDashArray: 5,
            xaxis: {
                lines: {
                    show: true
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            }
        },
        tooltip: {
            theme: 'light',
            shared: true,
            intersect: false,
            x: {
                formatter: (value) => {
                    if (isYearly) {
                        return value
                    }
                    return value
                }
            },
            y: [
                {
                    formatter: (value) => `${formatNumber(value)} sản phẩm nhập kho`,
                    title: {
                        formatter: () => 'Nhập kho'
                    }
                },
                {
                    formatter: (value) => `${formatNumber(value)} sản phẩm xuất kho`,
                    title: {
                        formatter: () => 'Xuất kho'
                    }
                },
                {
                    formatter: (value) => `${formatNumber(value)} sản phẩm tồn kho`,
                    title: {
                        formatter: () => 'Tồn kho'
                    }
                }
            ]
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            labels: {
                colors: '#6b7280'
            }
        },
        responsive: [
            {
                breakpoint: 1024,
                options: {
                    chart: {
                        height: 220
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '75%'
                        }
                    }
                }
            },
            {
                breakpoint: 768,
                options: {
                    chart: {
                        height: 200
                    },
                    legend: {
                        position: 'bottom'
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '85%'
                        }
                    },
                    xaxis: {
                        labels: {
                            rotate: 0
                        }
                    }
                }
            }
        ]
    }
}

onMounted(() => {
    fetchInventoryData(selectedPeriod.value)
})

watch(() => props.data, (newData) => {
    if (newData && Object.keys(newData).length > 0) {
        inventoryData.value = newData
        nextTick(() => {
            renderChart()
        })
    }
}, { deep: true })

onUnmounted(() => {
    if (chart) {
        chart.destroy()
    }
})
</script>

<style scoped>
.inventory-chart {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
}

.inventory-chart:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transform: translateY(-2px);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}

.chart-title h3 {
    margin-bottom: 0.25rem;
    color: #1f2937;
}

.chart-title p {
    color: #6b7280;
}

.chart-actions select {
    background-color: white;
    transition: all 0.2s;
    border: 1px solid #d1d5db;
}

.chart-actions select:hover {
    border-color: #3bb77e;
    box-shadow: 0 1px 3px rgba(59, 183, 126, 0.1);
}

.chart-actions select:focus {
    border-color: #3bb77e;
    box-shadow: 0 0 0 3px rgba(59, 183, 126, 0.1);
    outline: none;
}

.stats-summary {
    border-bottom: 1px solid #E5E7EB;
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
}

.stat-item {
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.stat-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px -8px rgba(0, 0, 0, 0.15);
    border-color: #E5E7EB;
}

.stat-icon {
    transition: all 0.3s ease;
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-item:hover .stat-icon {
    transform: scale(1.1) rotate(5deg);
}

.chart-container {
    min-height: 250px;
    position: relative;
}

.chart-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(59, 183, 126, 0.02) 0%, rgba(239, 68, 68, 0.02) 100%);
    border-radius: 0.5rem;
    pointer-events: none;
}

.chart-footer {
    border-top: 1px solid #E5E7EB;
    padding-top: 1rem;
    margin-top: 1.5rem;
}

/* .chart-footer .flex {
    gap: 1rem;
} */

.chart-footer .flex>div {
    display: flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
}

.chart-footer .flex>div:hover {
    background-color: #f9fafb;
    transform: translateX(2px);
}

.chart-footer .w-3 {
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    margin-right: 0.5rem;
}

@media (max-width: 1024px) {
    .chart-container {
        min-height: 220px;
    }

    .chart-actions .flex {
        gap: 0.25rem;
    }

    .chart-actions button {
        min-width: 70px;
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
    }
}

@media (max-width: 768px) {
    .chart-header {
        flex-direction: column;
        gap: 1rem;
    }

    .chart-actions .flex {
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .chart-actions button {
        min-width: 60px;
        padding: 0.5rem;
        font-size: 0.75rem;
    }

    .stats-summary .grid {
        grid-template-columns: 1fr;
    }

    .chart-footer .flex {
        flex-direction: column;
        gap: 0.5rem;
    }

    .chart-footer .flex>div {
        justify-content: center;
    }

    .chart-container {
        min-height: 200px;
    }
}

/* Custom scrollbar for select */
.chart-actions select::-webkit-scrollbar {
    width: 6px;
}

.chart-actions select::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.chart-actions select::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.chart-actions select::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.chart-actions .flex {
    gap: 0.5rem;
}

.chart-actions button {
    min-width: 80px;
    font-weight: 500;
    border: 1px solid transparent;
    position: relative;
    overflow: hidden;
}

.chart-actions button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.chart-actions button:hover::before {
    left: 100%;
}

.chart-actions button:active {
    transform: translateY(1px);
}

.chart-actions button.bg-primary {
    box-shadow: 0 4px 12px rgba(59, 183, 126, 0.3);
}

.chart-actions button.bg-primary:hover {
    box-shadow: 0 6px 16px rgba(59, 183, 126, 0.4);
    transform: translateY(-1px);
}
</style>