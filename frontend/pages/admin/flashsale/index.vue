<template>
  <div class="tw-bg-[#f7f8fa] tw-min-h-screen tw-p-6">
    <h1 class="tw-text-3xl tw-font-bold">Quản lý Flash Sale</h1>
    <div class="tw-text-gray-500 tw-mb-6">Quản lý các chương trình Flash Sale của bạn</div>
    <div class="tw-bg-white tw-rounded tw-shadow tw-p-4 tw-mb-4">
      <div class="tw-flex tw-gap-4 tw-mb-4 tw-flex-wrap">
        <input class="tw-border tw-rounded tw-px-3 tw-py-2 tw-w-64" placeholder="Tìm kiếm..." />
        <select class="tw-border tw-rounded tw-px-3 tw-py-2">
          <option>Tất cả trạng thái</option>
          <option>Đang diễn ra</option>
          <option>Kết thúc</option>
        </select>
        <input class="tw-border tw-rounded tw-px-3 tw-py-2" type="date" />
        <NuxtLink to="/admin/flashsale/create" class="tw-ml-auto tw-bg-green-500 hover:tw-bg-green-600 tw-text-white tw-px-4 tw-py-2 tw-rounded flex items-center gap-2">
          <i class="fa fa-plus"></i> Thêm mới
        </NuxtLink>
      </div>
      <div v-if="loading" class="tw-text-center tw-py-8">Đang tải dữ liệu...</div>
      <div v-if="error" class="tw-text-center tw-text-red-500 tw-py-4">{{ error }}</div>
      <div class="tw-overflow-x-auto" v-if="!loading && !error">
        <table class="tw-w-full tw-bg-white tw-rounded">
          <thead>
            <tr class="tw-bg-gray-100 tw-text-gray-700">
              <th class="tw-px-4 tw-py-2">#</th>
              <th class="tw-px-4 tw-py-2">Tên chiến dịch</th>
              <th class="tw-px-4 tw-py-2">Sản phẩm</th>
              <th class="tw-px-4 tw-py-2">Thời gian</th>
              <th class="tw-px-4 tw-py-2">Trạng thái</th>
              <th class="tw-px-4 tw-py-2">Lặp lại</th>
              <th class="tw-px-4 tw-py-2">#</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!Array.isArray(flashSales) || !flashSales[0]">
              <td colspan="7" class="tw-text-center tw-text-gray-400 tw-py-6">Không có dữ liệu</td>
            </tr>
            <tr v-for="item in (Array.isArray(flashSales) ? flashSales : [])" :key="item.id">
              <td class="tw-px-4 tw-py-2">{{ item.id }}</td>
              <td class="tw-px-4 tw-py-2">{{ item.name }}</td>
              <td class="tw-px-4 tw-py-2 tw-text-center">
                <span v-if="Array.isArray(item.products) && item.products[0]">Có sản phẩm</span>
                <span v-else>Không có sản phẩm</span>
              </td>
              <td class="tw-px-4 tw-py-2">{{ item.start_time }} ~ {{ item.end_time }}</td>
              <td class="tw-px-4 tw-py-2 tw-text-center">
                <span v-if="item.active" class="tw-bg-green-500 tw-text-white tw-px-3 tw-py-1 tw-rounded tw-text-xs">Đang diễn ra</span>
                <span v-else class="tw-bg-gray-400 tw-text-white tw-px-3 tw-py-1 tw-rounded tw-text-xs">Kết thúc</span>
              </td>
              <td class="tw-px-4 tw-py-2 tw-text-center">
                <span v-if="item.repeat" class="tw-bg-red-500 tw-text-white tw-px-3 tw-py-1 tw-rounded tw-text-xs">Lặp lại</span>
                <span v-else class="tw-bg-gray-200 tw-text-gray-600 tw-px-3 tw-py-1 tw-rounded tw-text-xs">Không</span>
              </td>
              <td class="tw-px-4 tw-py-2 tw-flex tw-gap-2 tw-justify-center">
                <NuxtLink :to="`/admin/flashsale/${item.id}/edit`" class="tw-bg-purple-600 tw-text-white tw-px-3 tw-py-1 tw-rounded">Sửa</NuxtLink>
                <button class="tw-bg-red-500 tw-text-white tw-px-3 tw-py-1 tw-rounded" @click="handleDelete(item.id)">Xóa</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="tw-flex tw-justify-between tw-items-center tw-mt-4 tw-text-sm tw-text-gray-500">
        <div>Hiển thị {{ Array.isArray(flashSales) ? flashSales.map(() => 1).reduce((a, b) => a + b, 0) : 0 }} trên tổng số {{ Array.isArray(flashSales) ? flashSales.map(() => 1).reduce((a, b) => a + b, 0) : 0 }} bản ghi</div>
        <div class="tw-flex tw-gap-2">
          <button class="tw-px-2 tw-py-1 tw-rounded tw-border tw-bg-white" disabled>&lt;</button>
          <span>Trang 1 / 1</span>
          <button class="tw-px-2 tw-py-1 tw-rounded tw-border tw-bg-white" disabled>&gt;</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'admin'
})
useHead({
    title: "Quản lí Flash sale"
})
import { ref, onMounted } from 'vue'
import { useFlashsale } from '@/composables/useFlashsale'
const { getFlashSales, deleteFlashSale } = useFlashsale()
const flashSales = ref([])
const loading = ref(false)
const error = ref('')
const deleteLoading = ref(false)

async function fetchFlashSales() {
  loading.value = true
  error.value = ''
  try {
    const data = await getFlashSales()
    flashSales.value = Array.isArray(data) ? data : []
  } catch (e) {
    error.value = e.message || 'Lỗi tải dữ liệu flash sale'
    flashSales.value = []
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
      alert('Đã xóa thành công!')
    } catch (e) {
      error.value = e.message || 'Xóa thất bại!'
      alert(error.value)
    } finally {
      deleteLoading.value = false
    }
  }
}
</script>
