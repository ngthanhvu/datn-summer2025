<template>
    <div class="user-growth-chart">
        <div class="chart-header">
            <div class="chart-title">
                <h3 class="text-lg font-semibold text-gray-800">Tăng trưởng người dùng</h3>
                <p class="text-sm text-gray-600">Biểu đồ đường theo dõi tăng trưởng người dùng</p>
            </div>
            <div class="chart-actions">
                <div class="flex space-x-1">
                    <button @click="handlePeriodChange('0')" :class="[
                        'px-2 py-1 text-xs font-medium rounded-md transition-all duration-200',
                        selectedPeriod === '0'
                            ? 'bg-primary text-white shadow-md'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200 hover:shadow-sm'
                    ]">
                        Năm
                    </button>
                </div>
            </div>
        </div>

        <div class="stats-summary mb-3">
            <div class="grid grid-cols-1 gap-2">
                <div class="stat-item bg-blue-50 p-2 rounded-lg">
                    <div class="flex items-center">
                        <div class="stat-icon bg-blue-100 p-1.5 rounded-full mr-2">
                            <i class="fas fa-users text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Tổng người dùng</p>
                            <p class="text-sm font-semibold text-blue-700">{{ formatNumber(userData.total_users || 0) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="stat-item bg-green-50 p-2 rounded-lg">
                    <div class="flex items-center">
                        <div class="stat-icon bg-green-100 p-1.5 rounded-full mr-2">
                            <i class="fas fa-user-plus text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Tháng này</p>
                            <p class="text-sm font-semibold text-green-700">{{
                                formatNumber(userData.new_users_this_month || 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-container" ref="chartContainer"></div>

        <div class="chart-footer mt-3">
            <div class="flex flex-wrap items-center justify-center gap-2 text-xs text-gray-600">
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mr-1"></div>
                    <span>Mới</span>
                </div>
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                    <span>Tổng</span>
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

const { getUserGrowthStats, formatNumber, getApexCharts } = useDashboard()

const chartContainer = ref(null)
const selectedPeriod = ref('0')
const userData = ref({})
const loading = ref(false)
let chart = null

const fetchUserData = async (period = null) => {
    try {
        loading.value = true
        let params = {}

        if (period === '0') {
            params = { year: new Date().getFullYear() }
        } else if (period) {
            params = { year: parseInt(period) }
        } else {
            params = { year: new Date().getFullYear() }
        }

        const response = await getUserGrowthStats(params)

        if (response.success) {
            userData.value = response.data
            await nextTick()
            renderChart()
        }
    } catch (error) {
        console.error('Error fetching user growth data:', error)
    } finally {
        loading.value = false
    }
}

const renderChart = async () => {
    if (!chartContainer.value || !userData.value.apex_chart_data) {
        console.log('Cannot render chart:', {
            chartContainer: !!chartContainer.value,
            apexChartData: !!userData.value.apex_chart_data,
            userData: userData.value
        })
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
        }

        const options = createUserGrowthChartOptions(userData.value.apex_chart_data)
        chart = new ApexCharts(chartContainer.value, options)
        chart.render()
    } catch (error) {
        console.error('Error rendering user growth chart:', error)
    }
}

const handlePeriodChange = (period) => {
    selectedPeriod.value = period
    fetchUserData(period)
}

const createUserGrowthChartOptions = (data) => {
    return {
        series: data?.series || [],
        chart: {
            type: 'line',
            height: 220,
            toolbar: { show: false },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 600,
                animateGradually: {
                    enabled: true,
                    delay: 100
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 300
                }
            }
        },
        dataLabels: { enabled: false },
        stroke: {
            curve: 'smooth',
            width: 2,
            dashArray: [0, 0]
        },
        colors: ['#3B82F6', '#10B981'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.6,
                opacityTo: 0.1,
                stops: [0, 90, 100]
            }
        },
        markers: {
            size: 3,
            colors: ['#3B82F6', '#10B981'], // Chỉ còn 2 màu
            strokeColors: '#fff',
            strokeWidth: 1,
            hover: {
                size: 5
            }
        },
        xaxis: {
            categories: data?.categories || [],
            labels: {
                style: { colors: '#6b7280', fontSize: '10px' },
                rotate: 0,
                rotateAlways: false,
                maxHeight: 40
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
        yaxis: [
            {
                title: {
                    text: 'Số lượng',
                    style: { color: '#6b7280', fontSize: '10px' }
                },
                labels: {
                    style: { colors: '#6b7280', fontSize: '10px' },
                    formatter: (value) => formatNumber(value)
                },
                grid: {
                    color: '#E5E7EB',
                    borderColor: '#E5E7EB'
                }
            }
        ],
        grid: {
            borderColor: '#E5E7EB',
            strokeDashArray: 3,
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
                formatter: (value) => value
            },
            y: [
                {
                    formatter: (value) => `${formatNumber(value)} người dùng mới`,
                    title: {
                        formatter: () => 'Người dùng mới'
                    }
                },
                {
                    formatter: (value) => `${formatNumber(value)} tổng người dùng`,
                    title: {
                        formatter: () => 'Tổng người dùng'
                    }
                }
            ]
        },
        legend: {
            position: 'top',
            horizontalAlign: 'center',
            fontSize: '10px',
            labels: {
                colors: '#6b7280'
            }
        },
        responsive: [
            {
                breakpoint: 1024,
                options: {
                    chart: {
                        height: 200
                    },
                    legend: {
                        fontSize: '9px'
                    }
                }
            },
            {
                breakpoint: 768,
                options: {
                    chart: {
                        height: 180
                    },
                    legend: {
                        position: 'bottom',
                        fontSize: '9px'
                    },
                    xaxis: {
                        labels: {
                            rotate: 0,
                            fontSize: '9px'
                        }
                    }
                }
            }
        ]
    }
}

onMounted(() => {
    fetchUserData(selectedPeriod.value)
})

watch(() => props.data, (newData) => {
    if (newData && Object.keys(newData).length > 0) {
        userData.value = newData
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
.user-growth-chart {
    background: white;
    border-radius: 0.75rem;
    padding: 1rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.user-growth-chart:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transform: translateY(-2px);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.chart-title h3 {
    margin-bottom: 0.25rem;
    color: #1f2937;
    font-size: 1rem;
}

.chart-title p {
    color: #6b7280;
    font-size: 0.75rem;
}

.chart-actions .flex {
    gap: 0.25rem;
}

.chart-actions button {
    min-width: 40px;
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
    box-shadow: 0 2px 8px rgba(59, 183, 126, 0.3);
}

.chart-actions button.bg-primary:hover {
    box-shadow: 0 4px 12px rgba(59, 183, 126, 0.4);
    transform: translateY(-1px);
}

.stats-summary {
    border-bottom: 1px solid #E5E7EB;
    padding-bottom: 0.75rem;
    margin-bottom: 1rem;
}

.stat-item {
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.stat-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px -5px rgba(0, 0, 0, 0.15);
    border-color: #E5E7EB;
}

.stat-icon {
    transition: all 0.3s ease;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-item:hover .stat-icon {
    transform: scale(1.1) rotate(5deg);
}

.chart-container {
    min-height: 220px;
    position: relative;
    flex: 1;
}

.chart-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(59, 183, 126, 0.02) 0%, rgba(139, 92, 246, 0.02) 100%);
    border-radius: 0.5rem;
    pointer-events: none;
}

.chart-footer {
    border-top: 1px solid #E5E7EB;
    padding-top: 0.75rem;
    margin-top: 0.75rem;
}

.chart-footer .flex {
    gap: 0.5rem;
}

.chart-footer .flex>div {
    display: flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}

.chart-footer .flex>div:hover {
    background-color: #f9fafb;
    transform: translateX(1px);
}

.chart-footer .w-2 {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
    margin-right: 0.25rem;
}

@media (max-width: 1024px) {
    .chart-container {
        min-height: 200px;
    }

    .chart-actions .flex {
        gap: 0.25rem;
    }

    .chart-actions button {
        min-width: 35px;
        padding: 0.25rem 0.5rem;
        font-size: 0.625rem;
    }
}

@media (max-width: 768px) {
    .chart-header {
        flex-direction: column;
        gap: 0.75rem;
    }

    .chart-actions .flex {
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.25rem;
    }

    .chart-actions button {
        min-width: 30px;
        padding: 0.25rem;
        font-size: 0.625rem;
    }

    .stats-summary .grid {
        grid-template-columns: 1fr;
    }

    .chart-footer .flex {
        flex-direction: column;
        gap: 0.25rem;
    }

    .chart-footer .flex>div {
        justify-content: center;
    }

    .chart-container {
        min-height: 180px;
    }
}
</style>