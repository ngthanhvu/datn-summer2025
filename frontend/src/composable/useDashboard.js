import axios from "axios";

const API = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL + "/api",
    timeout: 10000,
});

API.interceptors.request.use((config) => {
    const token = localStorage.getItem("token");
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
});

export const useDashboard = () => {
    const handleError = (err, msg) => {
        console.error(msg, err.response?.data || err.message);
        throw err;
    };

    // ---------- API CALLS ----------
    const getStats = (params = {}) =>
        API.get("/dashboard/stats", { params }).then((r) => r.data).catch((e) => handleError(e, "Không thể lấy thống kê dashboard"));

    const getMonthlyRevenue = (params = {}) =>
        API.get("/dashboard/revenue", { params }).then((r) => r.data).catch((e) => handleError(e, "Không thể lấy thống kê doanh thu"));

    const getYearlyRevenue = (params = {}) =>
        API.get("/dashboard/revenue/yearly", { params }).then((r) => r.data).catch((e) => handleError(e, "Không thể lấy thống kê doanh thu năm"));

    // Thêm function mới cho lọc theo khoảng thời gian
    const getRevenueByDateRange = (params = {}) =>
        API.get("/dashboard/revenue-by-date-range", { params }).then((r) => r.data).catch((e) => handleError(e, "Không thể lấy thống kê doanh thu theo khoảng thời gian"));

    const getMonthlyOrders = (params = {}) =>
        API.get("/dashboard/orders", { params }).then((r) => r.data).catch((e) => handleError(e, "Không thể lấy thống kê đơn hàng"));

    const getOrdersByStatus = (params = {}) =>
        API.get("/dashboard/orders/status", { params }).then((r) => r.data).catch((e) => handleError(e, "Không thể lấy thống kê theo trạng thái"));

    const getCustomersStats = () =>
        API.get("/dashboard/customers").then((r) => r.data).catch((e) => handleError(e, "Không thể lấy thống kê khách hàng"));

    const getProductsStats = () =>
        API.get("/dashboard/products").then((r) => r.data).catch((e) => handleError(e, "Không thể lấy thống kê sản phẩm"));

    const getRecentOrders = (params = {}) =>
        API.get("/dashboard/recent-orders", { params }).then((r) => r.data).catch((e) => handleError(e, "Không thể lấy đơn hàng gần đây"));

    const getTopSelling = (params = {}) =>
        API.get("/dashboard/top-selling", { params })
            .then((r) => r.data)
            .catch((e) => handleError(e, "Không thể lấy top sản phẩm bán chạy"));

    const getInventoryStats = (params = {}) =>
        API.get("/dashboard/inventory", { params })
            .then((r) => r.data)
            .catch((e) => handleError(e, "Không thể lấy thống kê xuất nhập kho"));

    const getUserGrowthStats = (params = {}) =>
        API.get("/dashboard/user-growth", { params })
            .then((r) => r.data)
            .catch((e) => handleError(e, "Không thể lấy thống kê tăng trưởng người dùng"));

    // ---------- FORMAT HELPERS ----------
    const formatCurrency = (v) =>
        new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(v);

    const formatNumber = (n) =>
        new Intl.NumberFormat("vi-VN").format(n);

    const getStatusColor = (status) =>
    ({
        pending: "#FFA500",
        processing: "#3498DB",
        shipping: "#9B59B6",
        delivered: "#27AE60",
        completed: "#2ECC71",
        cancelled: "#E74C3C",
        returned: "#95A5A6",
    }[status] || "#95A5A6");

    const getStatusName = (status) =>
    ({
        pending: "Chờ xử lý",
        processing: "Đang xử lý",
        shipping: "Đang giao hàng",
        delivered: "Đã giao hàng",
        completed: "Hoàn thành",
        cancelled: "Đã hủy",
        returned: "Đã trả hàng",
    }[status] || status);

    // ---------- CHART OPTIONS ----------
    const createRevenueChartOptions = (data) => ({
        series: data?.apex_chart_data?.series || [],
        chart: { type: "area", height: 300, toolbar: { show: false } },
        dataLabels: { enabled: false },
        stroke: { curve: "smooth", width: 2 },
        colors: ["#3bb77e", "#3498db"],
        fill: {
            type: "gradient",
            gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.2, stops: [0, 90, 100] },
        },
        xaxis: {
            categories: data?.apex_chart_data?.categories || [],
            labels: { style: { colors: "#6b7280" } },
        },
        yaxis: [
            {
                title: { text: "Doanh thu (VNĐ)", style: { color: "#6b7280" } },
                labels: {
                    formatter: (v) =>
                        new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND", minimumFractionDigits: 0 }).format(v),
                    style: { colors: "#6b7280" },
                },
            },
            {
                opposite: true,
                title: { text: "Số đơn hàng", style: { color: "#6b7280" } },
                labels: { style: { colors: "#6b7280" } },
            },
        ],
        tooltip: {
            y: [
                { formatter: (v) => formatCurrency(v) },
                { formatter: (v) => `${v} đơn hàng` },
            ],
        },
        legend: { position: "top", horizontalAlign: "right" },
    });

    const createOrdersStatusChartOptions = (data) => ({
        series: data?.apex_chart_data?.series || [],
        chart: { type: "donut", height: 300 },
        labels: data?.apex_chart_data?.labels || [],
        colors: data?.apex_chart_data?.colors || [],
        dataLabels: {
            enabled: true,
            formatter: (val, opts) => opts.w.globals.seriesTotals[opts.seriesIndex],
        },
        plotOptions: {
            pie: {
                donut: {
                    size: "60%",
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: "Tổng đơn hàng",
                            formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0),
                        },
                    },
                },
            },
        },
        tooltip: { y: { formatter: (v) => `${v} đơn hàng` } },
        legend: { position: "bottom" },
    });

    const createBarChartOptions = (data, title = "Biểu đồ cột") => ({
        series: [{ name: title, data: data || [] }],
        chart: { type: "bar", height: 300, toolbar: { show: false } },
        plotOptions: { bar: { horizontal: false, columnWidth: "55%", endingShape: "rounded" } },
        dataLabels: { enabled: false },
        stroke: { show: true, width: 2, colors: ["transparent"] },
        xaxis: { categories: [], labels: { style: { colors: "#6b7280" } } },
        yaxis: {
            title: { text: title, style: { color: "#6b7280" } },
            labels: { style: { colors: "#6b7280" } },
        },
        fill: { opacity: 1 },
        tooltip: { y: { formatter: (val) => val } },
    });

    const getApexCharts = async () => {
        try {
            const ApexCharts = await import("apexcharts");
            return ApexCharts.default || ApexCharts;
        } catch (e) {
            console.error("Lỗi import ApexCharts:", e);
            return null;
        }
    };

    return {
        getStats,
        getMonthlyRevenue,
        getYearlyRevenue,
        getRevenueByDateRange, // Thêm vào return
        getMonthlyOrders,
        getOrdersByStatus,
        getCustomersStats,
        getProductsStats,
        getRecentOrders,
        getTopSelling,
        getInventoryStats,
        getUserGrowthStats,
        formatCurrency,
        formatNumber,
        getStatusColor,
        getStatusName,
        createRevenueChartOptions,
        createOrdersStatusChartOptions,
        createBarChartOptions,
        getApexCharts,
    };
};
