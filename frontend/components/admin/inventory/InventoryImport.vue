<template>
    <div class="tw-p-6">
        <div class="tw-mb-6">
            <h1 class="tw-text-2xl tw-font-bold tw-mb-4">Nhập/Xuất kho</h1>
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-md tw-mb-6">
                <form @submit.prevent="handleImport" class="tw-space-y-4">
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Sản phẩm</label>
                            <select v-model="importForm.variant_id" class="tw-w-full tw-border tw-rounded-md tw-p-2"
                                required>
                                <option value="">Chọn sản phẩm</option>
                                <option v-for="variant in variants" :key="variant.id" :value="variant.id">
                                    {{ variant.product.name }} - {{ variant.color }} - {{ variant.size }} (SKU: {{
                                        variant.sku }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Số lượng nhập</label>
                            <input type="number" v-model="importForm.quantity"
                                class="tw-w-full tw-border tw-rounded-md tw-p-2" min="1" required>
                        </div>
                        <div class="md:tw-col-span-2">
                            <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Ghi chú</label>
                            <textarea v-model="importForm.note" class="tw-w-full tw-border tw-rounded-md tw-p-2"
                                rows="3"></textarea>
                        </div>
                    </div>
                    <button type="submit"
                        class="tw-bg-[#3BB77E] tw-text-white tw-px-4 tw-py-2 tw-rounded-md hover:tw-bg-[#5ebd91]">
                        Nhập kho
                    </button>
                </form>
            </div>
            <!-- Tabs for Import/Export History -->
            <div class="tw-mb-4">
                <button @click="activeTab = 'import'" :class="tabClass('import')">Lịch sử nhập kho</button>
                <button @click="activeTab = 'export'" :class="tabClass('export')">Lịch sử xuất kho</button>
            </div>
            <div class="tw-bg-white tw-rounded-lg tw-shadow-md">
                <div class="tw-p-4">
                    <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                        <h2 class="tw-text-xl tw-font-semibold">
                            {{ activeTab === 'import' ? 'Lịch sử nhập kho' : 'Lịch sử xuất kho' }}
                        </h2>
                        <div class="tw-flex tw-gap-2">
                            <input type="date" v-model="filters.date" class="tw-border tw-rounded-md tw-p-2">
                        </div>
                    </div>
                    <div class="tw-overflow-x-auto">
                        <table class="tw-min-w-full tw-text-sm">
                            <thead>
                                <tr class="tw-bg-gray-50">
                                    <th
                                        class="tw-px-3 tw-py-2 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                        Thời gian</th>
                                    <th
                                        class="tw-px-3 tw-py-2 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                        Sản phẩm</th>
                                    <th
                                        class="tw-px-3 tw-py-2 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                        Loại</th>
                                    <th
                                        class="tw-px-3 tw-py-2 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                        Số lượng</th>
                                    <th
                                        class="tw-px-3 tw-py-2 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                        Ghi chú</th>
                                    <th
                                        class="tw-px-3 tw-py-2 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                        Người thực hiện</th>
                                    <th
                                        class="tw-px-3 tw-py-2 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                                        Tải PDF</th>
                                </tr>
                            </thead>
                            <tbody class="tw-divide-y tw-divide-gray-200">
                                <template v-if="loading">
                                    <tr v-for="n in 5" :key="n">
                                        <td class="tw-px-3 tw-py-2">
                                            <div class="tw-bg-gray-200 tw-h-4 tw-rounded tw-w-20 tw-animate-pulse">
                                            </div>
                                        </td>
                                        <td class="tw-px-3 tw-py-2">
                                            <div class="tw-bg-gray-200 tw-h-4 tw-rounded tw-w-32 tw-animate-pulse">
                                            </div>
                                        </td>
                                        <td class="tw-px-3 tw-py-2">
                                            <div class="tw-bg-gray-200 tw-h-4 tw-rounded tw-w-16 tw-animate-pulse">
                                            </div>
                                        </td>
                                        <td class="tw-px-3 tw-py-2">
                                            <div class="tw-bg-gray-200 tw-h-4 tw-rounded tw-w-10 tw-animate-pulse">
                                            </div>
                                        </td>
                                        <td class="tw-px-3 tw-py-2">
                                            <div class="tw-bg-gray-200 tw-h-4 tw-rounded tw-w-24 tw-animate-pulse">
                                            </div>
                                        </td>
                                        <td class="tw-px-3 tw-py-2">
                                            <div class="tw-bg-gray-200 tw-h-4 tw-rounded tw-w-20 tw-animate-pulse">
                                            </div>
                                        </td>
                                        <td class="tw-px-3 tw-py-2">
                                            <div class="tw-bg-gray-200 tw-h-4 tw-rounded tw-w-12 tw-animate-pulse">
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template v-else>
                                    <tr v-for="movement in filteredMovements" :key="movement.id"
                                        class="hover:tw-bg-gray-50">
                                        <td class="tw-px-3 tw-py-2 tw-whitespace-nowrap">{{ movement.created_at ?
                                            formatDate(movement.created_at) : '-' }}</td>
                                        <td class="tw-px-3 tw-py-2">
                                            <div class="tw-font-medium">{{ movement.variant.product.name }}</div>
                                            <div class="tw-text-xs tw-text-gray-500">{{ movement.variant.color }} - {{
                                                movement.variant.size }} (SKU: {{ movement.variant.sku }})</div>
                                        </td>
                                        <td class="tw-px-3 tw-py-2">
                                            <span :class="{
                                                'tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium': true,
                                                'tw-bg-green-100 tw-text-green-800': movement.type === 'import',
                                                'tw-bg-red-100 tw-text-red-800': movement.type === 'export',
                                                'tw-bg-yellow-100 tw-text-yellow-800': movement.type === 'adjustment'
                                            }">
                                                {{ getMovementTypeLabel(movement.type) }}
                                            </span>
                                        </td>
                                        <td class="tw-px-3 tw-py-2">
                                            <span :class="{
                                                'tw-font-medium': true,
                                                'tw-text-green-600': movement.type === 'import',
                                                'tw-text-red-600': movement.type === 'export',
                                                'tw-text-yellow-600': movement.type === 'adjustment'
                                            }">
                                                {{ movement.type === 'import' ? '+' : movement.type === 'export' ? '-' :
                                                    ''
                                                }}{{ movement.quantity }}
                                            </span>
                                        </td>
                                        <td class="tw-px-3 tw-py-2">{{ movement.note || '-' }}</td>
                                        <td class="tw-px-3 tw-py-2">
                                            <div class="tw-flex tw-items-center">
                                                <div class="tw-text-xs tw-font-medium">{{ movement.user ?
                                                    movement.user.username : 'Không xác định' }}</div>
                                            </div>
                                        </td>
                                        <td class="tw-px-3 tw-py-2">
                                            <button @click="downloadMovementPdf(movement.id)"
                                                class="tw-bg-[#3BB77E] tw-text-white tw-px-2 tw-py-1 tw-rounded hover:tw-bg-[#5ebd91] tw-text-xs">
                                                Tải PDF
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredMovements.length === 0">
                                        <td colspan="7" class="tw-px-3 tw-py-2 tw-text-center tw-text-gray-500">Không có
                                            dữ
                                            liệu</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="tw-mt-4 tw-flex tw-justify-between tw-items-center">
                        <div class="tw-text-sm tw-text-gray-500">
                            Hiển thị {{ filteredMovements.length }} kết quả
                        </div>
                        <div class="tw-flex tw-gap-2">
                            <button @click="loadMore"
                                class="tw-px-4 tw-py-2 tw-bg-gray-100 tw-text-gray-700 tw-rounded-md hover:tw-bg-gray-200">
                                Xem thêm
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import jsPDF from 'jspdf'

