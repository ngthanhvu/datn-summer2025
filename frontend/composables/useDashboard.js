import axios from 'axios'
import Swal from 'sweetalert2'

export const useDashboard = () => {
    const config = useRuntimeConfig()
    const baseURL = `${config.public.apiBaseUrl}/api`

    // Lấy thống kê tổng quan
    const getStats = async (params = {}) => {
        try {
            const response = await axios.get(`${baseURL}/dashboard/stats`, {
                params,
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Error fetching dashboard stats:', error)
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Không thể lấy thống kê dashboard'
            })
            throw error
        }
    }

    // Lấy thống kê doanh thu theo tháng
    const getMonthlyRevenue = async (params = {}) => {
        try {
            const response = await axios.get(`${baseURL}/dashboard/revenue`, {
                params,
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Error fetching monthly revenue:', error)
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Không thể lấy thống kê doanh thu'
            })
            throw error
        }
    }

    // Lấy thống kê doanh thu theo năm
    const getYearlyRevenue = async (params = {}) => {
        try {
            const response = await axios.get(`${baseURL}/dashboard/revenue/yearly`, {
                params,
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Error fetching yearly revenue:', error)
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Không thể lấy thống kê doanh thu năm'
            })
            throw error
        }
    }

    // Lấy thống kê đơn hàng theo tháng
    const getMonthlyOrders = async (params = {}) => {
        try {
            const response = await axios.get(`${baseURL}/dashboard/orders`, {
                params,
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Error fetching monthly orders:', error)
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Không thể lấy thống kê đơn hàng'
            })
            throw error
        }
    }

    // Lấy thống kê đơn hàng theo trạng thái
    const getOrdersByStatus = async (params = {}) => {
        try {
            const response = await axios.get(`${baseURL}/dashboard/orders/status`, {
                params,
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Error fetching orders by status:', error)
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Không thể lấy thống kê đơn hàng theo trạng thái'
            })
            throw error
        }
    }

    // Lấy thống kê khách hàng
    const getCustomersStats = async () => {
        try {
            const response = await axios.get(`${baseURL}/dashboard/customers`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Error fetching customers stats:', error)
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Không thể lấy thống kê khách hàng'
            })
            throw error
        }
    }

    // Lấy thống kê sản phẩm
    const getProductsStats = async () => {
        try {
            const response = await axios.get(`${baseURL}/dashboard/products`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Error fetching products stats:', error)
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Không thể lấy thống kê sản phẩm'
            })
            throw error
        }
    }

    // Lấy đơn hàng gần đây
    const getRecentOrders = async (params = {}) => {
        try {
            const response = await axios.get(`${baseURL}/dashboard/recent-orders`, {
                params,
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Error fetching recent orders:', error)
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Không thể lấy đơn hàng gần đây'
            })
            throw error
        }
    }

    // Format số tiền
    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount)
    }

    // Format số lượng
    const formatNumber = (number) => {
        return new Intl.NumberFormat('vi-VN').format(number)
    }

    // Lấy màu sắc cho trạng thái đơn hàng
    const getStatusColor = (status) => {
        const statusColors = {
            'pending': '#FFA500',
            'processing': '#3498DB',
            'shipping': '#9B59B6',
            'delivered': '#27AE60',
            'completed': '#2ECC71',
            'cancelled': '#E74C3C',
            'returned': '#95A5A6'
        }
        return statusColors[status] || '#95A5A6'
    }

    // Lấy tên trạng thái đơn hàng
    const getStatusName = (status) => {
        const statusNames = {
            'pending': 'Chờ xử lý',
            'processing': 'Đang xử lý',
            'shipping': 'Đang giao hàng',
            'delivered': 'Đã giao hàng',
            'completed': 'Hoàn thành',
            'cancelled': 'Đã hủy',
            'returned': 'Đã trả hàng'
        }
        return statusNames[status] || status
    }

    // Tạo options cho biểu đồ doanh thu
    const createRevenueChartOptions = (data) => {
        return {
            series: data?.apex_chart_data?.series || [],
            chart: {
                type: 'area',
                height: 300,
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            colors: ['#3bb77e', '#3498db'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.2,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: data?.apex_chart_data?.categories || [],
                labels: {
                    style: {
                        colors: '#6b7280'
                    }
                }
            },
            yaxis: [
                {
                    title: {
                        text: 'Doanh thu (VNĐ)',
                        style: {
                            color: '#6b7280'
                        }
                    },
                    labels: {
                        formatter: function (value) {
                            return new Intl.NumberFormat('vi-VN', {
                                style: 'currency',
                                currency: 'VND',
                                minimumFractionDigits: 0
                            }).format(value)
                        },
                        style: {
                            colors: '#6b7280'
                        }
                    }
                },
                {
                    opposite: true,
                    title: {
                        text: 'Số đơn hàng',
                        style: {
                            color: '#6b7280'
                        }
                    },
                    labels: {
                        style: {
                            colors: '#6b7280'
                        }
                    }
                }
            ],
            tooltip: {
                y: [
                    {
                        formatter: function (value) {
                            return new Intl.NumberFormat('vi-VN', {
                                style: 'currency',
                                currency: 'VND'
                            }).format(value)
                        }
                    },
                    {
                        formatter: function (value) {
                            return value + ' đơn hàng'
                        }
                    }
                ]
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            }
        }
    }

    // Tạo options cho biểu đồ đơn hàng theo trạng thái
    const createOrdersStatusChartOptions = (data) => {
        return {
            series: data?.apex_chart_data?.series || [],
            chart: {
                type: 'donut',
                height: 300
            },
            labels: data?.apex_chart_data?.labels || [],
            colors: data?.apex_chart_data?.colors || [],
            dataLabels: {
                enabled: true,
                formatter: function (val, opts) {
                    return opts.w.globals.seriesTotals[opts.seriesIndex]
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '60%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Tổng đơn hàng',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                }
                            }
                        }
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (value) {
                        return value + ' đơn hàng'
                    }
                }
            },
            legend: {
                position: 'bottom'
            }
        }
    }

    // Tạo options cho biểu đồ cột
    const createBarChartOptions = (data, title = 'Biểu đồ cột') => {
        return {
            series: [{
                name: title,
                data: data || []
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded'
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: [],
                labels: {
                    style: {
                        colors: '#6b7280'
                    }
                }
            },
            yaxis: {
                title: {
                    text: title,
                    style: {
                        color: '#6b7280'
                    }
                },
                labels: {
                    style: {
                        colors: '#6b7280'
                    }
                }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val
                    }
                }
            }
        }
    }

    // Function để import ApexCharts an toàn
    const getApexCharts = async () => {
        if (process.client) {
            try {
                const ApexCharts = await import('apexcharts')
                return ApexCharts.default || ApexCharts
            } catch (error) {
                console.error('Error importing ApexCharts:', error)
                return null
            }
        }
        return null
    }

    return {
        getStats,
        getMonthlyRevenue,
        getYearlyRevenue,
        getMonthlyOrders,
        getOrdersByStatus,
        getCustomersStats,
        getProductsStats,
        getRecentOrders,
        formatCurrency,
        formatNumber,
        getStatusColor,
        getStatusName,
        createRevenueChartOptions,
        createOrdersStatusChartOptions,
        createBarChartOptions,
        getApexCharts
    }
}
