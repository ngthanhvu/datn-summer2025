<template>
  <div class="tw-container tw-mx-auto tw-px-4 tw-py-8">
    <div class="tw-flex tw-gap-8">
      <!-- Sidebar filter -->
      <aside :class="[
        'tw-w-72 tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6',
        'md:tw-block',
        showFilter ? 'tw-fixed tw-top-0 tw-left-0 tw-h-screen tw-z-[999] tw-overflow-y-auto' : 'tw-hidden'
      ]">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-4 md:tw-hidden">
          <h2 class="tw-text-lg tw-font-semibold">Bộ lọc</h2>
          <button @click="showFilter = false" class="tw-text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-6 tw-w-6" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <h2 class="tw-text-lg tw-font-semibold tw-mb-4">Tất cả sản phẩm</h2>
        <!-- Màu sắc -->
        <div class="tw-mb-6">
          <h3 class="tw-font-medium tw-mb-2 tw-text-sm">MÀU SẮC</h3>
          <div class="tw-flex tw-flex-col tw-gap-2">
            <label class="tw-flex tw-items-center tw-gap-2"><input type="radio" name="color" class="tw-form-radio" />
              Đỏ</label>
            <label class="tw-flex tw-items-center tw-gap-2"><input type="radio" name="color" class="tw-form-radio" />
              Cam</label>
            <label class="tw-flex tw-items-center tw-gap-2"><input type="radio" name="color" class="tw-form-radio" />
              Tím</label>
            <label class="tw-flex tw-items-center tw-gap-2"><input type="radio" name="color" class="tw-form-radio" />
              Xám</label>
            <label class="tw-flex tw-items-center tw-gap-2"><input type="radio" name="color" class="tw-form-radio" />
              Trắng</label>
            <button class="tw-text-xs tw-text-blue-500 tw-mt-1">Xem thêm</button>
          </div>
        </div>
        <!-- Mức giá -->
        <div class="tw-mb-6">
          <h3 class="tw-font-medium tw-mb-2 tw-text-sm">MỨC GIÁ</h3>
          <div class="tw-flex tw-flex-col tw-gap-2 tw-text-sm">
            <label><input type="checkbox" class="tw-form-checkbox" /> Dưới 1.000.000đ</label>
            <label><input type="checkbox" class="tw-form-checkbox" /> 1.000.000đ - 2.000.000đ</label>
            <label><input type="checkbox" class="tw-form-checkbox" /> 2.000.000đ - 3.000.000đ</label>
            <label><input type="checkbox" class="tw-form-checkbox" /> 3.000.000đ - 5.000.000đ</label>
            <label><input type="checkbox" class="tw-form-checkbox" /> 5.000.000đ - 10.000.000đ</label>
            <label><input type="checkbox" class="tw-form-checkbox" /> Giá trên 10.000.000đ</label>
          </div>
        </div>
        <!-- Dịch vụ giao hàng -->
        <div>
          <h3 class="tw-font-medium tw-mb-2 tw-text-sm">DỊCH VỤ GIAO HÀNG</h3>
          <div class="tw-flex tw-flex-col tw-gap-2 tw-text-sm">
            <label><input type="checkbox" class="tw-form-checkbox" /> Miễn phí giao hàng</label>
            <label><input type="checkbox" class="tw-form-checkbox" /> Giao hàng nhanh 4h</label>
            <label><input type="checkbox" class="tw-form-checkbox" /> Giao hàng tiết kiệm</label>
          </div>
        </div>
      </aside>
      <!-- Main content -->
      <main class="tw-flex-1">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
          <!-- Filter button for mobile -->
          <button @click="showFilter = !showFilter"
            class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-text-gray-600 md:tw-hidden">
            <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Lọc sản phẩm
          </button>
          <div class="tw-flex tw-items-center tw-gap-2">
            <span class="tw-text-sm">Sắp xếp:</span>
            <select class="tw-border tw-rounded tw-px-2 tw-py-1 tw-text-sm">
              <option>Tên A → Z</option>
              <option>Tên Z → A</option>
              <option>Giá tăng dần</option>
              <option>Giá giảm dần</option>
            </select>
          </div>
        </div>
        <!-- Product grid -->
        <div class="tw-grid tw-grid-cols-2 sm:tw-grid-cols-2 md:tw-grid-cols-3 lg:tw-grid-cols-4 tw-gap-4">
          <Card v-for="i in 8" :key="i" />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import Card from '~/components/home/Card.vue'
import { ref } from 'vue'

const showFilter = ref(false)

useHead({
  title: 'Sản phẩm - DEVGANG',
  meta: [
    { name: 'description', content: 'Sản phẩm - DEVGANG' },
  ],
});
</script>

<style scoped>
@media (max-width: 768px) {
  .tw-container {
    padding-bottom: 20px;
  }
}
</style>