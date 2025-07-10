<template>
  <div class="tw-container tw-mx-auto tw-px-4 tw-py-6">
    <div class="tw-bg-white tw-rounded tw-p-6 tw-mb-6">
      <div class="tw-flex tw-items-center tw-gap-3 tw-mb-4">
        <h1 class="tw-text-2xl tw-font-bold tw-text-blue-700">Tất cả Flash Sale</h1>
        <img src="https://theme.hstatic.net/200000696635/1001373943/14/flashsale-hot.png?v=6" alt="Flash Sale" class="tw-h-10 tw-w-auto" />
      </div>
      <!-- Tab menu -->
      <div v-if="flashSales.length > 1" class="tw-flex tw-gap-6 tw-border-b tw-mb-4 tw-ml-2">
        <button
          v-for="(fs, idx) in flashSales"
          :key="fs.id"
          @click="selectTab(idx)"
          class="tw-pb-2 tw-font-medium tw-transition tw-relative"
          :class="selectedIndex === idx ? 'tw-text-black tw-border-b-2 tw-border-black' : 'tw-text-gray-400'"
          style="background:none;border:none;outline:none;cursor:pointer;"
        >
          {{ fs.name }}
        </button>
      </div>
      <div v-if="flashSales.length" class="tw-mb-4 tw-flex tw-items-center tw-gap-4">
        <span class="tw-font-semibold">Thời gian:</span>
        <span>{{ formatDate(currentSale?.start_time) }} - {{ formatDate(currentSale?.end_time) }}</span>
        <span v-if="currentSale?.active" class="tw-bg-green-100 tw-text-green-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs">Đang diễn ra</span>
        <span v-else class="tw-bg-gray-200 tw-text-gray-600 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs">Đã kết thúc</span>
      </div>
      <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-3 lg:tw-grid-cols-5 tw-gap-4">
        <NuxtLink
          v-for="product in flashSaleProducts"
          :key="product.id"
          :to="{ path: `/chi-tiet/${product.slug}`, query: { flashsale: currentSale?.name, flash_price: product.flash_price, end_time: product.product?.end_time || product.end_time, sold: product.sold, quantity: product.flash_sale_quantity } }"
          class="tw-relative tw-flex-shrink-0"
          style="text-decoration: none; color: inherit;"
        >
          <div
            class="tw-relative tw-overflow-hidden tw-group tw-pb-2 sm:tw-pb-3 tw-bg-white"
            :style="`width: 250px; height: 370px; margin: 17px auto; background: url('${productSaleBg}') center/cover no-repeat;`"
          >
            <div class="tw-relative tw-overflow-hidden tw-rounded-[5px] " style="width: 236px; height: 320px; margin: 5px auto;">
              <img
                :src="getMainImage(product)"
                alt="Ảnh sản phẩm"
                class="tw-w-full tw-h-full tw-object-cover tw-transition-transform group-hover:tw-scale-105 tw-duration-300"
              />
              <!-- Hover overlay -->
              <div class="tw-absolute tw-inset-0 tw-bg-black/10 tw-opacity-0 group-hover:tw-opacity-100 tw-transition-opacity tw-duration-300"></div>
            </div>
          </div>
          <div class="tw-bg-white tw-px-2 tw-py-3 tw-shadow -tw-mt-4 tw-z-10 tw-relative">
            <div class="tw-font-bold tw-text-gray-500 tw-text-xs tw-uppercase tw-mb-1 tw-text-left">
              {{ product.category?.name || 'KHÁC' }}
            </div>
            <div class="tw-font-semib tw-text-base tw-mb-1 tw-text-left">
              {{ truncate(product.name, 40) }}
            </div>
            <div class="tw-text-sm sm:tw-text-sm tw-font-medium tw-text-gray-900 tw-line-clamp-2 tw-leading-tight">
              <span class="tw-text-blue-600 tw-font-bold">{{ formatPrice(product.flash_price) }}</span>
              <span class="tw-line-through tw-text-gray-400">{{ formatPrice(product.price) }}</span>
              <span class="tw-bg-red-500 tw-text-white tw-px-2 tw-py-1 tw-rounded tw-text-xs">
                -{{ getDiscountPercent(product.price, product.flash_price) }}%
              </span>
            </div>
            <div class="tw-flex tw-items-center tw-gap-1 tw-mb-1">
              <span
                v-for="(color, idx) in getUniqueColors(product)"
                :key="color"
                class="tw-inline-block tw-w-4 tw-h-4 tw-rounded-full tw-border tw-border-gray-300"
                :style="{ background: color || '#eee' }"
                :title="color"
              ></span>
              <span v-if="(product.variants && getUniqueColors(product).length > 3)" class="tw-text-xs tw-text-gray-400">+{{ getUniqueColors(product).length - 3 }}</span>
            </div>
            <div class="tw-w-full tw-mt-2 tw-px-2">
              <div class="tw-relative tw-h-6 tw-bg-gray-200 tw-rounded-full">
                <div
                  class="tw-absolute tw-left-0 tw-top-0 tw-h-6 tw-bg-blue-600 tw-rounded-full"
                  :style="`width: ${getSoldPercent(product)}%; transition: width 0.3s;`"
                ></div>
                <div class="tw-absolute tw-left-3 tw-top-0 tw-h-6 tw-flex tw-items-center tw-z-10 tw-text-white tw-font-semibold tw-text-sm">
                  <span style="font-size: 1.1rem; margin-right: 2px;">🔥</span>
                  Đã bán {{ product.sold ?? 0 }} sản phẩm
                </div>
              </div>
            </div>
          </div>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useFlashsale } from '@/composables/useFlashsale'
import productSaleBg from '~/assets/product_sale.jpg'

const flashSales = ref([])
const selectedIndex = ref(0)
const flashSaleProducts = ref([])
const currentSale = ref(null)
const { getFlashSales, getMainImage } = useFlashsale()

function formatPrice(price) {
  if (!price) return ''
  return Number(price).toLocaleString('vi-VN') + '₫'
}
function getDiscountPercent(price, flashPrice) {
  if (!price || !flashPrice) return 0
  return Math.round(100 - (flashPrice / price) * 100)
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
function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleString('vi-VN', { hour12: false })
}
function selectTab(idx) {
  if (selectedIndex.value === idx) return
  selectedIndex.value = idx
  updateTabData()
}
function updateTabData() {
  const fs = flashSales.value[selectedIndex.value]
  currentSale.value = fs
  if (fs && fs.products) {
    flashSaleProducts.value = fs.products.map(p => ({
      ...p.product,
      ...p,
      flash_price: p.flash_price,
      sold: p.sold ?? 0,
      end_time: fs.end_time,
      flash_sale_quantity: p.quantity
    }))
  }
}
function truncate(text, maxLength) {
  if (!text) return ''
  return text.length > maxLength ? text.slice(0, maxLength) + '...' : text
}
onMounted(async () => {
  const data = await getFlashSales()
  flashSales.value = Array.isArray(data) ? data : []
  // Chọn tab đầu tiên là flash sale đang active, nếu không có thì lấy đầu tiên
  let idx = 0
  if (flashSales.value.length > 0) {
    const activeIdx = flashSales.value.findIndex(fs => {
      const now = new Date()
      const start = new Date(fs.start_time)
      const end = new Date(fs.end_time)
      return fs.active && start <= now && end >= now
    })
    idx = activeIdx !== -1 ? activeIdx : 0
  }
  selectedIndex.value = idx
  updateTabData()
})
</script> 