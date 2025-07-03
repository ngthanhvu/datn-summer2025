<template>
    <div class="tw-mb-8">
        <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900 tw-mb-2">Danh sách phiếu nhập/xuất</h1>
        <p class="tw-text-gray-600">Quản lý và theo dõi các phiếu nhập/xuất kho</p>
    </div>

    <div v-if="loading" class="tw-flex tw-justify-center tw-items-center tw-py-12">
        <div class="tw-animate-spin tw-rounded-full tw-h-8 tw-w-8 tw-border-b-2 tw-border-blue-600"></div>
        <span class="tw-ml-2 tw-text-gray-600">Đang tải dữ liệu...</span>
    </div>

    <div v-else class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-overflow-hidden">
        <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th
                        class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-700 tw-uppercase tw-tracking-wider">
                        Mã phiếu
                    </th>
                    <th
                        class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-700 tw-uppercase tw-tracking-wider">
                        Loại
                    </th>
                    <th
                        class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-700 tw-uppercase tw-tracking-wider">
                        Người tạo
                    </th>
                    <th
                        class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-700 tw-uppercase tw-tracking-wider">
                        Ngày tạo
                    </th>
                    <th
                        class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-700 tw-uppercase tw-tracking-wider">
                        Số sản phẩm
                    </th>
                    <th
                        class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-700 tw-uppercase tw-tracking-wider">
                        Ghi chú
                    </th>
                    <th
                        class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-700 tw-uppercase tw-tracking-wider">
                        Thao tác
                    </th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                <tr v-for="(movement, index) in stockMovements" :key="movement.id" class="hover:tw-bg-gray-50">
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-text-sm tw-font-medium tw-text-gray-900">#{{ index + 1 }}</span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span :class="[
                            'tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium',
                            movement.type === 'import'
                                ? 'tw-bg-green-100 tw-text-green-800'
                                : 'tw-bg-red-100 tw-text-red-800'
                        ]">
                            {{ movement.type === 'import' ? 'Nhập kho' : 'Xuất kho' }}
                        </span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-text-sm tw-text-gray-900">{{ movement.creator?.name || 'N/A' }}</span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-text-sm tw-text-gray-900">{{ formatDate(movement.created_at) }}</span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <span class="tw-text-sm tw-text-gray-900">{{ movement.items?.length || 0 }} sản phẩm</span>
                    </td>
                    <td class="tw-px-6 tw-py-4">
                        <span class="tw-text-sm tw-text-gray-900">{{ movement.note || '-' }}</span>
                    </td>
                    <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                        <button @click="viewDetails(movement)" class="tw-text-blue-600 hover:tw-text-blue-900 tw-mr-3">
                            Xem chi tiết
                        </button>
                        <button @click="printReceipt(movement)" class="tw-text-green-600 hover:tw-text-green-900">
                            <svg class="tw-w-4 tw-h-4 tw-inline tw-mr-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                </path>
                            </svg>
                            In phiếu
                        </button>
                    </td>
                </tr>
                <tr v-if="stockMovements.length === 0">
                    <td colspan="8" class="tw-text-center tw-px-6 tw-py-4 tw-whitespace-nowrap">
                        <div class="tw-flex tw-justify-center tw-text-center">
                            <span class="tw-text-sm tw-font-medium tw-text-gray-500">Không có dữ liệu</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Movement Details Modal -->
    <div v-if="showDetailsModal"
        class="tw-fixed tw-inset-0 tw-backdrop-blur-sm tw-bg-black/30 tw-overflow-y-auto tw-h-full tw-w-full tw-z-50">
        <div
            class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-w-3/4 tw-max-w-4xl tw-shadow-lg tw-rounded-md tw-bg-white">
            <div class="tw-mt-3">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">
                        Chi tiết phiếu #{{ selectedMovement?.id }}
                    </h3>
                    <button @click="closeDetailsModal" class="tw-text-gray-400 hover:tw-text-gray-600">
                        <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div v-if="selectedMovement" class="tw-mb-6">
                    <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-4 tw-mb-4">
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Loại giao dịch</label>
                            <span :class="[
                                'tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-mt-1',
                                selectedMovement.type === 'import'
                                    ? 'tw-bg-green-100 tw-text-green-800'
                                    : 'tw-bg-red-100 tw-text-red-800'
                            ]">
                                {{ selectedMovement.type === 'import' ? 'Nhập kho' : 'Xuất kho' }}
                            </span>
                        </div>
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Người tạo</label>
                            <p class="tw-mt-1 tw-text-sm tw-text-gray-900">{{ selectedMovement.creator?.name || 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Ngày tạo</label>
                            <p class="tw-mt-1 tw-text-sm tw-text-gray-900">{{ formatDate(selectedMovement.created_at) }}
                            </p>
                        </div>
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Ghi chú</label>
                            <p class="tw-mt-1 tw-text-sm tw-text-gray-900">{{ selectedMovement.note || '-' }}</p>
                        </div>
                    </div>
                    <div>
                        <h4 class="tw-text-md tw-font-medium tw-text-gray-900 tw-mb-3">Danh sách sản phẩm</h4>
                        <div class="tw-overflow-x-auto">
                            <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                                <thead class="tw-bg-gray-50">
                                    <tr>
                                        <th
                                            class="tw-px-4 tw-py-2 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                            Sản phẩm</th>
                                        <th
                                            class="tw-px-4 tw-py-2 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                            SKU</th>
                                        <th
                                            class="tw-px-4 tw-py-2 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                            Số lượng</th>
                                        <th
                                            class="tw-px-4 tw-py-2 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                            Đơn giá</th>
                                        <th
                                            class="tw-px-4 tw-py-2 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                            Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                                    <tr v-for="item in selectedMovement.items" :key="item.id">
                                        <td class="tw-px-4 tw-py-2 tw-text-sm tw-text-gray-900">{{ item.product?.name }}
                                        </td>
                                        <td class="tw-px-4 tw-py-2 tw-text-sm tw-text-gray-500">{{ item.product?.sku }}
                                        </td>
                                        <td class="tw-px-4 tw-py-2 tw-text-sm tw-text-gray-900">{{ item.quantity }}</td>
                                        <td class="tw-px-4 tw-py-2 tw-text-sm tw-text-gray-900">{{
                                            formatCurrency(item.unit_price) }}</td>
                                        <td class="tw-px-4 tw-py-2 tw-text-sm tw-text-gray-900">{{
                                            formatCurrency(item.quantity * item.unit_price) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tw-flex tw-justify-end">
                    <button @click="closeDetailsModal"
                        class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-gray-100 tw-border tw-border-gray-300 tw-rounded-lg hover:tw-bg-gray-200">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Receipt Modal -->
    <div v-if="showPrintModal"
        class="tw-fixed tw-inset-0 tw-backdrop-blur-sm tw-bg-black/30 tw-overflow-y-auto tw-h-full tw-w-full tw-z-50">
        <div
            class="tw-relative tw-top-10 tw-mx-auto tw-p-5 tw-border tw-w-11/12 tw-max-w-4xl tw-shadow-lg tw-rounded-md tw-bg-white">
            <div class="tw-mt-3">
                <div class="no-print tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">
                        In phiếu #{{ selectedMovement?.id }}
                    </h3>
                    <div class="tw-flex tw-space-x-2">
                        <button @click="printDocument"
                            class="tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-lg hover:tw-bg-blue-700">
                            <svg class="tw-w-4 tw-h-4 tw-inline tw-mr-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                </path>
                            </svg>
                            In phiếu
                        </button>
                        <button @click="closePrintModal"
                            class="tw-px-4 tw-py-2 tw-bg-gray-500 tw-text-white tw-rounded-lg hover:tw-bg-gray-600">
                            Đóng
                        </button>
                    </div>
                </div>

                <div class="receipt-content tw-bg-white tw-p-8" ref="receiptContent">
                    <div class="tw-text-center tw-mb-8">
                        <h1 class="tw-text-2xl tw-font-bold tw-mb-2">
                            {{ selectedMovement?.type === 'import' ? 'PHIẾU NHẬP KHO' : 'PHIẾU XUẤT KHO' }}
                        </h1>
                        <p class="tw-text-lg">Số phiếu: <strong>#{{ selectedMovement?.id }}</strong></p>
                        <p class="tw-text-sm tw-text-gray-600">Ngày: {{ formatDate(selectedMovement?.created_at) }}</p>
                    </div>

                    <div class="tw-grid tw-grid-cols-2 tw-gap-8 tw-mb-8">
                        <div>
                            <h3 class="tw-font-semibold tw-mb-2">Thông tin phiếu:</h3>
                            <p><strong>Loại:</strong>
                                {{ selectedMovement?.type === 'import' ? 'Nhập kho' : 'Xuấtkho' }}</p>
                            <p><strong>Người tạo:</strong> {{ selectedMovement?.creator?.name || 'N/A' }}</p>
                            <p><strong>Ngày tạo:</strong> {{ formatDate(selectedMovement?.created_at) }}</p>
                        </div>
                        <div>
                            <h3 class="tw-font-semibold tw-mb-2">Ghi chú:</h3>
                            <p>{{ selectedMovement?.note || 'Không có ghi chú' }}</p>
                        </div>
                    </div>

                    <div class="tw-mb-8">
                        <h3 class="tw-font-semibold tw-mb-4">Danh sách sản phẩm:</h3>
                        <table class="tw-w-full tw-border-collapse tw-border tw-border-gray-300">
                            <thead>
                                <tr class="tw-bg-gray-50">
                                    <th class="tw-border tw-border-gray-300 tw-px-4 tw-py-2 tw-text-left">STT</th>
                                    <th class="tw-border tw-border-gray-300 tw-px-4 tw-py-2 tw-text-left">Tên sản phẩm
                                    </th>
                                    <th class="tw-border tw-border-gray-300 tw-px-4 tw-py-2 tw-text-left">SKU</th>
                                    <th class="tw-border tw-border-gray-300 tw-px-4 tw-py-2 tw-text-center">Số lượng
                                    </th>
                                    <th class="tw-border tw-border-gray-300 tw-px-4 tw-py-2 tw-text-right">Đơn giá</th>
                                    <th class="tw-border tw-border-gray-300 tw-px-4 tw-py-2 tw-text-right">Thành tiền
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in selectedMovement?.items" :key="item.id">
                                    <td class="tw-border tw-border-gray-300 tw-px-4 tw-py-2 tw-text-center">{{ index + 1
                                        }}</td>
                                    <td class="tw-border tw-border-gray-300 tw-px-4 tw-py-2">{{ item.product?.name }}
                                    </td>
                                    <td class="tw-border tw-border-gray-300 tw-px-4 tw-py-2">{{ item.product?.sku }}
                                    </td>
                                    <td class="tw-border tw-border-gray-300 tw-px-4 tw-py-2 tw-text-center">{{
                                        item.quantity }}
                                    </td>
                                    <td class="tw-border tw-border-gray-300 tw-px-4 tw-py-2 tw-text-right">{{
                                        formatCurrency(item.unit_price) }}</td>
                                    <td class="tw-border tw-border-gray-300 tw-px-4 tw-py-2 tw-text-right">{{
                                        formatCurrency(item.quantity * item.unit_price) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="tw-bg-gray-50 tw-font-semibold">
                                    <td colspan="3" class="tw-border tw-border-gray-300 tw-px-4 tw-py-2 tw-text-right">
                                        Tổng cộng:
                                    </td>
                                    <td class="tw-border tw-border-gray-300 tw-px-4 tw-py-2 tw-text-center">{{
                                        totalQuantity }}
                                    </td>
                                    <td class="tw-border tw-border-gray-300 tw-px-4 tw-py-2 tw-text-right">-</td>
                                    <td class="tw-border tw-border-gray-300 tw-px-4 tw-py-2 tw-text-right">{{
                                        formatCurrency(totalAmount) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="tw-grid tw-grid-cols-3 tw-gap-8 tw-mt-16">
                        <div class="tw-text-center">
                            <p class="tw-font-semibold tw-mb-16">Người lập phiếu</p>
                            <p class="tw-border-t tw-border-gray-400 tw-pt-2">{{ selectedMovement?.creator?.name ||
                                'N/A' }}
                            </p>
                        </div>
                        <div class="tw-text-center">
                            <p class="tw-font-semibold tw-mb-16">Thủ kho</p>
                            <p class="tw-border-t tw-border-gray-400 tw-pt-2">_________________</p>
                        </div>
                        <div class="tw-text-center">
                            <p class="tw-font-semibold tw-mb-16">Giám đốc</p>
                            <p class="tw-border-t tw-border-gray-400 tw-pt-2">_________________</p>
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

const loading = ref(false)
const stockMovements = ref([
    {
        id: 1,
        type: 'import',
        creator: { name: 'admin' },
        created_at: '2025-07-02T22:14:36',
        note: 'nhap kho',
        items: [
            {
                id: 1,
                product: { name: 'Áo thun nam', sku: 'TS001' },
                quantity: 2,
                unit_price: 120000
            },
            {
                id: 2,
                product: { name: 'Quần jeans', sku: 'J001' },
                quantity: 1,
                unit_price: 350000
            }
        ]
    },
    {
        id: 2,
        type: 'export',
        creator: { name: 'admin' },
        created_at: '2025-06-19T09:06:36',
        note: 'Xuất kho cho đơn hàng HD1750298796590',
        items: [
            {
                id: 3,
                product: { name: 'Áo thun nữ', sku: 'TS002' },
                quantity: 1,
                unit_price: 150000
            }
        ]
    },
    {
        id: 3,
        type: 'export',
        creator: { name: 'admin' },
        created_at: '2025-06-19T09:05:58',
        note: 'Xuất kho cho đơn hàng HD1750298758228',
        items: [
            {
                id: 4,
                product: { name: 'Áo sơ mi', sku: 'SM001' },
                quantity: 1,
                unit_price: 200000
            }
        ]
    }
])

const showDetailsModal = ref(false)
const showPrintModal = ref(false)
const selectedMovement = ref(null)

const totalQuantity = computed(() => {
    return selectedMovement.value?.items?.reduce((sum, item) => sum + item.quantity, 0) || 0
})

const totalAmount = computed(() => {
    return selectedMovement.value?.items?.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0) || 0
})

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
    window.print()
}

// onMounted(() => {
//     getStockMovements()
// })
</script>

<style>
@media print {
    .no-print {
        display: none !important;
    }

    body * {
        visibility: hidden;
    }

    .receipt-content,
    .receipt-content * {
        visibility: visible;
    }

    .receipt-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: white !important;
    }

    .tw-fixed {
        position: static !important;
    }

    .tw-bg-gray-600 {
        background: transparent !important;
    }
}

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