<template>
  <div v-if="flashSaleProducts.length > 0" class="bg-white rounded p-4 md:p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-3 md:mb-4 gap-3">
      <!-- Mobile & Desktop chung -->
      <div class="flex items-center justify-between w-full md:w-auto">
        <!-- Tiêu đề + icon -->
        <div class="flex items-center gap-2 md:gap-3">
          <h1 class="text-lg md:text-2xl font-bold text-blue-700">
            GIẢM SỐC {{ maxDiscountPercent }}%
          </h1>
          <img src="https://theme.hstatic.net/200000696635/1001373943/14/flashsale-hot.png?v=6" alt="Flash Sale"
            class="h-5 md:h-10 w-auto" />
        </div>

        <!-- Countdown mobile gọn -->
        <div class="flex items-center gap-1 md:hidden">
          <span class="text-xs text-blue-600">Kết thúc sau</span>
          <div class="flex items-center gap-1">
            <span class="bg-black text-white text-xs font-bold px-1 py-0.5 rounded">{{ countdown.days }}</span>
            <span class="text-black font-bold">|</span>
            <span class="bg-black text-white text-xs font-bold px-1 py-0.5 rounded">{{ countdown.hours }}</span>
            <span class="text-black font-bold">:</span>
            <span class="bg-black text-white text-xs font-bold px-1 py-0.5 rounded">{{ countdown.minutes }}</span>
            <span class="text-black font-bold">:</span>
            <span class="bg-black text-white text-xs font-bold px-1 py-0.5 rounded">{{ countdown.seconds }}</span>
          </div>
        </div>
      </div>

      <!-- Countdown desktop -->
      <div class="hidden md:flex items-center gap-2">
        <span class="text-sm md:text-base text-blue-600">Kết thúc sau</span>

        <div class="flex flex-col items-center bg-black text-white px-3 py-2 rounded">
          <span class="text-lg md:text-2xl font-bold leading-none">{{ countdown.days }}</span>
          <span class="text-[10px] md:text-xs text-gray-300">Ngày</span>
        </div>

        <div class="flex flex-col items-center bg-black text-white px-3 py-2 rounded">
          <span class="text-lg md:text-2xl font-bold leading-none">{{ countdown.hours }}</span>
          <span class="text-[10px] md:text-xs text-gray-300">Giờ</span>
        </div>

        <div class="flex flex-col items-center bg-black text-white px-3 py-2 rounded">
          <span class="text-lg md:text-2xl font-bold leading-none">{{ countdown.minutes }}</span>
          <span class="text-[10px] md:text-xs text-gray-300">Phút</span>
        </div>

        <div class="flex flex-col items-center bg-black text-white px-3 py-2 rounded">
          <span class="text-lg md:text-2xl font-bold leading-none">{{ countdown.seconds }}</span>
          <span class="text-[10px] md:text-xs text-gray-300">Giây</span>
        </div>
      </div>
    </div>

    <div v-if="flashSales.length > 1" class="flex gap-6 border-b border-t border-gray-200">
      <button v-for="(fs, idx) in flashSales" :key="fs.id" @click="selectTab(idx)"
        class="px-2 pb-2 pt-2 font-medium transition relative focus:outline-none" :class="selectedIndex === idx
          ? 'text-black border-b-2 border-black'
          : 'text-gray-400 hover:text-gray-600'">
        {{ fs.name }}
      </button>
    </div>

    <div class="relative">
      <button @click="scrollLeft"
        class="absolute left-0 top-1/2 -translate-y-1/2 z-20 bg-white shadow rounded-full w-10 h-10 flex items-center justify-center hover:bg-gray-100 transition"
        style="outline:none;">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>

      <div ref="sliderRef" class="flex gap-4 overflow-x-auto scroll-smooth" style="scrollbar-width:none;">
        <router-link v-for="product in flashSaleProducts" :key="product.id"
          :to="{ path: `/san-pham/${product.slug}`, query: { flashsale: campaignName, flash_price: product.flash_price, end_time: product.product?.end_time || product.end_time, sold: product.sold, quantity: product.flash_sale_quantity } }"
          class="relative w-64 flex-shrink-0" style="text-decoration: none; color: inherit;">
          <div class="relative overflow-hidden group pb-2 sm:pb-3 bg-white"
            :style="`width: 250px; height: 370px; margin: 17px auto; background: url('${productSaleBg}') center/cover no-repeat;`">
            <div class="relative overflow-hidden rounded-[5px]" style="width: 236px; height: 320px; margin: 5px auto;">
              <img :src="getMainImage(product)" alt="Ảnh sản phẩm"
                class="w-full h-full object-cover transition-transform group-hover:scale-105 duration-300" />
              <div
                class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
              </div>

              <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex opacity-0 translate-y-4 
            group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
                <div class="relative group/cart">
                  <button @click.prevent.stop="onQuickView(product)"
                    class="bg-white text-black w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center 
                    transition duration-200 cursor-pointer group-hover/cart:bg-black group-hover/cart:text-white rounded-l">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.35 2.7a1 1 0 00.9 1.5H19M7 13L5.4 5M16 16a1 1 0 100 2 1 1 0 000-2zm-8 0a1 1 0 100 2 1 1 0 000-2z" />
                    </svg>
                  </button>
                  <span class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 text-xs rounded bg-black text-white opacity-0 
                    group-hover/cart:opacity-100 transition duration-200 whitespace-nowrap">
                    Thêm vào giỏ
                  </span>
                </div>

                <div class="relative group/view">
                  <button @click.prevent.stop="onQuickView(product)"
                    class="bg-white text-black w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center 
                    transition duration-200 cursor-pointer group-hover/view:bg-black group-hover/view:text-white rounded-r">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <span class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 text-xs rounded bg-black text-white opacity-0 
                    group-hover/view:opacity-100 transition duration-200 whitespace-nowrap">
                    Xem nhanh
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white px-2 py-3 shadow -mt-4 z-10 relative">
            <div class="font-bold text-gray-500 text-xs uppercase mb-1 text-left">
              {{ product.category?.name || 'KHÁC' }}
            </div>
            <div class="font-semibold text-base mb-1 text-left">
              {{ truncate(product.name, 40) }}
            </div>
            <div class="flex items-center gap-2 mb-1">
              <span class="text-blue-600 font-bold">{{ formatPrice(product.flash_price) }}</span>
              <span class="line-through text-gray-400">{{ formatPrice(product.price) }}</span>
              <span class="bg-red-500 text-white px-2 py-1 rounded text-xs">
                -{{ getDiscountPercent(product.price, product.flash_price) }}%
              </span>
            </div>
            <div class="flex items-center gap-1 mb-1">
              <span v-for="(color, idx) in getUniqueColors(product)" :key="color"
                class="inline-block w-4 h-4 rounded-full border border-gray-300"
                :style="{ background: color || '#eee' }" :title="color"></span>
              <span v-if="(product.variants && getUniqueColors(product).length > 3)" class="text-xs text-gray-400">
                +{{ getUniqueColors(product).length - 3 }}
              </span>
            </div>
            <div class="w-full mt-2 px-2">
              <div class="relative h-6 bg-gray-200 rounded-full">
                <div class="absolute left-0 top-0 h-6 bg-blue-600 rounded-full"
                  :style="`width: ${getSoldPercent(product)}%; transition: width 0.3s;`"></div>
                <div class="absolute left-3 top-0 h-6 flex items-center z-10 text-white font-semibold text-sm">
                  <span style="font-size: 1.1rem; margin-right: 2px;">🔥</span>
                  Đã bán {{ product.sold ?? 0 }} sản phẩm
                </div>
              </div>
            </div>
          </div>
        </router-link>
      </div>

      <button @click="scrollRight"
        class="absolute right-0 top-1/2 -translate-y-1/2 z-20 bg-white shadow rounded-full w-10 h-10 flex items-center justify-center hover:bg-gray-100 transition"
        style="outline:none;">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>

    <div class="flex justify-center mt-4">
      <router-link to="/flash-sale"
        class="border border-[#81aacc] px-6 py-2 rounded bg-white font-semibold text-[#81aacc] hover:bg-[#81aacc] hover:text-white">
        Xem tất cả &gt;
      </router-link>
    </div>

    <!-- Quick View Modal -->
    <FlashSaleQuickView :show="showQuickView" :product="selectedProduct"
      :flash-sale-price="Number(selectedProduct?.flash_price) || 0"
      :flash-sale-percent="getDiscountPercent(selectedProduct?.price, selectedProduct?.flash_price)"
      :flash-sale-quantity="Number(selectedProduct?.flash_sale_quantity) || 0" @close="showQuickView = false"
      @add-to-cart="handleAddToCart" />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue'
