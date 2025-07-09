<template>
  <div class="tw-fixed tw-inset-0 tw-bg-black/30 tw-z-50 tw-flex tw-items-center tw-justify-center">
    <div class="tw-bg-white tw-rounded tw-shadow-lg tw-w-full tw-max-w-7xl tw-min-w-[900px] tw-relative tw-overflow-hidden">
      <!-- Header -->
      <div class="tw-bg-primary tw-p-4 tw-flex tw-items-center tw-justify-between">
        <h2 class="tw-text-white tw-text-xl tw-font-bold">Sản phẩm Flash Sale</h2>
        <button @click="$emit('close')" class="tw-text-white tw-text-2xl">&times;</button>
      </div>
      <!-- Body -->
      <div class="tw-p-4">
        <div class="tw-flex tw-gap-2 tw-mb-4">
          <input v-model="search" class="tw-border tw-rounded tw-px-3 tw-py-2 tw-w-80" placeholder="Gõ tên sản phẩm để tìm kiếm" />
          <button class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 tw-ml-auto" @click="showDiscount = true"><i class="fa fa-plus"></i></button>
        </div>
        <div v-if="loading" class="tw-text-center tw-py-4">Đang tải sản phẩm...</div>
        <div v-if="error" class="tw-text-center tw-text-red-500 tw-py-2">{{ error }}</div>
        <div class="tw-mb-4">
          <h3 class="tw-font-bold tw-mb-2">Chọn sản phẩm thêm vào Flash Sale</h3>
          <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-bg-white tw-rounded tw-shadow-sm">
              <thead>
                <tr class="tw-bg-gray-100 tw-text-gray-700">
                  <th class="tw-px-2 tw-py-2">Ảnh</th>
                  <th class="tw-px-2 tw-py-2">Tên sản phẩm</th>
                  <th class="tw-px-2 tw-py-2">Mã SP</th>
                  <th class="tw-px-2 tw-py-2">Giá</th>
                  <th class="tw-px-2 tw-py-2">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in filteredAllProducts" :key="item.id">
                  <td class="tw-px-2 tw-py-2"><img :src="getMainImage(item)" class="tw-w-10 tw-h-10 tw-rounded" /></td>
                  <td class="tw-px-2 tw-py-2">{{ item.name }}</td>
                  <td class="tw-px-2 tw-py-2">{{ item.sku }}</td>
                  <td class="tw-px-2 tw-py-2">{{ item.price }}</td>
                  <td class="tw-px-2 tw-py-2">
                    <button class="btn btn-primary" @click="addProduct(item)">Thêm</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <!-- Popup thiết lập giá giảm -->
        <div v-if="showDiscount" class="tw-absolute tw-top-24 tw-right-8 tw-bg-white tw-shadow-lg tw-rounded tw-p-4 tw-z-50 tw-w-96">
          <div class="tw-font-bold tw-mb-2">Thiết lập giảm giá</div>
          <div class="tw-flex tw-gap-2 tw-mb-2">
            <button :class="discountType === '%' ? 'tw-bg-blue-600 tw-text-white' : 'tw-bg-gray-200'" class="tw-px-3 tw-py-1 tw-rounded" @click="discountType = '%'">%</button>
            <button :class="discountType === '$' ? 'tw-bg-blue-600 tw-text-white' : 'tw-bg-gray-200'" class="tw-px-3 tw-py-1 tw-rounded" @click="discountType = '$'">$</button>
            <button :class="discountType === '₫' ? 'tw-bg-blue-600 tw-text-white' : 'tw-bg-gray-200'" class="tw-px-3 tw-py-1 tw-rounded" @click="discountType = '₫'">Đồng ₫</button>
          </div>
          <input v-model.number="discountValue" type="number" class="input tw-w-full tw-mb-2" placeholder="Nhập giá trị giảm" />
          <div class="tw-flex tw-justify-end tw-gap-2">
            <button class="tw-bg-gray-300 tw-text-black tw-px-4 tw-py-1 tw-rounded" @click="showDiscount = false">Đóng</button>
            <button class="tw-bg-blue-600 tw-text-white tw-px-4 tw-py-1 tw-rounded" @click="applyDiscount">Áp dụng</button>
          </div>
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
                <th class="tw-px-2 tw-py-2">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in filteredProducts" :key="item.id">
                <td class="tw-px-2 tw-py-2"><img :src="getMainImage(item)" class="tw-w-10 tw-h-10 tw-rounded" /></td>
                <td class="tw-px-2 tw-py-2">{{ item.name }}</td>
                <td class="tw-px-2 tw-py-2">{{ item.sku }}</td>
                <td class="tw-px-2 tw-py-2">{{ item.price }}</td>
                <td class="tw-px-2 tw-py-2">{{ item.salePrice }}</td>
                <td class="tw-px-2 tw-py-2"><input v-model="item.flashPrice" class="input tw-w-20" placeholder="Giá FS" /></td>
                <td class="tw-px-2 tw-py-2"><input v-model="item.sold" class="input tw-w-16" placeholder="Đã bán" /></td>
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
import { ref, computed, onMounted, watch } from 'vue'
import { useProducts } from '@/composables/useProducts'
import { useFlashsale } from '@/composables/useFlashsale'
const props = defineProps({
  products: Array
})
const emit = defineEmits(['close', 'select'])
const search = ref('')
const showDiscount = ref(false)
const discountType = ref('%')
const discountValue = ref(0)

