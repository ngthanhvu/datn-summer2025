<template>
  <div class="tw-bg-[#f7f8fa] tw-p-6 tw-min-h-screen">
    <div class="tw-flex tw-justify-between tw-items-center tw-mb-6 tw-pt-6 tw-pl-6">
      <div>
        <h1 class="tw-text-2xl tw-font-bold tw-mb-2">{{ form.name ? 'Cập nhật' : 'Thêm' }} chiến dịch Flash Sale</h1>
        <div class="tw-text-gray-500 tw-mb-4">Điền thông tin để tạo chương trình Flash Sale mới</div>
      </div>
    </div>
    <div class="tw-flex tw-flex-col md:tw-flex-row tw-gap-8">
      <!-- Container nhập thông tin (40%) -->
      <div class="tw-bg-white tw-rounded tw-shadow tw-p-6 md:tw-w-2/5 tw-mb-6 md:tw-mb-0">
        <div class="tw-space-y-4">
          <div>
            <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Tên chiến dịch</label>
            <input v-model="form.name" class="input" placeholder="Nhập tên chiến dịch" />
          </div>
          <div class="tw-flex tw-gap-2">
            <div class="tw-flex-1">
              <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Thời gian bắt đầu</label>
              <input type="datetime-local" v-model="form.start" class="input" />
            </div>
            <div class="tw-flex-1">
              <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Thời gian kết thúc</label>
              <input type="datetime-local" v-model="form.end" class="input" />
            </div>
          </div>
          <div class="tw-flex tw-items-center tw-gap-4">
            <div class="tw-flex tw-items-center tw-gap-2">
              <label class="tw-text-sm">Tự động lặp lại</label>
              <button @click="form.repeat = !form.repeat" :class="form.repeat ? 'tw-bg-pink-500' : 'tw-bg-gray-300'" class="tw-relative tw-w-10 tw-h-6 tw-rounded-full tw-transition-colors tw-outline-none">
                <span :class="form.repeat ? 'tw-translate-x-4 tw-bg-white' : 'tw-translate-x-0 tw-bg-white'" class="tw-absolute tw-left-0 tw-top-0 tw-w-6 tw-h-6 tw-rounded-full tw-shadow tw-transition-transform"></span>
              </button>
            </div>
            <div v-if="form.repeat" class="tw-flex tw-items-center tw-gap-2">
              <label class="tw-text-sm">Nhập số phút lặp lại</label>
              <input v-model="form.repeatMinutes" type="number" min="1" class="input tw-w-24" placeholder="Phút" />
            </div>
          </div>
          <div class="tw-flex tw-items-center tw-gap-4 tw-mt-2">
            <div class="tw-flex tw-items-center tw-gap-2">
              <label class="tw-text-sm">Tự động tăng số lượng bán</label>
              <button @click="form.autoIncrease = !form.autoIncrease" :class="form.autoIncrease ? 'tw-bg-gray-400' : 'tw-bg-gray-300'" class="tw-relative tw-w-10 tw-h-6 tw-rounded-full tw-transition-colors tw-outline-none">
                <span :class="form.autoIncrease ? 'tw-translate-x-4 tw-bg-white' : 'tw-translate-x-0 tw-bg-white'" class="tw-absolute tw-left-0 tw-top-0 tw-w-6 tw-h-6 tw-rounded-full tw-shadow tw-transition-transform"></span>
              </button>
            </div>
            <div class="tw-flex tw-items-center tw-gap-2">
              <label class="tw-text-sm">Active</label>
              <button @click="form.active = !form.active" :class="form.active ? 'tw-bg-blue-600' : 'tw-bg-gray-300'" class="tw-relative tw-w-10 tw-h-6 tw-rounded-full tw-transition-colors tw-outline-none">
                <span :class="form.active ? 'tw-translate-x-4 tw-bg-white' : 'tw-translate-x-0 tw-bg-white'" class="tw-absolute tw-left-0 tw-top-0 tw-w-6 tw-h-6 tw-rounded-full tw-shadow tw-transition-transform"></span>
              </button>
            </div>
          </div>
          <div class="tw-flex tw-gap-2 tw-mt-6">
            <button class="btn btn-primary" @click="submit">Hoàn tất</button>
            <button class="btn btn-secondary">Custom Style</button>
            <button class="btn btn-warning tw-bg-orange-500 hover:tw-bg-orange-600" @click="showProductModal = true">Sản phẩm</button>
          </div>
        </div>
      </div>
      <!-- Container hiển thị sản phẩm (60%) -->
      <div class="tw-bg-white tw-rounded tw-shadow tw-p-6 md:tw-w-3/5">
        <h3 class="tw-font-bold tw-mb-2">Sản phẩm Flash Sale</h3>
        <div class="tw-overflow-x-auto">
          <table class="tw-w-full tw-bg-white tw-rounded tw-shadow-sm">
            <thead>
              <tr class="tw-bg-gray-100 tw-text-gray-700">
                <th class="tw-px-3 tw-py-2">Ảnh</th>
                <th class="tw-px-3 tw-py-2">Tên sản phẩm</th>
                <th class="tw-px-3 tw-py-2">Mã SP</th>
                <th class="tw-px-3 tw-py-2">Giá Flash Sale</th>
                <th class="tw-px-3 tw-py-2">Số lượng</th>
                <th class="tw-px-3 tw-py-2">#</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in products" :key="item.id">
                <td class="tw-px-3 tw-py-2"><img :src="item.image" class="tw-w-10 tw-h-10 tw-rounded" /></td>
                <td class="tw-px-3 tw-py-2">{{ item.name }}</td>
                <td class="tw-px-3 tw-py-2">{{ item.sku }}</td>
                <td class="tw-px-3 tw-py-2"><input v-model="item.flashPrice" class="input tw-w-24" placeholder="Giá FS" /></td>
                <td class="tw-px-3 tw-py-2"><input v-model="item.quantity" class="input tw-w-16" placeholder="SL" /></td>
                <td class="tw-px-3 tw-py-2"><button class="btn btn-danger" @click="removeProduct(idx)">Xóa</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <FlashSaleProductModal
        v-if="showProductModal"
        :products="products"
        @close="showProductModal = false"
        @select="onSelectProducts"
      />
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'admin'
})
import { ref, watch, toRefs } from 'vue'
import FlashSaleProductModal from './FlashSaleProductModal.vue'
const props = defineProps({
  editData: Object
})
const showProductModal = ref(false)
const products = ref([
  { id: 1, name: 'Hoodie', sku: 'woo-hoodie', image: '/img.png', flashPrice: '', quantity: 100 }
])
const form = ref({
  name: '',
  start: '',
  end: '',
  buttonText: '',
  buttonUrl: '',
  repeat: false,
  repeatMinutes: 60,
  autoIncrease: false,
  active: true
})
// Nếu có editData thì fill vào form và products
watch(() => props.editData, (val) => {
  if (val) {
    form.value = {
      name: val.name || '',
      start: val.start || '',
      end: val.end || '',
      buttonText: val.buttonText || '',
      buttonUrl: val.buttonUrl || '',
      repeat: val.repeat || false,
      repeatMinutes: val.repeatMinutes || 60,
      autoIncrease: val.autoIncrease || false,
      active: val.active !== undefined ? val.active : true
    }
    if (val.products) products.value = JSON.parse(JSON.stringify(val.products))
  }
}, { immediate: true })
function addProduct(product) {
  products.value.push({ ...product, flashPrice: '', quantity: 100 })
}
function removeProduct(idx) {
  products.value.splice(idx, 1)
}
function submit() {
  // Xử lý submit form
  alert('Gửi dữ liệu lên backend')
}
function onSelectProducts(newProducts) {
  products.value = newProducts
}
</script>

<style scoped>
.input {
  @apply tw-border tw-rounded tw-px-2 tw-py-1 tw-w-full;
}
.btn {
  @apply tw-px-4 tw-py-2 tw-rounded;
}
.btn-primary { @apply tw-bg-green-500 tw-text-white; }
.btn-secondary { @apply tw-bg-purple-600 tw-text-white; }
.btn-warning { @apply tw-bg-orange-500 tw-text-white; }
.btn-danger { @apply tw-bg-red-500 tw-text-white; }
</style> 