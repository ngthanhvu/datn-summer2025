<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-mb-6">
        <div v-if="tab === 'products'" class="tw-p-4">
            <table class="tw-w-full tw-text-sm tw-border-collapse">
                <thead>
                    <tr class="tw-bg-gray-50">
                        <th class="tw-px-4 tw-py-2 tw-text-left"> </th>
                        <th class="tw-px-4 tw-py-2 tw-text-left">Tên sản phẩm</th>
                        <th class="tw-px-4 tw-py-2 tw-text-center">Đánh giá</th>
                        <th class="tw-px-4 tw-py-2 tw-text-center">Số lượng</th>
                        <th class="tw-px-4 tw-py-2 tw-text-center">Ngày gần nhất</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in paginatedProducts" :key="product.id" class="tw-border-b hover:tw-bg-gray-50">
                        <td class="tw-px-4 tw-py-2">
                            <img :src="getImageUrl(product.image)" :alt="product.name" class="tw-w-12 tw-h-12 tw-object-cover tw-rounded" />
                        </td>
                        <td class="tw-px-4 tw-py-2">
                            <a href="#" class="tw-text-blue-600 hover:tw-underline">{{ product.name }}</a>
                        </td>
                        <td class="tw-px-4 tw-py-2 tw-text-center">
                            <span v-html="renderStars(product.average_rating)"></span>
                        </td>
                        <td class="tw-px-4 tw-py-2 tw-text-center">{{ product.review_count }}</td>
                        <td class="tw-px-4 tw-py-2 tw-text-center">{{ product.latest_review_date ? formatDate(product.latest_review_date) : '' }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="tw-flex tw-justify-between tw-items-center tw-mt-4">
                <span class="tw-text-xs tw-text-gray-600">
                    Hiển thị kết quả từ {{ (currentPage - 1) * pageSize + 1 }} - {{ Math.min(currentPage * pageSize, products.length) }} trên tổng {{ products.length }}
                </span>
                <div class="tw-flex tw-items-center tw-gap-1">
                    <button :disabled="currentPage === 1" @click="currentPage--" class="tw-px-2 tw-py-1 tw-rounded tw-border tw-bg-white tw-text-gray-700" :class="{ 'tw-opacity-50': currentPage === 1 }">«</button>
                    <button v-for="page in totalPages" :key="page" @click="currentPage = page" :class="['tw-px-2 tw-py-1 tw-rounded tw-border', currentPage === page ? 'tw-bg-primary tw-text-white' : 'tw-bg-white tw-text-gray-700']">{{ page }}</button>
                    <button :disabled="currentPage === totalPages" @click="currentPage++" class="tw-px-2 tw-py-1 tw-rounded tw-border tw-bg-white tw-text-gray-700" :class="{ 'tw-opacity-50': currentPage === totalPages }">»</button>
                </div>
            </div>
        </div>
        <div v-else class="tw-p-4 tw-text-gray-500 tw-text-center">
            (Chức năng danh sách đánh giá sẽ hiển thị ở đây)
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRuntimeConfig } from 'nuxt/app'

const runtimeConfig = useRuntimeConfig()
const emit = defineEmits(['select-product'])

const tab = ref('products')
const products = ref([])
const pageSize = 6
const currentPage = ref(1)
const totalReviews = ref(0)

const getImageUrl = (url) => {
    if (!url) return 'https://via.placeholder.com/150'
    
    if (url.startsWith('http')) {
        const baseUrl = runtimeConfig.public.apiBaseUrl
        
        if (url.includes(`${baseUrl}/storage/${baseUrl}/storage/`)) {
            return url.replace(new RegExp(`(${baseUrl}/storage/)+`, 'g'), `${baseUrl}/storage/`)
        }
        
        if (url.includes(`${baseUrl}/storage/`) && !url.startsWith(`${baseUrl}/storage/`)) {
            return url.replace(`${baseUrl}/storage/`, '')
        }
        
        return url
    }
    
    const baseUrl = runtimeConfig.public.apiBaseUrl
    return `${baseUrl}/storage/${url.replace(/^\/storage\//, '')}`
}

const fetchProducts = async () => {
    try {
        const response = await fetch(`${runtimeConfig.public.apiBaseUrl}/api/products-reviewed`)
        const data = await response.json()
        products.value = data
        totalReviews.value = data.reduce((sum, p) => sum + (p.review_count || 0), 0)
    } catch (err) {
        console.error('Lỗi khi tải danh sách sản phẩm:', err)
    }
}

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * pageSize
    return products.value.slice(start, start + pageSize)
})

const totalPages = computed(() => Math.ceil(products.value.length / pageSize))

const renderStars = (rating) => {
    let html = ''
    for (let i = 1; i <= 5; i++) {
        if (rating >= i) html += '<i class="fas fa-star tw-text-yellow-400"></i>'
        else if (rating >= i - 0.5) html += '<i class="fas fa-star-half-alt tw-text-yellow-400"></i>'
        else html += '<i class="fas fa-star tw-text-gray-300"></i>'
    }
    return html
}

const formatDate = (dateStr) => {
    const d = new Date(dateStr)
    return d.toLocaleDateString('vi-VN') + ' ' + d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
    fetchProducts()
})
</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}
.tw-text-primary {
    color: #3bb77e;
}
.tw-border-primary {
    border-color: #3bb77e;
}
</style> 