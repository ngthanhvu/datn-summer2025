<template>
    <div class="bg-[#f7f8fa] min-h-screen p-6">
        <h1 class="text-3xl font-bold">Quản lý Flash Sale</h1>
        <div class="text-gray-500 mb-6">Quản lý các chương trình Flash Sale của bạn</div>
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex gap-4 mb-4 flex-wrap">
                <div class="relative flex-1 min-w-[220px]">
                    <input class="border border-gray-300 rounded px-3 py-2 w-full pl-10" placeholder="Tìm kiếm..." />
                    <i class="fa fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <select class="border border-gray-300 rounded px-3 py-2 min-w-[180px]">
                    <option>Tất cả trạng thái</option>
                    <option>Đang diễn ra</option>
                    <option>Kết thúc</option>
                </select>
                <div class="relative min-w-[180px]">
                    <input class="border border-gray-300 rounded px-3 py-2 w-full" type="date" />
                </div>
                <router-link to="/admin/flashsale/create"
                    class="ml-auto bg-[#3BB77E] hover:bg-green-600 text-white px-4 py-2 rounded flex items-center gap-2 cursor-pointer">
                    <i class="fa fa-plus"></i> Thêm mới
                </router-link>
            </div>
            <div v-if="loading" class="text-center py-8">Đang tải dữ liệu...</div>
            <div v-if="error" class="text-center text-red-500 py-4">{{ error }}</div>
            <div class="overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white"
                v-if="!loading && !error">
                <table class="w-full bg-white rounded-xl shadow-sm text-sm">
                    <thead>
                        <tr class="border-b border-gray-300">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Tên chiến dịch</th>
                            <th class="px-4 py-3">Sản phẩm</th>
                            <th class="px-4 py-3">Thời gian</th>
                            <th class="px-4 py-3 text-center">Trạng thái</th>
                            <th class="px-4 py-3">Lặp lại</th>
                            <th class="px-4 py-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!Array.isArray(flashSales) || !flashSales[0]">
                            <td colspan="7" class="text-center text-gray-400 py-6">Không có dữ liệu</td>
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
                            <td class="px-4 py-2 text-center">
                                <button
                                    :class="['w-10 h-6 rounded-full relative transition-colors', item.active ? 'bg-primary' : 'bg-gray-300']"
                                    @click="toggleStatus(item)" :aria-pressed="item.active"
                                    style="background-color: #3bb77e">
                                    <span
                                        :class="['absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform', item.active ? 'translate-x-4' : '']"></span>
                                </button>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <span v-if="item.repeat"
                                    class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs">Lặp lại</span>
                                <span v-else
                                    class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs">Không</span>
                            </td>
                            <td class="px-4 py-2 flex justify-center items-center">
                                <div class="flex gap-2">
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

            <!-- Pagination -->
            <div v-if="!loading && !error && totalPages > 1" class="flex justify-between items-center mt-6">
                <div class="text-sm text-gray-600">
                    Hiển thị {{ paginatedFlashSales.length }} trên tổng số {{ flashSales.length }} bản ghi
                </div>
                <div class="flex gap-2">
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
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useFlashsale } from '../../../composable/useFlashsale'
import { push } from 'notivue'

const { getFlashSales, deleteFlashSale } = useFlashsale()
const flashSales = ref([])
const loading = ref(false)
const error = ref('')
const deleteLoading = ref(false)
const currentPage = ref(1)
const itemsPerPage = 10

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
    if (confirm('Bạn có chắc muốn xóa flash sale này?')) {
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

const toggleStatus = async (item) => {
    const newStatus = item.active ? 0 : 1
    try {
        await updateFlashSaleStatus(item.id, newStatus)
        item.active = newStatus
        push.success('Cập nhật trạng thái flash sale thành công!')
    } catch (e) {
        push.error('Có lỗi xảy ra khi cập nhật trạng thái flash sale!')
    }
}

// Hàm giả lập gọi API cập nhật trạng thái
const updateFlashSaleStatus = async (id, status) => {
    // TODO: Thay bằng gọi API thực tế
    return new Promise((resolve) => setTimeout(resolve, 500))
}
</script>
