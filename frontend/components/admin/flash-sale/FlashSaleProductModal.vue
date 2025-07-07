<template>
  <div class="tw-fixed tw-inset-0 tw-bg-black/30 tw-z-50 tw-flex tw-items-center tw-justify-center">
    <div class="tw-bg-white tw-rounded tw-shadow-lg tw-w-full tw-max-w-5xl tw-relative tw-overflow-hidden">
      <!-- Header -->
      <div class="tw-bg-primary tw-p-4 tw-flex tw-items-center tw-justify-between">
        <h2 class="tw-text-white tw-text-xl tw-font-bold">Sản phẩm Flash Sale</h2>
        <button @click="$emit('close')" class="tw-text-white tw-text-2xl">&times;</button>
      </div>
      <!-- Body -->
      <div class="tw-p-4">
        <div class="tw-flex tw-gap-2 tw-mb-4">
          <input v-model="search" class="tw-border tw-rounded tw-px-3 tw-py-2 tw-w-80" placeholder="Gõ tên sản phẩm để tìm kiếm" />
          <button class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 tw-ml-auto"><i class="fa fa-plus"></i></button>
        </div>
        <div class="tw-overflow-x-auto">
          <table class="tw-w-full tw-bg-white tw-rounded">
            <thead>
              <tr class="tw-bg-gray-100 tw-text-gray-700">
                <th class="tw-px-2 tw-py-2">Ảnh</th>
                <th class="tw-px-2 tw-py-2">Tên sản phẩm</th>
                <th class="tw-px-2 tw-py-2">Mã SP</th>
                <th class="tw-px-2 tw-py-2">Giá thường</th>
                <th class="tw-px-2 tw-py-2">Giá KM</th>
                <th class="tw-px-2 tw-py-2">Giá Flash sale</th>
                <th class="tw-px-2 tw-py-2">Đã bán</th>
                <th class="tw-px-2 tw-py-2">Số lượng</th>
                <th class="tw-px-2 tw-py-2">SL Thật</th>
                <th class="tw-px-2 tw-py-2">#</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in filteredProducts" :key="item.id">
                <td class="tw-px-2 tw-py-2"><img :src="item.image" class="tw-w-10 tw-h-10 tw-rounded" /></td>
                <td class="tw-px-2 tw-py-2">{{ item.name }}</td>
                <td class="tw-px-2 tw-py-2">{{ item.sku }}</td>
                <td class="tw-px-2 tw-py-2">{{ item.price }}</td>
                <td class="tw-px-2 tw-py-2">{{ item.salePrice }}</td>
                <td class="tw-px-2 tw-py-2"><input v-model="item.flashPrice" class="input tw-w-20" placeholder="Giá FS" /></td>
                <td class="tw-px-2 tw-py-2">{{ item.sold }}</td>
                <td class="tw-px-2 tw-py-2"><input v-model="item.quantity" class="input tw-w-16" placeholder="SL" /></td>
                <td class="tw-px-2 tw-py-2"><input type="checkbox" v-model="item.realQty" /></td>
                <td class="tw-px-2 tw-py-2">
                  <button class="btn btn-danger" @click="remove(idx)"><i class="fa fa-minus"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="tw-flex tw-justify-end tw-mt-4">
          <button class="tw-bg-primary tw-text-white tw-px-6 tw-py-2 tw-rounded" @click="apply">Áp dụng</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
const props = defineProps({
  products: Array
})
const emit = defineEmits(['close', 'select'])
const search = ref('')
const localProducts = ref((props.products || []).map(p => ({ ...p })))
const filteredProducts = computed(() => {
  if (!search.value) return localProducts.value
  return localProducts.value.filter(p => p.name.toLowerCase().includes(search.value.toLowerCase()))
})
function remove(idx) {
  localProducts.value.splice(idx, 1)
}
function apply() {
  emit('select', localProducts.value)
  emit('close')
}
</script>

<style scoped>
.input {
  @apply tw-border tw-rounded tw-px-2 tw-py-1;
}
.btn-danger {
  @apply tw-bg-purple-600 tw-text-white tw-px-2 tw-py-1 tw-rounded;
}
</style> 