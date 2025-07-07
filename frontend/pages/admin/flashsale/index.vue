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
      <div class="tw-overflow-x-auto">
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
            <tr v-if="flashSales.length === 0">
              <td colspan="7" class="tw-text-center tw-text-gray-400 tw-py-6">Không có dữ liệu</td>
            </tr>
            <tr v-for="item in flashSales" :key="item.id">
              <td class="tw-px-4 tw-py-2">{{ item.id }}</td>
              <td class="tw-px-4 tw-py-2">{{ item.name }}</td>
              <td class="tw-px-4 tw-py-2 tw-text-center">{{ item.productCount }}</td>
              <td class="tw-px-4 tw-py-2">{{ item.time }}</td>
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
                <button class="tw-bg-red-500 tw-text-white tw-px-3 tw-py-1 tw-rounded">Xóa</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="tw-flex tw-justify-between tw-items-center tw-mt-4 tw-text-sm tw-text-gray-500">
        <div>Hiển thị {{ flashSales.length }} trên tổng số {{ flashSales.length }} bản ghi</div>
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
const flashSales = [
  { id: 63, name: 'Flash sale cuối năm', productCount: 7, time: '00:00 07/10/2021 ~ 23:59 07/10/2021', active: false, repeat: false },
  { id: 62, name: 'Flash sale cuối năm', productCount: 7, time: '00:00 01/09/2021 ~ 00:00 31/10/2021', active: true, repeat: true },
]
</script>
