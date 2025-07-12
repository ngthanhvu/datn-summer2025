<template>
  <div class="tw-bg-[#f7f8fa] tw-p-6 tw-min-h-screen tw-text-sm">
    <div class="tw-flex tw-justify-between tw-items-center tw-mb-6 tw-pt-6 tw-pl-6">
      <div>
        <h1 class="tw-text-2xl tw-font-bold tw-mb-2">{{ props.editData ? 'Cập nhật' : 'Thêm' }} chiến dịch Flash Sale</h1>
        <div class="tw-text-gray-500 tw-mb-4">Điền thông tin để {{ props.editData ? 'cập nhật' : 'tạo' }} chương trình Flash Sale</div>
      </div>
    </div>
    <div v-if="error" class="tw-text-red-500 tw-mb-2">{{ error }}</div>
    <div v-if="success" class="tw-text-green-600 tw-mb-2">{{ success }}</div>
    <div class="tw-flex tw-flex-col md:tw-flex-row tw-gap-8">
      <div class="tw-bg-white tw-rounded tw-shadow tw-p-6 md:tw-w-2/5 tw-mb-6 md:tw-mb-0 tw-text-sm">
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
            <button class="btn btn-primary" :disabled="loading" @click="submit">{{ loading ? 'Đang lưu...' : (props.editData ? 'Cập nhật' : 'Hoàn tất') }}</button>
            <button class="btn btn-warning tw-bg-orange-500 hover:tw-bg-orange-600" @click="goToSelectProducts">Thêm sản phẩm</button>
          </div>
        </div>
      </div>
      <div class="tw-bg-white tw-rounded tw-shadow tw-p-6 md:tw-w-3/5 tw-text-sm">
        <h3 class="tw-font-bold tw-mb-2">Sản phẩm Flash Sale</h3>
        <div class="tw-overflow-x-auto">
          <table class="tw-w-full tw-bg-white tw-rounded tw-shadow-sm tw-text-sm">
            <thead>
              <tr class="tw-bg-gray-100 tw-text-gray-700">
                <th class="tw-px-3 tw-py-2">Ảnh</th>
                <th class="tw-px-3 tw-py-2">Tên sản phẩm</th>
                <th class="tw-px-3 tw-py-2">Giá thường</th>
                <th class="tw-px-3 tw-py-2">Giá KM</th>
                <th class="tw-px-3 tw-py-2">Giá Flash Sale</th>
                <th class="tw-px-3 tw-py-2">Đã bán</th>
                <th class="tw-px-3 tw-py-2">Số lượng</th>
                <th class="tw-px-3 tw-py-2">SL Thật</th>
                <th class="tw-px-3 tw-py-2">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in paginatedProducts" :key="item.id">
                <td class="tw-px-3 tw-py-2"><img :src="getMainImage(item)" class="tw-w-10 tw-h-10 tw-rounded" /></td>
                <td class="tw-px-3 tw-py-2">{{ truncate(item.name) }}</td>
                <td class="tw-px-3 tw-py-2">{{ item.product?.price ? formatPrice(item.product.price) : (item.price ? formatPrice(item.price) : 'N/A') }}</td>
                <td class="tw-px-3 tw-py-2">{{ item.product?.discount_price ? formatPrice(item.product.discount_price) : (item.discount_price ? formatPrice(item.discount_price) : 'N/A') }}</td>
                <td class="tw-px-3 tw-py-2"><input v-model="item.flashPrice" class="input tw-w-24" placeholder="Giá FS" /></td>
                <td class="tw-px-3 tw-py-2"><input v-model="item.sold" class="input tw-w-16" placeholder="Đã bán" /></td>
                <td class="tw-px-3 tw-py-2"><input v-model="item.quantity" class="input tw-w-16" placeholder="SL" /></td>
                <td class="tw-px-3 tw-py-2"><input type="checkbox" v-model="item.realQty" /></td>
                <td class="tw-px-3 tw-py-2">
                  <button class="btn btn-danger" @click="removeProduct(idx)" title="Xóa">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="tw-flex tw-justify-center tw-items-center tw-gap-2 tw-mt-2">
            <button class="tw-px-3 tw-py-1 tw-rounded tw-bg-gray-200" :disabled="productPage === 1" @click="productPage > 1 && (productPage--)">&lt;</button>
            <span>Trang {{ productPage }} / {{ productTotalPages }}</span>
            <button class="tw-px-3 tw-py-1 tw-rounded tw-bg-gray-200" :disabled="productPage === productTotalPages" @click="productPage < productTotalPages && (productPage++)">&gt;</button>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import { useFlashsale } from '@/composables/useFlashsale'
