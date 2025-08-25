<template>
    <div class="bg-[#f7f8fa] min-h-screen p-3 sm:p-6">
        <h1 class="text-2xl sm:text-3xl font-bold">Quản lý Flash Sale</h1>
        <div class="text-gray-500 mb-4 sm:mb-6">Quản lý các chương trình Flash Sale của bạn</div>

        <!-- Thống kê tổng quan -->
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div v-for="i in 4" :key="i" class="bg-white rounded-xl shadow p-4">
                <div class="animate-pulse">
                    <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                    <div class="h-8 bg-gray-200 rounded w-1/2"></div>
                </div>
            </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Tổng sản phẩm đã bán -->
            <div class="bg-white rounded-xl shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Tổng sản phẩm đã bán</p>
                        <p class="text-2xl font-bold text-gray-900">{{ totalSold }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Tổng doanh thu -->
            <div class="bg-white rounded-xl shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Tổng doanh thu</p>
                        <p class="text-2xl font-bold text-green-600">{{ formatCurrency(totalRevenue) }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Flash Sale đang chạy -->
            <div class="bg-white rounded-xl shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Flash Sale đang chạy</p>
                        <p class="text-2xl font-bold text-orange-600">{{ activeFlashSales }}</p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-fire text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Lợi nhuận thực tế -->
            <div class="bg-white rounded-xl shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Lợi nhuận thực tế</p>
                        <p class="text-2xl font-bold text-purple-600">{{ formatCurrency(totalProfit) }}</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-3 sm:p-6">
            <!-- Mobile-first filter layout -->
            <div class="space-y-3 sm:space-y-0 sm:flex sm:gap-4 mb-4 sm:flex-wrap">
                <div class="relative flex-1 min-w-full sm:min-w-[220px]">
                    <input class="border border-gray-300 rounded px-3 py-2 w-full pl-10 text-sm"
                        placeholder="Tìm kiếm..." />
                    <i class="fa fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex gap-2 sm:gap-4">
                    <select class="border border-gray-300 rounded px-3 py-2 flex-1 sm:min-w-[180px] text-sm">
                        <option>Tất cả trạng thái</option>
                        <option>Đang diễn ra</option>
                        <option>Kết thúc</option>
                    </select>
                    <div class="relative flex-1 sm:min-w-[180px]">
                        <input class="border border-gray-300 rounded px-3 py-2 w-full text-sm" type="date" />
                    </div>
                </div>
                <router-link to="/admin/flashsale/create"
                    class="w-full sm:w-auto bg-[#3BB77E] hover:bg-green-600 text-white px-4 py-2 rounded flex items-center justify-center gap-2 cursor-pointer text-sm">
                    <i class="fa fa-plus"></i> Thêm mới
                </router-link>
                <button @click="processRepeat"
                    class="w-full sm:w-auto bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded flex items-center justify-center gap-2 cursor-pointer text-sm">
                    <i class="fa fa-refresh"></i> Xử lý lặp lại
                </button>
            </div>
            <div v-if="loading" class="text-center py-8">Đang tải dữ liệu...</div>
            <div v-if="error" class="text-center text-red-500 py-4">{{ error }}</div>
            <!-- Desktop table view -->
            <div class="hidden lg:block overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white"
                v-if="!loading && !error">
                <table class="w-full bg-white rounded-xl shadow-sm text-sm">
                    <thead>
                        <tr class="border-b border-gray-300">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Tên chiến dịch</th>
                            <th class="px-4 py-3">Sản phẩm</th>
                            <th class="px-4 py-3">Thời gian</th>
                            <th class="px-4 py-3 text-center">Đã bán (thật)</th>
                            <th class="px-4 py-3 text-center">Giá Flash Sale</th>
                            <th class="px-4 py-3 text-center">Doanh thu thực tế</th>
                            <th class="px-4 py-3 text-center">Giá nhập kho</th>
                            <th class="px-4 py-3 text-center">Phí ship</th>
                            <th class="px-4 py-3 text-center">Lợi nhuận</th>
                            <th class="px-4 py-3 text-center">Trạng thái</th>
                            <th class="px-4 py-3">Lặp lại</th>
                            <th class="px-4 py-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!Array.isArray(flashSales) || !flashSales[0]">
                            <td colspan="12" class="text-center text-gray-400 py-6">Không có dữ liệu</td>
                        </tr>
                        <tr v-for="(item, idx) in paginatedFlashSales" :key="item.id"
                            class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-2 text-center">{{ (currentPage - 1) * itemsPerPage + idx + 1 }}</td>
                            <td class="px-4 py-2 text-center">{{ item.name }}</td>
                            <td class="px-4 py-2 text-center">
                                <span v-if="Array.isArray(item.products) && item.products[0]">Có sản phẩm</span>
                                <span v-else>Không có sản phẩm</span>
                            </td>
                            <td class="px-4 py-2 text-center">{{ item.start_time }} ~ {{ item.end_time }}</td>
                            <td class="px-4 py-2 text-center">{{ getSoldReal(item) }}</td>
                            <td class="px-4 py-2 text-center">{{ formatCurrency(getFlashSalePrice(item)) }}</td>
                            <td class="px-4 py-2 text-center">{{ formatCurrency(realStats[item.id]?.revenue_real ?? 0)
                                }}</td>
                            <td class="px-4 py-2 text-center">{{ formatCurrency(realStats[item.id]?.cost_real ?? 0) }}
                            </td>
                            <td class="px-4 py-2 text-center">{{ formatCurrency(realStats[item.id]?.shipping_fee ?? 0)
                                }}</td>
                            <td class="px-4 py-2 text-center">{{ formatCurrency(realStats[item.id]?.profit_real ?? 0) }}
                            </td>
                            <td class="px-2 py-2 text-center">
                                <button class="px-2 py-1 rounded-full text-xs font-semibold cursor-pointer"
                                    :class="item.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                                    @click="toggleStatus(item)">
                                    {{ item.active ? 'Hoạt động' : 'Đang ẩn' }}
                                </button>
                            </td>
                            <td class="px-2 py-2 text-center">
                                <span v-if="item.repeat"
                                    class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs">Lặp lại</span>
                                <span v-else
                                    class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs">Không</span>
                            </td>
                            <td class="px-4 py-2 flex justify-center items-center">
                                <div class="flex gap-2">
                                    <button
                                        class="inline-flex items-center p-1.5 text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors duration-150"
                                        @click="toggleStatus(item)"
                                        :title="item.active ? 'Tắt chiến dịch' : 'Bật chiến dịch'">
                                        <svg v-if="item.active" class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18 12H6" />
                                        </svg>
                                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v12m6-6H6" />
                                        </svg>
                                    </button>
                                    <router-link :to="`/admin/flashsale/${item.id}/edit`"
                                        class="inline-flex items-center p-1.5 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors duration-150"
                                        title="Sửa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </router-link>
                                    <button
                                        class="inline-flex items-center p-1.5 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors duration-150"
                                        @click="handleDelete(item.id)" title="Xóa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile card view -->
            <div class="lg:hidden space-y-3" v-if="!loading && !error">
                <div v-if="!Array.isArray(flashSales) || !flashSales[0]" class="text-center text-gray-400 py-6">
                    Không có dữ liệu
                </div>
                <div v-for="(item, idx) in paginatedFlashSales" :key="item.id"
                    class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-sm">{{ item.name }}</h3>
                            <p class="text-xs text-gray-500 mt-1">#{{ (currentPage - 1) * itemsPerPage + idx + 1 }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span :class="getStatusBadgeClass(item.active)">
                                {{ getStatusText(item.active) }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Sản phẩm:</span>
                            <span v-if="Array.isArray(item.products) && item.products[0]" class="text-green-600">Có sản
                                phẩm</span>
                            <span v-else class="text-gray-500">Không có sản phẩm</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Thời gian:</span>
                            <span class="text-gray-900 text-right">{{ item.start_time }} ~ {{ item.end_time }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Đã bán:</span>
                            <span class="text-gray-900">{{ getSoldReal(item) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Giá Flash Sale:</span>
                            <span class="text-blue-600">{{ formatCurrency(getFlashSalePrice(item)) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Doanh thu thực tế:</span>
                            <span class="text-green-600">{{ formatCurrency(realStats[item.id]?.revenue_real ?? 0)
                                }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Giá nhập kho:</span>
                            <span class="text-red-600">{{ formatCurrency(realStats[item.id]?.cost_real ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phí ship:</span>
                            <span class="text-orange-600">{{ formatCurrency(realStats[item.id]?.shipping_fee ?? 0)
                                }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Lợi nhuận:</span>
                            <span class="text-blue-600 font-semibold">{{ formatCurrency(realStats[item.id]?.profit_real
                                ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Lặp lại:</span>
                            <span v-if="item.repeat"
                                class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs">Lặp lại</span>
                            <span v-else class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs">Không</span>
                        </div>
                    </div>

                    <div class="flex justify-center gap-2 mt-3 pt-3 border-t border-gray-100">
                        <router-link :to="`/admin/flashsale/${item.id}/edit`"
                            class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded transition-colors duration-150 text-xs font-medium"
                            title="Sửa">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Sửa
                        </router-link>
                        <button
                            class="inline-flex items-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded transition-colors duration-150 text-xs font-medium"
                            @click="handleDelete(item.id)" title="Xóa">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                            Xóa
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="!loading && !error && totalPages > 1"
                class="flex flex-col sm:flex-row justify-between items-center mt-6 gap-3">
                <div class="text-xs sm:text-sm text-gray-600 order-2 sm:order-1">
                    Hiển thị {{ paginatedFlashSales.length }} trên tổng số {{ flashSales.length }} bản ghi
                </div>
                <div class="flex gap-2 order-1 sm:order-2">
                    <button :disabled="currentPage === 1" @click="goToPage(currentPage - 1)"
                        class="px-3 py-1 border border-gray-400 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer text-sm">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="px-3 py-1 text-sm">
                        Trang {{ currentPage }} / {{ totalPages }}
                    </span>
                    <button :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)"
                        class="px-3 py-1 border border-gray-400 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer text-sm">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useFlashsale } from '../../../composable/useFlashsale'
import { push } from 'notivue'
import Swal from 'sweetalert2'

const { getFlashSales, deleteFlashSale, getFlashSaleStatistics, toggleFlashSaleStatus, processRepeat: processRepeatAPI } = useFlashsale()
const flashSales = ref([])
const loading = ref(false)
const error = ref('')
const deleteLoading = ref(false)
const currentPage = ref(1)
const itemsPerPage = 10
const realStats = ref({})

function getSoldReal(item) {
    return realStats.value[item.id]?.sold_real ?? 0
}

function getFlashSalePrice(item) {
    return Number((item.products?.[0]?.flash_price) || 0)
}

function getFlashRevenue(item) {
    const sold = getSoldReal(item)
    const flashPrice = getFlashSalePrice(item)
    return sold * flashPrice
}

// Pagination computed properties
const totalPages = computed(() => Math.ceil(flashSales.value.length / itemsPerPage))

const paginatedFlashSales = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    const end = start + itemsPerPage
    return flashSales.value.slice(start, end)
})

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
    }
}

async function fetchFlashSales() {
    loading.value = true
    error.value = ''
    try {
        const data = await getFlashSales()
        flashSales.value = Array.isArray(data) ? data : []
        // Tải thống kê thật
        const stats = await getFlashSaleStatistics()
        const mapping = {}
            ; (Array.isArray(stats) ? stats : []).forEach(s => { mapping[s.id] = s })
        realStats.value = mapping
    } catch (e) {
        error.value = e.message || 'Lỗi tải dữ liệu flash sale'
        flashSales.value = []
        push.error('Có lỗi xảy ra khi tải danh sách flash sale!')
    } finally {
        loading.value = false
    }
}
onMounted(fetchFlashSales)

async function handleDelete(id) {
    if (deleteLoading.value) return

    const result = await Swal.fire({
        title: 'Bạn có chắc chắn?',
        text: 'Bạn có chắc muốn xóa flash sale này?',
        icon: 'warning',
        showCancelButton: true,
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        reverseButtons: true
    })

    if (result.isConfirmed) {
        deleteLoading.value = true
        error.value = ''
        try {
            await deleteFlashSale(id)
            await fetchFlashSales()
            push.success('Xóa flash sale thành công!')
        } catch (e) {
            error.value = e.message || 'Xóa thất bại!'
            push.error('Có lỗi xảy ra khi xóa flash sale!')
        } finally {
            deleteLoading.value = false
        }
    }
}

const getStatusBadgeClass = (active) => {
    return active ? 'status-badge active' : 'status-badge inactive'
}

const getStatusText = (active) => {
    return active ? 'Đang diễn ra' : 'Kết thúc'
}

const toggleStatus = async (item) => {
    try {
        const next = !item.active
        await toggleFlashSaleStatus(item.id, next)
        item.active = next
        push.success(next ? 'Đã bật chiến dịch' : 'Đã ẩn chiến dịch')
    } catch (e) {
        push.error('Không thể cập nhật trạng thái')
    }
}

const formatCurrency = (v) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v || 0)

// Computed properties cho thống kê
const totalSold = computed(() => {
    return flashSales.value.reduce((total, fs) => {
        return total + (realStats.value[fs.id]?.sold_real || 0)
    }, 0)
})

const totalRevenue = computed(() => {
    return flashSales.value.reduce((total, fs) => {
        return total + (realStats.value[fs.id]?.revenue_real || 0)
    }, 0)
})

const activeFlashSales = computed(() => {
    return flashSales.value.filter(fs => fs.active).length
})

const totalProfit = computed(() => {
    return flashSales.value.reduce((total, fs) => {
        return total + (realStats.value[fs.id]?.profit_real || 0)
    }, 0)
})

const averageRevenue = computed(() => {
    const activeCount = activeFlashSales.value
    return activeCount > 0 ? totalRevenue.value / activeCount : 0
})

async function processRepeat() {
    try {
        const result = await Swal.fire({
            title: 'Xác nhận xử lý',
            text: 'Bạn có muốn xử lý tự động lặp lại Flash Sale và tăng giá tự động?',
            icon: 'question',
            showCancelButton: true,
            cancelButtonText: 'Hủy',
            confirmButtonText: 'Xử lý',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
        })

        if (result.isConfirmed) {
            loading.value = true
            const response = await processRepeatAPI()
            await fetchFlashSales()
            push.success(response.message || 'Xử lý thành công!')
        }
    } catch (e) {
        push.error('Có lỗi xảy ra khi xử lý: ' + (e.message || 'Lỗi không xác định'))
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
/* Status Badge Styles */
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    text-align: center;
    display: inline-block;
    min-width: 80px;
}

.status-badge.active {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.status-badge.inactive {
    background-color: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
</style>