const { getInventories, updateStock, getMovements, getVariants, downloadMovementPdf } = useInventories()
const variants = ref([])
const recentImports = ref([])
const importForm = ref({
    variant_id: '',
    quantity: 1,
    note: ''
})

const activeTab = ref('import')

const tabClass = (tab) => {
    return [
        'tw-px-4 tw-py-2 tw-rounded-t-md tw-font-semibold',
        activeTab.value === tab ? 'tw-bg-[#3BB77E] tw-text-white' : 'tw-bg-white tw-text-gray-700',
        'tw-mr-2'
    ].join(' ')
}

const filters = ref({
    date: ''
})

const recentMovements = ref([])
const loading = ref(false)

const fetchVariants = async () => {
    try {
        loading.value = true
        const data = await getVariants()
        variants.value = data
    } catch (error) {
        console.error('Error fetching variants:', error)
    } finally {
        loading.value = false
    }
}

const fetchRecentMovements = async () => {
    try {
        loading.value = true
        const data = await getMovements({ limit: 20 })
        recentMovements.value = data
    } catch (error) {
        console.error('Error fetching movements:', error)
    } finally {
        loading.value = false
    }
}

const handleImport = async () => {
    try {
        const response = await updateStock({
            variant_id: importForm.value.variant_id,
            quantity: importForm.value.quantity,
            type: 'import',
            note: importForm.value.note
        })

        if (!response) {
            throw new Error('Không nhận được phản hồi từ server')
        }

        importForm.value = {
            variant_id: '',
            quantity: 1,
            note: ''
        }

        await fetchRecentMovements()

        alert('Nhập kho thành công')
    } catch (error) {
        console.error('Error importing stock:', error)
        let errorMessage = 'Có lỗi xảy ra khi nhập kho'

        if (error.response?.data?.message) {
            errorMessage = error.response.data.message
        } else if (error.response?.data?.errors) {
            // Handle validation errors
            const errors = error.response.data.errors
            errorMessage = Object.values(errors).flat().join('\n')
        }

        alert(errorMessage)
    }
}

const formatDate = (date) => {
    return new Date(date).toLocaleString('vi-VN')
}

const getMovementTypeLabel = (type) => {
    const labels = {
        import: 'Nhập kho',
        export: 'Xuất kho',
        adjustment: 'Điều chỉnh'
    }
    return labels[type] || type
}

const filteredMovements = computed(() => {
    let result = recentMovements.value.filter(m => m.type === activeTab.value)
    if (filters.value.date) {
        const filterDate = new Date(filters.value.date).toDateString()
        result = result.filter(m => new Date(m.created_at).toDateString() === filterDate)
    }
    return result
})

const loadMore = async () => {
    try {
        const currentLength = recentMovements.value.length
        const newData = await getMovements({
            type: activeTab.value,
            limit: 10,
            offset: currentLength
        })
        recentMovements.value = [...recentMovements.value, ...newData]
    } catch (error) {
        console.error('Error loading more movements:', error)
    }
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

function toVietnameseText(number) {
    return number + ' đồng chẵn'
}

onMounted(() => {
    fetchVariants()
    fetchRecentMovements()
})
</script>