import { useProducts } from '@/composables/useProducts'
import { useRouter } from 'vue-router'
function formatPrice(price) {
  if (price === null || price === undefined || price === '') return 'N/A'
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(Number(price))
}
function truncate(str, n = 30) {
  if (!str) return ''
  return str.length > n ? str.slice(0, n) + '...' : str
}
const props = defineProps({
  editData: Object
})
const products = ref([])
const productPage = ref(1)
const productPageSize = 5
const productTotalPages = computed(() => Math.ceil(products.value.length / productPageSize))
const paginatedProducts = computed(() => {
  const start = (productPage.value - 1) * productPageSize
  return products.value.slice(start, start + productPageSize)
})
const form = ref({
  name: '',
  start: '',
  end: '',
  repeat: false,
  repeatMinutes: 60,
  autoIncrease: false,
  active: true
})
const loading = ref(false)
const error = ref('')
const success = ref('')
const { createFlashSale, updateFlashSale, getMainImage } = useFlashsale()
const { getProducts } = useProducts()
const allProducts = ref([])
const router = useRouter()
function goToSelectProducts() {
  localStorage.setItem('flashsale_form_data', JSON.stringify(form.value));
  if (props.editData && props.editData.id) {
    router.push(`/admin/flashsale/select-products?flashSaleId=${props.editData.id}`)
  } else {
    router.push('/admin/flashsale/select-products')
  }
}
onMounted(async () => {
  allProducts.value = await getProducts()
  const savedForm = localStorage.getItem('flashsale_form_data');
  if (savedForm) {
    try {
      Object.assign(form.value, JSON.parse(savedForm));
      localStorage.removeItem('flashsale_form_data');
    } catch {}
  }
  const selected = localStorage.getItem('flashsale_selected_products')
  const flashSaleId = props.editData?.id
  const editSelected = flashSaleId ? localStorage.getItem(`flashsale_edit_${flashSaleId}`) : null
  
  const productsToLoad = editSelected || selected
  
  if (productsToLoad) {
    try {
      products.value = JSON.parse(productsToLoad)
      localStorage.removeItem('flashsale_selected_products')
      if (editSelected) {
        localStorage.removeItem(`flashsale_edit_${flashSaleId}`)
      }
    } catch {}
  }
})
watch(() => props.editData, (val) => {
  if (val) {
    form.value = {
      name: val.name || '',
      start: val.start_time ? val.start_time.slice(0, 16) : (val.start || ''),
      end: val.end_time ? val.end_time.slice(0, 16) : (val.end || ''),
      repeat: val.repeat || false,
      repeatMinutes: val.repeat_minutes || val.repeatMinutes || 60,
      autoIncrease: val.auto_increase || val.autoIncrease || false,
      active: val.active !== undefined ? val.active : true
    }
    if (val.products && val.products.length > 0 && products.value.length === 0) {
      products.value = val.products.map(p => {
        const productData = p.product || {}
        return {
          id: p.product_id || productData.id,
          product_id: p.product_id || productData.id,
          name: productData.name || p.name,
          price: productData.price || p.price,
          discount_price: productData.discount_price || p.discount_price,
          flashPrice: p.flash_price || p.flashPrice || '',
          quantity: p.quantity || 100,
          sold: p.sold || 0,
          realQty: p.real_qty !== undefined ? p.real_qty : true,
          image: productData.main_image?.image_path || productData.image || '/default-product.png',
          product: productData
        }
      })
    }
  }
}, { immediate: true })

function addProduct(product) {
  const existingIndex = products.value.findIndex(p => p.id === product.id)
  if (existingIndex === -1) {
    products.value.push({ 
      ...product, 
      flashPrice: '', 
      quantity: 100,
      sold: 0,
      realQty: true
    })
  }
}
function removeProduct(idx) {
  products.value.splice(idx, 1)
}
async function submit() {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    const payload = {
      name: form.value.name,
      start_time: form.value.start,
      end_time: form.value.end,
      repeat: form.value.repeat,
      repeat_minutes: form.value.repeatMinutes,
      auto_increase: form.value.autoIncrease,
      active: form.value.active,
      products: products.value.map(p => ({
        product_id: p.product_id ? p.product_id : p.id,
        flash_price: p.flashPrice !== '' ? Number(p.flashPrice) : '',
        quantity: Number(p.quantity) || 0,
        sold: Number(p.sold) || 0,
        real_qty: p.realQty !== undefined ? p.realQty : true
      }))
    }
    let res
    if (props.editData && props.editData.id) {
      res = await updateFlashSale(props.editData.id, payload)
      success.value = 'Cập nhật flash sale thành công!'
    } else {
      res = await createFlashSale(payload)
      success.value = 'Tạo flash sale thành công!'
      setTimeout(() => router.push('/admin/flashsale'), 1000)
    }
    // Xóa dữ liệu form tạm sau khi submit thành công
    localStorage.removeItem('flashsale_form_data');
  } catch (e) {
    error.value = e.message || 'Có lỗi xảy ra khi lưu flash sale'
  } finally {
    loading.value = false
  }
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