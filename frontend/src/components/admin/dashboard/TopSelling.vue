<template>
    <div class="top-selling bg-white rounded-2xl p-6 text-gray-900 border border-gray-200">
        <h3 class="mb-4 font-semibold text-lg">Top Sản Phẩm Bán Chạy</h3>
        <div v-if="loading" class="py-8 text-center text-gray-400">Đang tải...</div>
        <div v-else-if="error" class="py-8 text-center text-red-500">{{ error }}</div>
        <ul v-else class="space-y-4">
            <li v-for="(product, idx) in products" :key="product.id" class="flex items-center gap-4">
                <img :src="product.image || '/no-image.png'" alt="Ảnh sản phẩm"
                    class="w-14 h-14 object-cover rounded-xl bg-gray-100" />
                <div class="flex-1 min-w-0">
                    <div class="font-semibold truncate">{{ product.name }}</div>
                    <div class="flex items-center gap-2 mt-1">
                        <span v-if="product.discount_price" class="line-through text-gray-400 text-sm">{{
                            formatPrice(product.price) }}</span>
                        <span
                            :class="product.discount_price ? 'text-red-500 font-bold' : 'text-gray-900 font-semibold'">
                            {{ formatPrice(product.discount_price || product.price) }}
                        </span>
                    </div>
                </div>
                <div class="text-right min-w-[80px]">
                    <div class="text-xs text-gray-500">đã bán</div>
                    <span class="text-green-600 font-bold text-sm">{{ product.sold_count }}</span>
                </div>
                <div class="flex items-center gap-1 min-w-[60px]">
                    <template v-if="Array.isArray(product.colors) && product.colors.length">
                        <template v-for="(color, cidx) in product.colors.slice(0, 3)" :key="cidx">
                            <span class="w-4 h-4 rounded-full border-2 border-gray-200"
                                :style="{ background: color }"></span>
                        </template>
                        <span v-if="product.colors.length > 3" class="ml-1 text-xs text-gray-500 font-bold">+{{
                            product.colors.length - 3
                        }}</span>
                    </template>
                    <template v-else>
                        <span v-if="product.color" class="w-4 h-4 rounded-full border-2 border-gray-200"
                            :style="{ background: product.color }"></span>
                        <span v-else class="text-gray-400">-</span>
                    </template>
                </div>

            </li>
        </ul>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useDashboard } from '../../../composable/useDashboard'

const { getTopSelling, formatCurrency } = useDashboard()

const products = ref([])
const loading = ref(true)
const error = ref('')
const placeholder = '/no-image.png'

function formatPrice(price) {
    if (price == null) return '-'
    return formatCurrency(price)
}

async function fetchTopSelling() {
    loading.value = true
    error.value = ''
    try {
        const data = await getTopSelling({ limit: 5 })
        if (data.success) {
            products.value = data.data.map(p => ({
                ...p,
                colors: Array.isArray(p.colors) ? p.colors : (p.color ? [p.color] : [])
            }))
        } else {
            error.value = data.message || 'Không lấy được dữ liệu.'
            products.value = []
        }
    } catch (e) {
        error.value = 'Lỗi kết nối đến server.'
        products.value = []
    } finally {
        loading.value = false
    }
}

onMounted(fetchTopSelling)
</script>

<style scoped>
.top-selling {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
    padding: 1.5rem;
}
</style>