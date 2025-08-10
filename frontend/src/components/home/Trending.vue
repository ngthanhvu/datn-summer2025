<template>
    <div v-if="shouldShowRecommend" class="mt-3 bg-white p-4 md:p-8 rounded-[5px]">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-lg md:text-2xl font-semibold text-gray-800">Sản phẩm đề xuất</h2>
            <router-link to="/products/trending"
                class="text-blue-600 hover:text-blue-800 font-medium transition-colors text-sm md:text-base">
                Xem tất cả →
            </router-link>
        </div>

        <!-- Loading State -->
        <div v-if="loading"
            class="flex gap-4 overflow-x-auto scroll-smooth md:grid md:grid-cols-1 md:sm:grid-cols-2 md:lg:grid-cols-4 md:xl:grid-cols-5 md:gap-4">
            <div v-for="i in 5" :key="i"
                class="bg-white rounded-lg shadow-sm overflow-hidden animate-pulse flex-shrink-0 w-64 md:w-auto">
                <div class="h-64 md:h-80 bg-gray-200"></div>
                <div class="p-4">
                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                    <div class="h-6 bg-gray-200 rounded mb-2"></div>
                    <div class="h-8 bg-gray-200 rounded"></div>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-8">
            <div class="text-red-500 mb-4">{{ error }}</div>
            <button @click="fetchProducts" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Thử lại
            </button>
        </div>

        <!-- Products Grid -->
        <div v-else
            class="flex gap-4 overflow-x-auto scroll-smooth md:grid md:grid-cols-1 md:sm:grid-cols-2 md:lg:grid-cols-4 md:xl:grid-cols-5 md:gap-4">
            <div v-for="product in (recommendedProducts.length > 0 ? recommendedProducts.slice(0, 5) : newProducts.slice(0, 5))"
                :key="product.id" class="flex-shrink-0 w-64 md:w-auto">
                <Card :product="product" @quick-view="openQuickView" />
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && !error && recommendedProducts.length === 0 && newProducts.length === 0"
            class="text-center py-8">
            <p class="text-gray-500">Chưa có sản phẩm đề xuất</p>
        </div>

        <!-- Quick View Modal -->
        <QuickView :show="showQuickView" :product="quickViewProduct" @close="closeQuickView" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Cookies from 'js-cookie'
import { useProducts } from '../../composable/useProducts'
import Card from '../ui/Card.vue'
import QuickView from '../products/Quick-view.vue' // cập nhật path cho Vue 3

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL

const { getNewProducts, getRecommendedProducts } = useProducts()

const newProducts = ref([])
const recommendedProducts = ref([])
const loading = ref(true)
const error = ref(null)

const showQuickView = ref(false)
const quickViewProduct = ref(null)

const userCookie = Cookies.get('user')
const user = userCookie ? JSON.parse(userCookie) : null

const shouldShowRecommend = computed(() => {
    return Boolean(user?.username && user?.gender && user?.dateOfBirth)
})

function openQuickView(product) {
    quickViewProduct.value = product
    showQuickView.value = true
}
function closeQuickView() {
    showQuickView.value = false
    quickViewProduct.value = null
}

function getFullImagePath(imagePath) {
    if (!imagePath) return ''
    if (imagePath.startsWith('http')) return imagePath
    let path = imagePath
    if (!path.startsWith('/storage/')) {
        if (path.startsWith('storage/')) {
            path = '/' + path
        } else {
            path = '/storage/' + path.replace(/^\/+/, '')
        }
    }
    return apiBaseUrl + path
}

const fetchProducts = async () => {
    try {
        loading.value = true
        error.value = null
        if (shouldShowRecommend.value) {
            const rec = await getRecommendedProducts()
            if (rec && rec.length > 0) {
                rec.forEach(product => {
                    if (Array.isArray(product.images)) {
                        product.images = product.images.map(img => ({
                            ...img,
                            image_path: getFullImagePath(img.image_path)
                        }))
                    }
                })
                recommendedProducts.value = rec
                loading.value = false
                return
            }
        }

        const products = await getNewProducts(10)
        products.forEach(product => {
            if (Array.isArray(product.images)) {
                product.images = product.images.map(img => ({
                    ...img,
                    image_path: getFullImagePath(img.image_path)
                }))
            }
        })
        newProducts.value = products
    } catch (err) {
        console.error('Error fetching products:', err)
        error.value = 'Không thể tải sản phẩm mới. Vui lòng thử lại.'
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    fetchProducts()
})
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