import { useFlashsale } from '../../composable/useFlashsale'
import FlashSaleQuickView from '../products/FlashSaleQuickView.vue'
import productSaleBg from '../../assets/product_sale.jpg'

const flashSaleProducts = ref([])
const countdown = ref({ days: '00', hours: '00', minutes: '00', seconds: '00' })
const campaignName = ref('')
const flashSales = ref([])
const selectedIndex = ref(0)
const showQuickView = ref(false)
const selectedProduct = ref(null)
const maxDiscountPercent = ref(0)
let countdownInterval = null
let autoIncreaseInterval = null

const { getFlashSales, getMainImage } = useFlashsale()
const sliderRef = ref(null)
const emit = defineEmits(['has-flash-sale'])

function formatPrice(price) {
  return Number(price || 0).toLocaleString('vi-VN') + '₫'
}
function getDiscountPercent(price, flashPrice) {
  return price && flashPrice ? Math.round(100 - (flashPrice / price) * 100) : 0
}
function truncate(text, maxLength) {
  return text && text.length > maxLength ? text.slice(0, maxLength) + '...' : text
}
function getSoldPercent(product) {
  if (product.quantity && product.sold) {
    return Math.max(Math.round((product.sold / (product.quantity + product.sold)) * 100), 10)
  }
  return 50
}
function getUniqueColors(product) {
  const seen = new Set()
  const unique = []
  if (!product.variants) return []
  for (const v of product.variants) {
    if (v.color && !seen.has(v.color)) {
      seen.add(v.color)
      unique.push(v.color)
    }
  }
  return unique.slice(0, 3)
}
function updateCountdown(endTime) {
  const now = new Date()
  const end = new Date(endTime)
  let diff = Math.max(0, end - now)

  const days = String(Math.floor(diff / (1000 * 60 * 60 * 24))).padStart(2, '0')
  diff %= 1000 * 60 * 60 * 24
  const hours = String(Math.floor(diff / (1000 * 60 * 60))).padStart(2, '0')
  diff %= 1000 * 60 * 60
  const minutes = String(Math.floor(diff / (1000 * 60))).padStart(2, '0')
  diff %= 1000 * 60
  const seconds = String(Math.floor(diff / 1000)).padStart(2, '0')

  countdown.value = { days, hours, minutes, seconds }
}
function updateTabData() {
  if (countdownInterval) clearInterval(countdownInterval)
  const fs = flashSales.value[selectedIndex.value]

  if (fs && fs.products) {
    campaignName.value = fs.name || 'Flash Sale'
    flashSaleProducts.value = fs.products.map(p => ({
      ...p.product, // Đây là thông tin sản phẩm chính (có images, variants, etc.)
      ...p, // Thông tin flash sale (flash_price, quantity, sold, etc.)
      flash_price: Number(p.flash_price) || 0, // Convert to number
      sold: p.sold ?? 0,
      end_time: fs.end_time,
      flash_sale_quantity: Number(p.quantity) || 0,
      images: p.product?.images || [],
      variants: p.product?.variants || [],
      brand: p.product?.brand,
      category: p.product?.category,
      sku: p.product?.sku,
      slug: p.product?.slug
    }))

    // Tính phần trăm giảm giá cao nhất
    maxDiscountPercent.value = Math.max(...flashSaleProducts.value.map(p => {
      if (p.price && p.flash_price) {
        return Math.round((p.price - p.flash_price) / p.price * 100)
      }
      return 0
    }))

    if (fs.end_time) {
      updateCountdown(fs.end_time)
      countdownInterval = setInterval(() => updateCountdown(fs.end_time), 1000)
    }

    startAutoIncrease()
  } else {
    flashSaleProducts.value = []
    countdown.value = { days: '00', hours: '00', minutes: '00', seconds: '00' }
    stopAutoIncrease()
  }

  emit('has-flash-sale', flashSaleProducts.value.length > 0)
}
function selectTab(idx) {
  if (selectedIndex.value !== idx) {
    selectedIndex.value = idx
    updateTabData()
  }
}
function scrollLeft() {
  sliderRef.value?.scrollBy({ left: -300, behavior: 'smooth' })
}
function scrollRight() {
  sliderRef.value?.scrollBy({ left: 300, behavior: 'smooth' })
}
function addToCart(product) {
}
function onQuickView(product) {

  selectedProduct.value = {
    ...product,
    images: product.images || [],
    variants: product.variants || [],
    brand: product.brand,
    category: product.category,
    sku: product.sku,
    slug: product.slug,
    flash_price: Number(product.flash_price) || 0,
    flash_sale_quantity: Number(product.flash_sale_quantity) || 0
  }

  showQuickView.value = true
}
function handleAddToCart() {
  showQuickView.value = false
}
function startAutoIncrease() {
  if (autoIncreaseInterval) clearInterval(autoIncreaseInterval)
  const currentFlashSale = flashSales.value[selectedIndex.value]
  if (!currentFlashSale?.auto_increase) return

  autoIncreaseInterval = setInterval(() => {
    flashSaleProducts.value.forEach(product => {
      const currentSold = Number(product.sold) || 0
      const increaseAmount = Number(currentFlashSale.increase_amount) || 1
      const maxQuantity = Number(product.flash_sale_quantity) || 0
      if (currentSold < maxQuantity) {
        product.sold = Math.min(currentSold + increaseAmount, maxQuantity)
      }
    })
  }, 3600000)
}
function stopAutoIncrease() {
  if (autoIncreaseInterval) clearInterval(autoIncreaseInterval)
}

onMounted(async () => {
  try {
    const data = await getFlashSales()
    const all = Array.isArray(data) ? data : []
    const now = new Date()
    flashSales.value = all.filter(fs => {
      const start = new Date(fs.start_time)
      const end = new Date(fs.end_time)
      return !!fs.active && start <= now && end >= now
    })

    if (flashSales.value.length === 0) {
      flashSaleProducts.value = []
      emit('has-flash-sale', false)
      return
    }

    selectedIndex.value = 0

    updateTabData()
    emit('has-flash-sale', flashSaleProducts.value.length > 0)
    startAutoIncrease()
  } catch (e) {
    console.error('Error loading flash sales:', e)
  }
})

onUnmounted(() => {
  if (countdownInterval) clearInterval(countdownInterval)
  stopAutoIncrease()
})

watch(selectedIndex, updateTabData)
</script>

<style scoped>
.rounded-32px {
  border-radius: 32px;
}
</style>
