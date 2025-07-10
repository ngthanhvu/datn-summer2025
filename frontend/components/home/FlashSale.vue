<template>
    <div class="tw-bg-white tw-rounded tw-p-6 tw-mb-6">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <div class="tw-flex tw-items-center tw-gap-3">
                <h1 class="tw-text-2xl tw-font-bold tw-text-blue-700">{{ campaignName }}</h1>
                <img src="https://theme.hstatic.net/200000696635/1001373943/14/flashsale-hot.png?v=6" alt="Flash Sale" class="tw-h-10 tw-w-auto" />
            </div>
            <div class="tw-flex tw-items-center tw-gap-2">
                <span>Kết thúc sau</span>
                <div class="tw-bg-black tw-text-white tw-px-2 tw-py-1 tw-rounded">{{ countdown.hours }}</div>
                <span>Giờ</span>
                <div class="tw-bg-black tw-text-white tw-px-2 tw-py-1 tw-rounded">{{ countdown.minutes }}</div>
                <span>Phút</span>
                <div class="tw-bg-black tw-text-white tw-px-2 tw-py-1 tw-rounded">{{ countdown.seconds }}</div>
                <span>Giây</span>
            </div>
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
        <div class="tw-relative">
            <button @click="scrollLeft" class="tw-absolute tw-left-0 tw-top-1/2 -tw-translate-y-1/2 tw-z-20 tw-bg-white tw-shadow tw-rounded-full tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center hover:tw-bg-gray-100 tw-transition" style="outline:none;">
                <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-6 tw-w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <div ref="sliderRef" class="tw-flex tw-gap-4 tw-overflow-x-auto tw-scroll-smooth tw-px-12" style="scrollbar-width:none;">
                <NuxtLink
                    v-for="product in flashSaleProducts"
                    :key="product.id"
                    :to="{ path: `/chi-tiet/${product.slug}`, query: { flashsale: campaignName, flash_price: product.flash_price, end_time: product.product?.end_time || product.end_time, sold: product.sold, quantity: product.flash_sale_quantity } }"
                    class="tw-relative tw-w-64 tw-flex-shrink-0"
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
                        <!-- Hover buttons -->
                        <div
                          class="tw-absolute tw-bottom-4 tw-left-1/2 -tw-translate-x-1/2 tw-flex tw-gap-2 tw-opacity-0 tw-translate-y-4 group-hover:tw-opacity-100 group-hover:tw-translate-y-0 tw-transition-all tw-duration-300"
                        >
                          <button
                            class="tw-bg-white tw-rounded tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-shadow hover:tw-bg-gray-100 tw-transition tw-duration-200"
                            title="Thêm vào giỏ"
                            @click.prevent.stop="addToCart(product)"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" fill="none"
                              viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.35 2.7a1 1 0 00.9 1.5H19M7 13L5.4 5M16 16a1 1 0 100 2 1 1 0 000-2zm-8 0a1 1 0 100 2 1 1 0 000-2z" />
                            </svg>
                          </button>
                          <button
                            class="tw-bg-white tw-rounded tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-shadow hover:tw-bg-gray-100 tw-transition tw-duration-200"
                            title="Xem nhanh"
                            @click.prevent.stop="onQuickView(product)"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" fill="none"
                              viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="tw-bg-white tw-px-2 tw-py-3 tw-shadow -tw-mt-4 tw-z-10 tw-relative">
                        <div class="tw-font-bold tw-text-gray-500 tw-text-xs tw-uppercase tw-mb-1 tw-text-left">
                            {{ product.category?.name || 'KHÁC' }}
                        </div>
                        <div class="tw-font-semibold tw-text-base tw-mb-1 tw-text-left">
                            {{ truncate(product.name, 40) }}
                        </div>
                        <div class="tw-flex tw-items-center tw-gap-2 tw-mb-1">
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
            <button @click="scrollRight" class="tw-absolute tw-right-0 tw-top-1/2 -tw-translate-y-1/2 tw-z-20 tw-bg-white tw-shadow tw-rounded-full tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center hover:tw-bg-gray-100 tw-transition" style="outline:none;">
                <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-6 tw-w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>
        <div class="tw-flex tw-justify-center tw-mt-4">
            <NuxtLink to="/flash-sale" class="tw-border tw-px-6 tw-py-2 tw-rounded tw-bg-white tw-font-bold">Xem tất cả &gt;</NuxtLink>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useFlashsale } from '@/composables/useFlashsale'
import productSaleBg from '~/assets/product_sale.jpg'

const flashSaleProducts = ref([])
const countdown = ref({ hours: '00', minutes: '00', seconds: '00' })
const campaignName = ref('')
const flashSales = ref([])
const selectedIndex = ref(0)
let countdownInterval = null
const { getFlashSales, getMainImage } = useFlashsale()
const sliderRef = ref(null)

function formatPrice(price) {
    if (!price) return ''
    return Number(price).toLocaleString('vi-VN') + '₫'
}

function getDiscountPercent(price, flashPrice) {
    if (!price || !flashPrice) return 0
    return Math.round(100 - (flashPrice / price) * 100)
}

function getFirstActiveFlashSale(flashSales) {
    const now = new Date()
    return flashSales.find(fs => {
        const start = new Date(fs.start_time)
        const end = new Date(fs.end_time)
        return fs.active && start <= now && end >= now
    }) || flashSales[0]
}

function updateCountdown(endTime) {
    const now = new Date()
    const end = new Date(endTime)
    let diff = Math.max(0, end - now)
    const hours = String(Math.floor(diff / (1000 * 60 * 60))).padStart(2, '0')
    diff %= 1000 * 60 * 60
    const minutes = String(Math.floor(diff / (1000 * 60))).padStart(2, '0')
    diff %= 1000 * 60
    const seconds = String(Math.floor(diff / 1000)).padStart(2, '0')
    countdown.value = { hours, minutes, seconds }
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
  // TODO: Thêm logic thêm vào giỏ hàng
  console.log('Add to cart:', product)
}
function onQuickView(product) {
  // TODO: Hiển thị modal quick view
  console.log('Quick view:', product)
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
    }
}

function truncate(text, maxLength) {
  if (!text) return ''
  return text.length > maxLength ? text.slice(0, maxLength) + '...' : text
}

function scrollLeft() {
  const el = sliderRef.value
  if (el) el.scrollBy({ left: -300, behavior: 'smooth' })
}
function scrollRight() {
  const el = sliderRef.value
  if (el) el.scrollBy({ left: 300, behavior: 'smooth' })
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

watch(selectedIndex, updateTabData)
</script>

<style scoped>
.tw-rounded-32px {
    border-radius: 32px;
}
</style>