const { getProducts } = useProducts()
const allProducts = ref([])
const loading = ref(false)
const error = ref('')
const { getMainImage } = useFlashsale()

// Lấy sản phẩm khi mở modal
onMounted(async () => {
  loading.value = true
  try {
    const data = await getProducts()
    allProducts.value = data.map(p => {
      let img = '/default-product.png';
      if (p.images && Array.isArray(p.images) && p.images.length > 0) {
        const mainImg = p.images.find(img => img.is_main == 1);
        img = mainImg ? mainImg.image_path : p.images[0].image_path;
      }
      return {
        ...p,
        image: img
      }
    })
  } catch (e) {
    error.value = e.message || 'Không lấy được danh sách sản phẩm'
  } finally {
    loading.value = false
  }
})

// Sản phẩm local (đã chọn hoặc đang chỉnh sửa)
const localProducts = ref([])

watch(
  [() => props.products, allProducts],
  ([val, allProds]) => {
    if (val && allProds.length > 0) {
      localProducts.value = val.map(p => {
        const prodId = String(p.product_id || p.id || (p.product && p.product.id))
        const origin = allProds.find(ap => String(ap.id) === prodId)
        const fallback = !origin && p.name ? allProds.find(ap => ap.name === p.name) : undefined
        return {
          ...(origin || fallback || {}), 
          ...p,      
          product: origin || fallback 
        }
      })
    }
  },
  { immediate: true }
)

// Khi chọn sản phẩm từ danh sách, thêm vào localProducts nếu chưa có
function addProduct(product) {
  if (!localProducts.value.find(p => p.id === product.id)) {
    localProducts.value.push({
      ...product,
      flashPrice: '',
      quantity: 100,
      sold: product.sold ?? 0
    })
  }
}

// Lọc sản phẩm theo tên
const filteredAllProducts = computed(() => {
  if (!search.value) return allProducts.value
  return allProducts.value.filter(p => p.name.toLowerCase().includes(search.value.toLowerCase()))
})

function fakePrice(idx) {
  // Giá thường 210tr, giá KM 180tr, số lượng đã bán random 10-99
  return {
    price: 210000000,
    salePrice: 180000000,
    sold: Math.floor(Math.random() * 90) + 10
  }
}
const filteredProducts = computed(() => localProducts.value)

function remove(idx) {
  localProducts.value.splice(idx, 1)
}
function apply() {
  emit('select', localProducts.value.map(p => ({
    ...p,
    image: p.image
  })))
  emit('close')
}
function applyDiscount() {
  localProducts.value.forEach(p => {
    let base = Number(p.price) || 0
    if (discountType.value === '%') {
      p.flashPrice = base ? Math.round(base * (1 - discountValue.value / 100)) : ''
    } else if (discountType.value === '$' || discountType.value === '₫') {
      p.flashPrice = base ? Math.max(0, base - discountValue.value) : ''
    }
  })
  showDiscount.value = false
}
</script>

<style scoped>
.input {
  @apply tw-border tw-rounded tw-px-2 tw-py-1;
}
.btn-danger {
  @apply tw-bg-red-600 tw-text-white tw-px-2 tw-py-1 tw-rounded;
}
</style> 