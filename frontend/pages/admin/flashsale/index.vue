<template>
  <div class="tw-bg-[#f7f8fa] tw-min-h-screen tw-p-6">
    <h1 class="tw-text-3xl tw-font-bold">Quản lý Flash Sale</h1>
    <div class="tw-text-gray-500 tw-mb-6">Quản lý các chương trình Flash Sale của bạn</div>
    <div class="tw-bg-white tw-rounded-xl tw-shadow tw-p-6">
      <div class="tw-flex tw-gap-4 tw-mb-4 tw-flex-wrap">
        <div class="tw-relative tw-flex-1 min-w-[220px]">
          <input class="tw-border tw-rounded tw-px-3 tw-py-2 tw-w-full tw-pl-10" placeholder="Tìm kiếm..." />
          <i class="fa fa-search tw-absolute tw-left-3 tw-top-1/2 tw--translate-y-1/2 tw-text-gray-400"></i>
        </div>
        <select class="tw-border tw-rounded tw-px-3 tw-py-2 min-w-[180px]">
          <option>Tất cả trạng thái</option>
          <option>Đang diễn ra</option>
          <option>Kết thúc</option>
        </select>
        <div class="tw-relative min-w-[180px]">
          <input class="tw-border tw-rounded tw-px-3 tw-py-2 tw-w-full" type="date" />
        </div>
        <NuxtLink to="/admin/flashsale/create"
          class="tw-ml-auto tw-bg-[#3BB77E] hover:tw-bg-green-600 tw-text-white tw-px-4 tw-py-2 tw-rounded flex items-center gap-2">
          <i class="fa fa-plus"></i> Thêm mới
        </NuxtLink>
      </div>
      <div v-if="loading" class="tw-text-center tw-py-8">Đang tải dữ liệu...</div>
      <div v-if="error" class="tw-text-center tw-text-red-500 tw-py-4">{{ error }}</div>
      <div class="tw-overflow-x-auto" v-if="!loading && !error">
        <table class="tw-w-full tw-bg-white tw-rounded-xl tw-shadow-sm tw-text-sm">
          <thead>
            <tr class="tw-bg-gray-50 tw-text-gray-700">
              <th class="tw-px-4 tw-py-3">#</th>
              <th class="tw-px-4 tw-py-3">Tên chiến dịch</th>
              <th class="tw-px-4 tw-py-3">Sản phẩm</th>
              <th class="tw-px-4 tw-py-3">Thời gian</th>
              <th class="tw-px-4 tw-py-3">Trạng thái</th>
              <th class="tw-px-4 tw-py-3">Lặp lại</th>
              <th class="tw-px-4 tw-py-3">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!Array.isArray(flashSales) || !flashSales[0]">
              <td colspan="7" class="tw-text-center tw-text-gray-400 tw-py-6">Không có dữ liệu</td>
            </tr>
            <tr v-for="(item, idx) in (Array.isArray(flashSales) ? flashSales : [])" :key="item.id"
              class="hover:tw-bg-gray-50 tw-transition-colors">
              <td class="tw-px-4 tw-py-2">{{ idx + 1 }}</td>
              <td class="tw-px-4 tw-py-2">{{ item.name }}</td>
              <td class="tw-px-4 tw-py-2">
                <span v-if="Array.isArray(item.products) && item.products[0]">Có sản phẩm</span>
                <span v-else>Không có sản phẩm</span>
              </td>
              <td class="tw-px-4 tw-py-2">{{ item.start_time }} ~ {{ item.end_time }}</td>
              <td class="tw-px-4 tw-py-2">
                <span v-if="item.active"
                  class="tw-bg-green-100 tw-text-green-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs">Hoạt động</span>
                <span v-else class="tw-bg-gray-200 tw-text-gray-600 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs">Kết
                  thúc</span>
              </td>
              <td class="tw-px-4 tw-py-2">
                <span v-if="item.repeat"
                  class="tw-bg-blue-100 tw-text-blue-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs">Lặp lại</span>
                <span v-else
                  class="tw-bg-gray-100 tw-text-gray-600 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs">Không</span>
              </td>
              <td class="tw-px-4 tw-py-2 tw-flex tw-gap-2">
                <NuxtLink :to="`/admin/flashsale/${item.id}/edit`"
                  class="tw-bg-white tw-text-blue-600 tw-px-2 tw-py-1 tw-rounded tw-border hover:tw-bg-blue-50 flex items-cen"
                  title="Sửa">
                  <i class="fa fa-pen-to-square fa-lg"></i>
                </NuxtLink>
                <button
                  class="tw-bg-white tw-text-red-600 tw-px-2 tw-py-1 tw-rounded tw-border hover:tw-bg-red-50 flex "
                  @click="handleDelete(item.id)" title="Xóa">
                  <i class="fa fa-trash fa-lg"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="tw-flex tw-justify-between tw-ter tw-mt-4 tw-text-sm tw-text-gray-500">
        <div>Hiển thị {{Array.isArray(flashSales) ? flashSales.map(() => 1).reduce((a, b) => a + b, 0) : 0}} trên tổng
          số {{Array.isArray(flashSales) ? flashSales.map(() => 1).reduce((a, b) => a + b, 0) : 0}} bản ghi</div>
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
