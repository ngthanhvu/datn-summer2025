export default defineNuxtPlugin(() => {
    // ApexCharts plugin chỉ chạy ở client-side
    if (process.client) {
        // Import ApexCharts khi cần thiết
        console.log('ApexCharts plugin loaded on client-side')
    }
}) 