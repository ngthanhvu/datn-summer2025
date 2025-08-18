<template>
  <div class="bg-white min-h-screen relative">
    <!-- Snowfall Effect -->
    <div class="snowfall-container">
      <div v-for="i in 50" :key="i" class="snowflake" :style="getSnowflakeStyle(i)"></div>
    </div>

    <!-- Banner DEVGANG style -->
    <div class="bg-gradient-to-r from-[#81AACC] to-blue-400 text-white py-8 relative z-10">
      <div class="container mx-auto px-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
          <div class="flex items-center gap-4">
            <div class="flex items-center gap-3">
              <div class="bg-white rounded-full p-3">
                <svg class="w-8 h-8 text-[#81AACC]" fill="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" />
                </svg>
              </div>
              <div>
                <h1 class="text-3xl font-bold">{{ shopName }}</h1>
                <p class="text-lg opacity-90">{{ shopSlogan }}</p>
              </div>
            </div>
          </div>
          <div class="flex flex-col gap-2">
            <div v-for="badge in processBadges" :key="badge.text"
              class="bg-[#81AACC] px-4 py-2 rounded flex items-center gap-2">
              <component :is="badge.icon" class="w-5 h-5" />
              <span class="text-sm font-semibold">{{ badge.text }}</span>
            </div>
          </div>
        </div>
        <div class="mt-6 text-center">
          <div class="text-lg font-semibold mb-2">{{ countdownLabel }}</div>
          <div class="flex items-center justify-center gap-2">
            <div class="bg-black text-white px-4 py-2 rounded text-2xl font-bold">{{ countdown.days }}</div>
            <span class="text-2xl font-bold">:</span>
            <div class="bg-black text-white px-4 py-2 rounded text-2xl font-bold">{{ countdown.hours }}</div>
            <span class="text-2xl font-bold">:</span>
            <div class="bg-black text-white px-4 py-2 rounded text-2xl font-bold">{{ countdown.minutes }}</div>
            <span class="text-2xl font-bold">:</span>
            <div class="bg-black text-white px-4 py-2 rounded text-2xl font-bold">{{ countdown.seconds }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab menu -->
    <div v-if="flashSales.length > 1" class="bg-white shadow-sm sticky top-0 z-20">
      <div class="container mx-auto px-4">
        <div class="flex gap-4 py-4">
          <button v-for="(fs, idx) in flashSales" :key="fs.id" @click="selectTab(idx)"
            class="px-6 py-2 rounded-full font-semibold transition-all border" :class="selectedIndex === idx
              ? 'bg-[#155DFC] text-white border-[#155DFC] shadow'
              : 'bg-white text-[#155DFC] border-[#155DFC] hover:bg-[#e6f1f8]'" style="outline:none;cursor:pointer;">
            {{ fs.name }}
          </button>
        </div>
      </div>
    </div>

    <!-- Products Section -->
    <div class="container mx-auto px-4 py-8">
      <div v-if="loading" class="flex items-center justify-center min-h-96">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-[#81AACC]"></div>
        <span class="ml-4 text-lg">Đang tải sản phẩm flash sale...</span>
      </div>

      <div v-else-if="flashSaleProducts.length === 0" class="text-center py-16">
        <div class="text-gray-500 text-xl">Không có sản phẩm flash sale nào</div>
      </div>

      <div v-else>
        <!-- Products grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
          <router-link v-for="product in flashSaleProducts" :key="product.id"
            :to="{ path: `/san-pham/${product.slug}`, query: { flashsale: campaignName, flash_price: product.flash_price, end_time: product.product?.end_time || product.end_time, sold: product.sold, quantity: product.flash_sale_quantity } }"
            class="relative" style="text-decoration: none; color: inherit;">
            <div class="relative overflow-hidden group pb-2 sm:pb-3 bg-white"
              :style="`width: 250px; height: 370px; margin: 17px auto; background: url('${productSaleBg}') center/cover no-repeat;`">
              <div class="relative overflow-hidden rounded-[5px] "
                style="width: 236px; height: 320px; margin: 5px auto;">
                <img :src="getMainImage(product)" alt="Ảnh sản phẩm"
                  class="w-full h-full object-cover transition-transform group-hover:scale-105 duration-300" />
                <div
                  class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>
              </div>
            </div>
            <div class="bg-white px-2 py-3 shadow -mt-4 z-10 relative">
              <div class="font-bold text-[#81AACC] text-xs uppercase mb-1 text-left">
                {{ product.category?.name || 'KHÁC' }}
              </div>
              <div class="font-semibold text-base mb-1 text-left">
                {{ truncate(product.name, 40) }}
              </div>
              <div class="flex items-center gap-2 mb-1">
                <span class="text-[#155DFC] font-bold">{{ formatPrice(product.flash_price) }}</span>
                <span class="line-through text-gray-400">{{ formatPrice(product.price) }}</span>
                <span class="bg-[#FB2C36] text-white px-2 py-1 rounded text-xs">
                  -{{ getDiscountPercent(product.price, product.flash_price) }}%
                </span>
              </div>
              <div class="flex items-center gap-1 mb-1">
                <span v-for="(color, idx) in getUniqueColors(product)" :key="color"
                  class="inline-block w-4 h-4 rounded-full border border-gray-300"
                  :style="{ background: color || '#eee' }" :title="color"></span>
                <span v-if="(product.variants && getUniqueColors(product).length > 3)" class="text-xs text-gray-400">+{{
                  getUniqueColors(product).length - 3 }}</span>
              </div>
              <div class="w-full mt-2 px-2">
                <div class="relative h-6 bg-gray-200 rounded-full">
                  <div class="absolute left-0 top-0 h-6 bg-[#155DFC] rounded-full"
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
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, h } from 'vue'
import { useFlashsale } from '../composable/useFlashsale'
import productSaleBg from '../assets/product_sale.jpg'
import { useHead } from '@vueuse/head';
useHead({
  title: "Flash Sale | DEVGANG",
  meta: [
    {
      name: "description",
      content: "Flash Sale"
    }
  ]
})

const shopName = ref('DEVGANG')
const shopSlogan = ref('Flash Sale - Giá Tốt, Ưu Đãi Khủng')
const countdownLabel = ref('KẾT THÚC TRONG')
const processBadges = [
  { icon: { render: () => h('svg', { class: 'w-5 h-5', fill: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { d: 'M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h4c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z' })]) }, text: 'HÀNG SẴN TẠI KHO' },
  { icon: { render: () => h('svg', { class: 'w-5 h-5', fill: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { d: 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z' })]) }, text: 'DEVGANG ĐÓNG GÓI' },
  { icon: { render: () => h('svg', { class: 'w-5 h-5', fill: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { d: 'M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h4c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4z' })]) }, text: 'NHẬN HÀNG NHANH CHÓNG' },
]

const flashSaleProducts = ref([])
const countdown = ref({ days: '00', hours: '00', minutes: '00', seconds: '00' })
const campaignName = ref('')
const flashSales = ref([])
const selectedIndex = ref(0)
const loading = ref(true)
let countdownInterval = null
const { getFlashSales, getMainImage } = useFlashsale()

// Snowfall Effect
function getSnowflakeStyle(index) {
  const left = Math.random() * 100
  const animationDuration = Math.random() * 3 + 2
  const animationDelay = Math.random() * 5
  const size = Math.random() * 4 + 2
  return {
    left: left + '%',
    animationDuration: animationDuration + 's',
    animationDelay: animationDelay + 's',
    width: size + 'px',
    height: size + 'px'
  }
}

function formatPrice(price) {
  if (!price) return ''
  return Number(price).toLocaleString('vi-VN') + '₫'
}

function getDiscountPercent(price, flashPrice) {
  if (!price || !flashPrice) return 0
  return Math.round(100 - (flashPrice / price) * 100)
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

function getSoldPercent(product) {
  if (product.quantity && product.sold) {
    let percent = Math.round((product.sold / (product.quantity + product.sold)) * 100)
    return Math.max(percent, 10)
  }
  return 50
}

function getUniqueColors(product) {
  if (!product.variants) return []
  const seen = new Set()
  const unique = []
  for (const v of product.variants) {
    if (v.color && !seen.has(v.color)) {
      seen.add(v.color)
      unique.push(v.color)
    }
  }
  return unique.slice(0, 3)
}

function addToCart(product) {
}

function onQuickView(product) {
}

function selectTab(idx) {
  if (selectedIndex.value === idx) return
  selectedIndex.value = idx
  updateTabData()
}

function updateTabData() {
  if (countdownInterval) clearInterval(countdownInterval)
  const fs = flashSales.value[selectedIndex.value]
  if (fs && fs.products) {
    campaignName.value = fs.name || 'Flash Sale'
    flashSaleProducts.value = fs.products.map(p => ({
      ...p.product,
      ...p,
      flash_price: p.flash_price,
      sold: p.sold ?? 0,
      end_time: fs.end_time,
      flash_sale_quantity: p.quantity
    }))
    updateCountdown(fs.end_time)
    countdownInterval = setInterval(() => updateCountdown(fs.end_time), 1000)
    // Restart auto increase cho flash sale mới
    startAutoIncrease()
  } else {
    flashSaleProducts.value = []
    stopAutoIncrease()
  }
}

function truncate(text, maxLength) {
  if (!text) return ''
  return text.length > maxLength ? text.slice(0, maxLength) + '...' : text
}

// Auto increase sold quantity
let autoIncreaseInterval = null

function startAutoIncrease() {
  // Dừng interval cũ nếu có
  if (autoIncreaseInterval) {
    clearInterval(autoIncreaseInterval)
  }
  // Bắt đầu interval mới
  autoIncreaseInterval = setInterval(() => {
    const currentFlashSale = flashSales.value[selectedIndex.value]
    if (currentFlashSale && currentFlashSale.auto_increase) {
      flashSaleProducts.value.forEach(product => {
        const currentSold = Number(product.sold) || 0
        const increaseAmount = Number(currentFlashSale.increase_amount) || 1
        const maxQuantity = Number(product.flash_sale_quantity) || 0
        if (currentSold < maxQuantity) {
          const newSold = Math.min(currentSold + increaseAmount, maxQuantity)
          product.sold = newSold
        }
      })
    }
  }, 3600000) // 1 giờ (3600000 ms)
}

function stopAutoIncrease() {
  if (autoIncreaseInterval) {
    clearInterval(autoIncreaseInterval)
    autoIncreaseInterval = null
  }
}

onMounted(async () => {
  try {
    loading.value = true
    const data = await getFlashSales()
    const all = Array.isArray(data) ? data : []
    const now = new Date()
    // Chỉ giữ các campaign đang bật và còn trong khung giờ
    flashSales.value = all.filter(fs => {
      const start = new Date(fs.start_time)
      const end = new Date(fs.end_time)
      return !!fs.active && start <= now && end >= now
    })

    if (flashSales.value.length === 0) {
      flashSaleProducts.value = []
      loading.value = false
      return
    }

    selectedIndex.value = 0
    updateTabData()
    // Bắt đầu auto increase cho flash sale đang active
    startAutoIncrease()
  } catch (error) {
    console.error('Error loading flash sales:', error)
  } finally {
    loading.value = false
  }
})

watch(selectedIndex, updateTabData)

// Cleanup khi component unmount
import { onUnmounted } from 'vue'

onUnmounted(() => {
  if (countdownInterval) clearInterval(countdownInterval)
  stopAutoIncrease()
})
</script>

<style scoped>
.snowfall-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  pointer-events: none;
  z-index: 1;
  overflow: hidden;
}

.snowflake {
  position: absolute;
  background: white;
  border-radius: 50%;
  opacity: 0.8;
  animation: snowfall linear infinite;
}

@keyframes snowfall {
  0% {
    transform: translateY(-10px) rotate(0deg);
    opacity: 0;
  }

  10% {
    opacity: 0.8;
  }

  90% {
    opacity: 0.8;
  }

  100% {
    transform: translateY(100vh) rotate(360deg);
    opacity: 0;
  }
}
</style>
