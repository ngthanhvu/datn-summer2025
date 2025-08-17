<template>
    <div class="inventory-chart-skeleton">
        <div class="chart-header">
            <div class="chart-title">
                <div class="h-6 bg-gray-200 rounded w-48 mb-2 animate-pulse"></div>
                <div class="h-4 bg-gray-200 rounded w-44 animate-pulse"></div>
            </div>
            <div class="chart-actions">
                <div class="flex space-x-2">
                    <div class="h-10 bg-gray-200 rounded w-20 animate-pulse"></div>
                    <div class="h-10 bg-gray-200 rounded w-20 animate-pulse"></div>
                    <div class="h-10 bg-gray-200 rounded w-20 animate-pulse"></div>
                    <div class="h-10 bg-gray-200 rounded w-20 animate-pulse"></div>
                </div>
            </div>
        </div>

        <div class="stats-summary mb-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="stat-item bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <div class="flex items-center">
                        <div class="stat-icon bg-gray-200 p-2 rounded-full mr-3 w-10 h-10 animate-pulse"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-gray-200 rounded w-20 mb-2 animate-pulse"></div>
                            <div class="h-5 bg-gray-200 rounded w-24 animate-pulse"></div>
                        </div>
                    </div>
                </div>
                <div class="stat-item bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <div class="flex items-center">
                        <div class="stat-icon bg-gray-200 p-2 rounded-full mr-3 w-10 h-10 animate-pulse"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-gray-200 rounded w-20 mb-2 animate-pulse"></div>
                            <div class="h-5 bg-gray-200 rounded w-24 animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-container">
            <!-- Column chart skeleton for monthly view with 3 columns per month -->
            <div class="flex items-end justify-between h-64 px-4">
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-4 bg-blue-200 rounded-t animate-pulse" style="height: 50%"></div>
                        <div class="w-4 bg-green-200 rounded-t animate-pulse" style="height: 30%"></div>
                        <div class="w-4 bg-orange-200 rounded-t animate-pulse" style="height: 70%"></div>
                    </div>
                    <div class="h-3 bg-gray-200 rounded w-8 animate-pulse"></div>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-4 bg-blue-200 rounded-t animate-pulse" style="height: 70%"></div>
                        <div class="w-4 bg-green-200 rounded-t animate-pulse" style="height: 60%"></div>
                        <div class="w-4 bg-orange-200 rounded-t animate-pulse" style="height: 80%"></div>
                    </div>
                    <div class="h-3 bg-gray-200 rounded w-8 animate-pulse"></div>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-4 bg-blue-200 rounded-t animate-pulse" style="height: 40%"></div>
                        <div class="w-4 bg-green-200 rounded-t animate-pulse" style="height: 25%"></div>
                        <div class="w-4 bg-orange-200 rounded-t animate-pulse" style="height: 45%"></div>
                    </div>
                    <div class="h-3 bg-gray-200 rounded w-8 animate-pulse"></div>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-4 bg-blue-200 rounded-t animate-pulse" style="height: 80%"></div>
                        <div class="w-4 bg-green-200 rounded-t animate-pulse" style="height: 75%"></div>
                        <div class="w-4 bg-orange-200 rounded-t animate-pulse" style="height: 90%"></div>
                    </div>
                    <div class="h-3 bg-gray-200 rounded w-8 animate-pulse"></div>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-4 bg-blue-200 rounded-t animate-pulse" style="height: 35%"></div>
                        <div class="w-4 bg-green-200 rounded-t animate-pulse" style="height: 20%"></div>
                        <div class="w-4 bg-orange-200 rounded-t animate-pulse" style="height: 30%"></div>
                    </div>
                    <div class="h-3 bg-gray-200 rounded w-8 animate-pulse"></div>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-4 bg-blue-200 rounded-t animate-pulse" style="height: 65%"></div>
                        <div class="w-4 bg-green-200 rounded-t animate-pulse" style="height: 55%"></div>
                        <div class="w-4 bg-orange-200 rounded-t animate-pulse" style="height: 75%"></div>
                    </div>
                    <div class="h-3 bg-gray-200 rounded w-8 animate-pulse"></div>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-4 bg-blue-200 rounded-t animate-pulse" style="height: 45%"></div>
                        <div class="w-4 bg-green-200 rounded-t animate-pulse" style="height: 35%"></div>
                        <div class="w-4 bg-orange-200 rounded-t animate-pulse" style="height: 55%"></div>
                    </div>
                    <div class="h-3 bg-gray-200 rounded w-8 animate-pulse"></div>
                </div>
            </div>
        </div>

        <div class="chart-footer mt-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-gray-200 rounded-full mr-2 animate-pulse"></div>
                    <div class="h-4 bg-gray-200 rounded w-16 animate-pulse"></div>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-gray-200 rounded-full mr-2 animate-pulse"></div>
                    <div class="h-4 bg-gray-200 rounded w-16 animate-pulse"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// Component skeleton cho InventoryChart với column chart
</script>

<style scoped>
.inventory-chart-skeleton {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
}

.inventory-chart-skeleton:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transform: translateY(-2px);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
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

.chart-container {
    min-height: 250px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
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

.chart-footer .flex {
    gap: 1rem;
}

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
}

@media (max-width: 768px) {
    .chart-header {
        flex-direction: column;
        gap: 1rem;
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

    .chart-container .flex {
        flex-wrap: wrap;
        gap: 1rem;
    }

    .chart-container .flex>div {
        flex: 0 0 calc(50% - 0.5rem);
    }
}
</style>