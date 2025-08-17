<template>
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Danh sách phiếu nhập/xuất</h1>
        <p class="text-gray-600">Quản lý và theo dõi các phiếu nhập/xuất kho</p>
    </div>

    <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="ml-2 text-gray-600">Đang tải dữ liệu...</span>
    </div>

    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Desktop table -->
        <table class="min-w-full divide-y divide-gray-200 hidden md:table">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Mã phiếu
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Loại
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Người tạo
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Ngày tạo
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Số sản phẩm
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Ghi chú
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Thao tác
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(movement, index) in paginatedMovements" :key="movement.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-medium text-gray-900">#{{ (currentPage - 1) * itemsPerPage + index + 1
                            }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="[
                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                            movement.type === 'import'
                                ? 'bg-green-100 text-green-800 border border-green-500'
                                : 'bg-red-100 text-red-800 border border-red-500',
                        ]">
                            {{ movement.type === 'import' ? 'Nhập kho' : 'Xuất kho' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-900">{{ movement.user?.username || 'N/A' }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-900">{{ formatDate(movement.created_at) }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-900">{{ movement.items?.length || 0 }} sản phẩm</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-900">{{ movement.note || '-' }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button @click="viewDetails(movement)"
                            class="text-blue-600 hover:text-blue-900 mr-3 cursor-pointer">
                            Xem chi tiết
                        </button>
                        <button @click="printReceipt(movement)"
                            class="text-green-600 hover:text-green-900 cursor-pointer">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                </path>
                            </svg>
                            In phiếu
                        </button>
                    </td>
                </tr>
                <tr v-if="paginatedMovements.length === 0">
                    <td colspan="7" class="text-center px-6 py-4 whitespace-nowrap">
                        <div class="flex justify-center text-center">
                            <span class="text-sm font-medium text-gray-500">Không có dữ liệu</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Mobile card list -->
        <div v-if="paginatedMovements.length > 0" class="space-y-3 p-4 md:hidden">
            <div v-for="(movement, index) in paginatedMovements" :key="'m-' + movement.id"
                class="rounded-lg border border-gray-200 p-3">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold">#{{ (currentPage - 1) * itemsPerPage + index + 1 }}</div>
                        <div class="text-xs text-gray-500">{{ movement.user?.username || 'N/A' }}</div>
                    </div>
                    <span :class="[
                        'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                        movement.type === 'import'
                            ? 'bg-green-100 text-green-800'
                            : 'bg-red-100 text-red-800'
                    ]">
                        {{ movement.type === 'import' ? 'Nhập kho' : 'Xuất kho' }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="text-gray-500">Ngày tạo</div>
                    <div class="text-right">{{ formatDate(movement.created_at) }}</div>
                    <div class="text-gray-500">Số sản phẩm</div>
                    <div class="text-right">{{ movement.items?.length || 0 }} sản phẩm</div>
                    <div class="text-gray-500">Ghi chú</div>
                    <div class="text-right">{{ movement.note || '-' }}</div>
                </div>
                <div class="flex gap-2 mt-3">
                    <button @click="viewDetails(movement)"
                        class="flex-1 text-blue-600 hover:text-blue-900 text-xs py-1 px-2 border border-blue-200 rounded hover:bg-blue-50">
                        Xem chi tiết
                    </button>
                    <button @click="printReceipt(movement)"
                        class="flex-1 text-green-600 hover:text-green-900 text-xs py-1 px-2 border border-green-200 rounded hover:bg-green-50">
                        In phiếu
                    </button>
                </div>
            </div>
        </div>
        <div v-if="paginatedMovements.length === 0" class="text-center text-gray-500 py-4 md:hidden">
            Không có dữ liệu
        </div>
    </div>

    <!-- Pagination -->
    <div v-if="!loading && totalPages > 1"
        class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-6 gap-3">
        <div class="text-sm text-gray-600 text-center sm:text-left">
            Hiển thị {{ paginatedMovements.length }} trên tổng số {{ stockMovements.length }} bản ghi
        </div>
        <div class="flex justify-center gap-2">
            <button :disabled="currentPage === 1" @click="goToPage(currentPage - 1)"
                class="px-3 py-1 border border-gray-400 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer">
                <i class="fas fa-chevron-left"></i>
            </button>
            <span class="px-3 py-1">
                Trang {{ currentPage }} / {{ totalPages }}
            </span>
            <button :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)"
                class="px-3 py-1 border border-gray-400 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <!-- Movement Details Modal -->
    <div v-if="showDetailsModal"
        class="fixed inset-0 backdrop-blur-sm bg-black/30 overflow-y-auto h-full w-full z-50 p-4">
        <div class="relative top-10 mx-auto p-4 sm:p-5 w-full sm:w-3/4 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Chi tiết phiếu #{{ selectedMovement?.id }}
                    </h3>
                    <button @click="closeDetailsModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div v-if="selectedMovement" class="mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Loại giao dịch</label>
                            <span :class="[
                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1',
                                selectedMovement.type === 'import'
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-red-100 text-red-800'
                            ]">
                                {{ selectedMovement.type === 'import' ? 'Nhập kho' : 'Xuất kho' }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Người tạo</label>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedMovement.user?.username || 'N/A'
                            }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ngày tạo</label>
                            <p class="mt-1 text-sm text-gray-900">{{ formatDate(selectedMovement.created_at) }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ghi chú</label>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedMovement.note || '-' }}</p>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-3">Danh sách sản phẩm</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Sản phẩm</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            SKU</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Số lượng</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Đơn giá</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in selectedMovement.items" :key="item.id">
                                        <td class="px-4 py-2 text-sm text-gray-900">{{
                                            item.variant.product.name }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ item.variant.sku }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ parseInt(item.quantity) || 0 }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{
                                            formatCurrency(parseFloat(item.unit_price) || 0) }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{
                                            formatCurrency((parseInt(item.quantity) || 0) * (parseFloat(item.unit_price)
                                                || 0)) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button @click="closeDetailsModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Receipt Modal -->
    <div v-if="showPrintModal"
        class="fixed inset-0 backdrop-blur-sm bg-black/30 overflow-y-auto h-full w-full z-50 p-4">
        <div
            class="relative top-10 mx-auto p-4 sm:p-5 border w-full sm:w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="no-print flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        In phiếu #{{ selectedMovement?.id }}
                    </h3>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <button @click="printDocument"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                </path>
                            </svg>
                            In phiếu
                        </button>
                        <button @click="closePrintModal"
                            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                            Đóng
                        </button>
                    </div>
                </div>

                <div class="receipt-content bg-white p-4 sm:p-8" ref="receiptContent">
                    <div class="text-center mb-8">
                        <h1 class="text-xl sm:text-2xl font-bold mb-2">
                            {{ selectedMovement?.type === 'import' ? 'PHIẾU NHẬP KHO' : 'PHIẾU XUẤT KHO' }}
                        </h1>
                        <p class="text-base sm:text-lg">Số phiếu: <strong>#{{ selectedMovement?.id }}</strong></p>
                        <p class="text-sm text-gray-600">Ngày: {{ formatDate(selectedMovement?.created_at) }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-8 mb-8">
                        <div>
                            <h3 class="font-semibold mb-2">Thông tin phiếu:</h3>
                            <p><strong>Loại:</strong>
                                {{ selectedMovement?.type === 'import' ? 'Nhập kho' : 'Xuấtkho' }}</p>
                            <p><strong>Người tạo:</strong> {{ selectedMovement?.user?.username || 'N/A' }}</p>
                            <p><strong>Ngày tạo:</strong> {{ formatDate(selectedMovement?.created_at) }}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold mb-2">Ghi chú:</h3>
                            <p>{{ selectedMovement?.note || 'Không có ghi chú' }}</p>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h3 class="font-semibold mb-4">Danh sách sản phẩm:</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse border border-gray-300 text-xs sm:text-sm">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left">STT</th>
                                        <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left">Tên sản phẩm
                                        </th>
                                        <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left">SKU</th>
                                        <th class="border border-gray-300 px-2 sm:px-4 py-2 text-center">Số lượng
                                        </th>
                                        <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right">Đơn giá</th>
                                        <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right">Thành tiền
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in selectedMovement?.items" :key="item.id">
                                        <td class="border border-gray-300 px-2 sm:px-4 py-2 text-center">{{ index + 1
                                            }}</td>
                                        <td class="border border-gray-300 px-2 sm:px-4 py-2">{{
                                            item.variant.product.name }}
                                        </td>
                                        <td class="border border-gray-300 px-2 sm:px-4 py-2">{{ item.variant.sku }}
                                        </td>
                                        <td class="border border-gray-300 px-2 sm:px-4 py-2 text-center">{{
                                            parseInt(item.quantity) || 0 }}
                                        </td>
                                        <td class="border border-gray-300 px-2 sm:px-4 py-2 text-right">{{
                                            formatCurrency(parseFloat(item.unit_price) || 0) }}</td>
                                        <td class="border border-gray-300 px-2 sm:px-4 py-2 text-right">{{
                                            formatCurrency((parseInt(item.quantity) || 0) * (parseFloat(item.unit_price)
                                                || 0)) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50 font-semibold">
                                        <td colspan="3" class="border border-gray-300 px-2 sm:px-4 py-2 text-right">
                                            Tổng cộng:
                                        </td>
                                        <td class="border border-gray-300 px-2 sm:px-4 py-2 text-center">{{
                                            totalQuantity }}
                                        </td>
                                        <td class="border border-gray-300 px-2 sm:px-4 py-2 text-right">-</td>
                                        <td class="border border-gray-300 px-2 sm:px-4 py-2 text-right">{{
                                            formatCurrency(totalAmount) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-8 mt-8 sm:mt-16">
                        <div class="text-center">
                            <p class="font-semibold mb-8 sm:mb-16">Người lập phiếu</p>
                            <p class="border-t border-gray-400 pt-2">{{ selectedMovement?.user?.username ||
                                'N/A' }}
                            </p>
                        </div>
                        <div class="text-center">
                            <p class="font-semibold mb-8 sm:mb-16">Thủ kho</p>
                            <p class="border-t border-gray-400 pt-2">_________________</p>
                        </div>
                        <div class="text-center">
                            <p class="font-semibold mb-8 sm:mb-16">Giám đốc</p>
                            <p class="border-t border-gray-400 pt-2">_________________</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
// import { useInventories } from '~/composables/useInventorie'
import { useInventories } from '../../../composable/useInventorie'

const { getStockMovement } = useInventories()

const loading = ref(false)
const stockMovements = ref([])
const currentPage = ref(1)
const itemsPerPage = 10

const fetchStockMovements = async () => {
    loading.value = true
    try {
        const res = await getStockMovement()
        stockMovements.value = res
    } catch (e) {
        alert('Không thể tải danh sách phiếu kho!')
    } finally {
        loading.value = false
    }
}

const showDetailsModal = ref(false)
const showPrintModal = ref(false)
const selectedMovement = ref(null)

const totalQuantity = computed(() => {
    return selectedMovement.value?.items?.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0) || 0
})

const totalAmount = computed(() => {
    return selectedMovement.value?.items?.reduce((sum, item) => sum + ((parseInt(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)), 0) || 0
})

const paginatedMovements = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    const end = start + itemsPerPage
    return stockMovements.value.slice(start, end)
})

const totalPages = computed(() => Math.ceil(stockMovements.value.length / itemsPerPage))

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
    }
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('vi-VN')
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount || 0)
}

const viewDetails = (movement) => {
    selectedMovement.value = movement
    showDetailsModal.value = true
}

const closeDetailsModal = () => {
    showDetailsModal.value = false
    selectedMovement.value = null
}

const printReceipt = (movement) => {
    selectedMovement.value = movement
    showPrintModal.value = true
}

const closePrintModal = () => {
    showPrintModal.value = false
    selectedMovement.value = null
}

const printDocument = () => {
    const printWindow = window.open('', '_blank')
    const receiptContent = document.querySelector('.receipt-content')

    // Tính toán tổng số lượng và tổng tiền trực tiếp
    const calculatedTotalQuantity = selectedMovement.value?.items?.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0) || 0
    const calculatedTotalAmount = selectedMovement.value?.items?.reduce((sum, item) => sum + ((parseInt(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)), 0) || 0

    if (receiptContent && printWindow) {
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Phiếu ${selectedMovement.value?.type === 'import' ? 'Nhập' : 'Xuất'} Kho #${selectedMovement.value?.id}</title>
                <style>
                    body {
                        font-family: 'Times New Roman', serif;
                        margin: 0;
                        padding: 20px;
                        line-height: 1.4;
                        color: black;
                    }
                    .receipt-content {
                        max-width: 800px;
                        margin: 0 auto;
                    }
                    h1 {
                        text-align: center;
                        font-size: 24px;
                        font-weight: bold;
                        margin-bottom: 20px;
                    }
                    .header-info {
                        text-align: center;
                        margin-bottom: 30px;
                    }
                    .header-info p {
                        margin: 5px 0;
                    }
                    .info-grid {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 30px;
                        margin-bottom: 30px;
                    }
                    .info-section h3 {
                        font-weight: bold;
                        margin-bottom: 10px;
                    }
                    .info-section p {
                        margin: 5px 0;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 30px;
                    }
                    th, td {
                        border: 1px solid #000;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #f5f5f5;
                        font-weight: bold;
                    }
                    .text-center {
                        text-align: center;
                    }
                    .text-right {
                        text-align: right;
                    }
                    .signatures {
                        display: grid;
                        grid-template-columns: 1fr 1fr 1fr;
                        gap: 30px;
                        margin-top: 50px;
                    }
                    .signature-box {
                        text-align: center;
                    }
                    .signature-line {
                        border-top: 1px solid #000;
                        margin-top: 40px;
                        padding-top: 10px;
                    }
                    @media print {
                        body {
                            margin: 0;
                            padding: 15px;
                        }
                        .receipt-content {
                            max-width: none;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="receipt-content">
                    <div class="header-info">
                        <h1>${selectedMovement.value?.type === 'import' ? 'PHIẾU NHẬP KHO' : 'PHIẾU XUẤT KHO'}</h1>
                        <p><strong>Số phiếu: #${selectedMovement.value?.id}</strong></p>
                        <p>Ngày: ${formatDate(selectedMovement.value?.created_at)}</p>
                    </div>

                    <div class="info-grid">
                        <div class="info-section">
                            <h3>Thông tin phiếu:</h3>
                            <p><strong>Loại:</strong> ${selectedMovement.value?.type === 'import' ? 'Nhập kho' : 'Xuất kho'}</p>
                            <p><strong>Người tạo:</strong> ${selectedMovement.value?.user?.username || 'N/A'}</p>
                            <p><strong>Ngày tạo:</strong> ${formatDate(selectedMovement.value?.created_at)}</p>
                        </div>
                        <div class="info-section">
                            <h3>Ghi chú:</h3>
                            <p>${selectedMovement.value?.note || 'Không có ghi chú'}</p>
                        </div>
                    </div>

                    <h3>Danh sách sản phẩm:</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên sản phẩm</th>
                                <th>SKU</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-right">Đơn giá</th>
                                <th class="text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${selectedMovement.value?.items?.map((item, index) => `
                                <tr>
                                    <td class="text-center">${index + 1}</td>
                                    <td>${item.variant.product.name}</td>
                                    <td>${item.variant.sku}</td>
                                    <td class="text-center">${parseInt(item.quantity) || 0}</td>
                                    <td class="text-right">${formatCurrency(parseFloat(item.unit_price) || 0)}</td>
                                    <td class="text-right">${formatCurrency((parseInt(item.quantity) || 0) * (parseFloat(item.unit_price) || 0))}</td>
                                </tr>
                            `).join('') || ''}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Tổng cộng:</strong></td>
                                <td class="text-center"><strong>${calculatedTotalQuantity}</strong></td>
                                <td class="text-right">-</td>
                                <td class="text-right"><strong>${formatCurrency(calculatedTotalAmount)}</strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="signatures">
                        <div class="signature-box">
                            <p class="signature-line"><strong>Người lập phiếu</strong></p>
                            <p>${selectedMovement.value?.user?.username || 'N/A'}</p>
                        </div>
                        <div class="signature-box">
                            <p class="signature-line"><strong>Thủ kho</strong></p>
                            <p>_________________</p>
                        </div>
                        <div class="signature-box">
                            <p class="signature-line"><strong>Giám đốc</strong></p>
                            <p>_________________</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
        `)

        printWindow.document.close()
        printWindow.focus()

        printWindow.onload = function () {
            printWindow.print()
            printWindow.close()
        }
    }
}

onMounted(() => {
    fetchStockMovements()
})
</script>

<style>
.receipt-content {
    font-family: 'Times New Roman', serif;
    line-height: 1.4;
}

.receipt-content table {
    page-break-inside: avoid;
}

.receipt-content h1,
.receipt-content h3 {
    color: #000 !important;
}
</style>