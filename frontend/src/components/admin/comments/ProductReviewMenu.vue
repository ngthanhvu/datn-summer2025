<template>
    <div class="bg-white rounded-lg shadow mb-4 sm:mb-6">

        
        <div v-if="tab === 'products'" class="p-3 sm:p-4">
            <!-- Loading State -->
            <div v-if="products.length === 0" class="text-center py-8">
                <i class="fas fa-spinner animate-spin text-2xl text-gray-400 mb-2"></i>
                <div class="text-gray-500">Đang tải danh sách sản phẩm...</div>
            </div>
            
            <!-- Content when products exist -->
            <div v-else>
                <!-- Mobile Card Layout (hidden on desktop) -->
                <div class="block sm:hidden space-y-4">
                    <div v-for="product in paginatedProducts" :key="product.id" 
                         class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    
                    <!-- Product Header -->
                    <div class="flex items-start gap-3 mb-3">
                        <img :src="getImageUrl(product.image)" :alt="product.name"
                             class="w-16 h-16 object-cover rounded-lg flex-shrink-0" />
                        <div class="flex-1 min-w-0">
                            <h3 class="font-medium text-gray-900 text-sm leading-tight mb-1 line-clamp-2">
                                <a href="#" class="text-blue-600 hover:underline">{{ product.name }}</a>
                            </h3>
                            <div class="flex items-center gap-2 mb-2">
                                <span v-html="renderStars(product.average_rating)" class="text-sm"></span>
                                <span class="text-xs text-gray-500">({{ parseFloat(product.average_rating || 0).toFixed(1) }})</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Stats -->
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-lg font-semibold text-gray-900">{{ product.review_count }}</div>
                            <div class="text-xs text-gray-500">Đánh giá</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs font-medium text-gray-900">
                                {{ product.latest_review_date ? formatDate(product.latest_review_date) : 'Chưa có' }}
                            </div>
                            <div class="text-xs text-gray-500">Gần nhất</div>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Desktop Table Layout (hidden on mobile) -->
                <div class="hidden sm:block overflow-x-auto overflow-hidden border border-gray-200 bg-white rounded-lg">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-300 bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"> </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên sản phẩm</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Đánh giá</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày gần nhất</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="product in paginatedProducts" :key="product.id"
                            class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4">
                                <img :src="getImageUrl(product.image)" :alt="product.name"
                                    class="w-12 h-12 object-cover rounded-lg" />
                            </td>
                            <td class="px-4 py-4">
                                <a href="#" class="text-blue-600 hover:underline font-medium">{{ product.name }}</a>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <span v-html="renderStars(product.average_rating)"></span>
                                    <span class="text-sm text-gray-500 ml-1">({{ parseFloat(product.average_rating || 0).toFixed(1) }})</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ product.review_count }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center text-sm text-gray-900">
                                {{ product.latest_review_date ? formatDate(product.latest_review_date) : 'Chưa có' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
                
                <!-- Pagination -->
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mt-4 pt-4 border-t border-gray-200">
                <span class="text-xs sm:text-sm text-gray-600 text-center sm:text-left">
                    Hiển thị {{ (currentPage - 1) * pageSize + 1 }} - {{ Math.min(currentPage * pageSize, products.length) }} 
                    trên tổng {{ products.length }} sản phẩm
                </span>
                <div class="flex items-center justify-center gap-1">
                    <button :disabled="currentPage === 1" @click="currentPage--"
                        class="px-2 sm:px-3 py-1 rounded border border-gray-300 bg-white text-gray-700 text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                        <span class="hidden sm:inline ml-1">Trước</span>
                    </button>
                    
                    <!-- Page numbers - limit visible pages on mobile -->
                    <div class="flex gap-1">
                        <button v-for="page in getVisiblePages()" :key="page" @click="currentPage = page"
                            :class="[
                                'px-2 sm:px-3 py-1 rounded border text-xs sm:text-sm',
                                currentPage === page 
                                    ? 'bg-primary text-white border-primary' 
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                            ]">
                            {{ page }}
                        </button>
                    </div>
                    
                    <button :disabled="currentPage === totalPages" @click="currentPage++"
                        class="px-2 sm:px-3 py-1 rounded border border-gray-300 bg-white text-gray-700 text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                        <span class="hidden sm:inline mr-1">Sau</span>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                </div>
            </div>
        </div>
        <div v-else class="p-4 text-gray-500 text-center">
            (Chức năng danh sách đánh giá sẽ hiển thị ở đây)
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL; // ✅ Thay useRuntimeConfig
const emit = defineEmits(["select-product"]);

const tab = ref("products");
const products = ref([]);
const pageSize = 6;
const currentPage = ref(1);
const totalReviews = ref(0);

const getImageUrl = (url) => {
    if (!url) return "https://via.placeholder.com/150";

    if (url.startsWith("http")) {
        if (url.includes(`${API_BASE_URL}/storage/${API_BASE_URL}/storage/`)) {
            return url.replace(
                new RegExp(`(${API_BASE_URL}/storage/)+`, "g"),
                `${API_BASE_URL}/storage/`
            );
        }

        if (
            url.includes(`${API_BASE_URL}/storage/`) &&
            !url.startsWith(`${API_BASE_URL}/storage/`)
        ) {
            return url.replace(`${API_BASE_URL}/storage/`, "");
        }

        return url;
    }

    return `${API_BASE_URL}/storage/${url.replace(/^\/storage\//, "")}`;
};

const fetchProducts = async () => {
    try {
        const response = await fetch(`${API_BASE_URL}/api/products-reviewed`);
        const data = await response.json();
        products.value = data;
        totalReviews.value = data.reduce(
            (sum, p) => sum + (p.review_count || 0),
            0
        );
    } catch (err) {
        console.error("Lỗi khi tải danh sách sản phẩm:", err);
    }
};

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return products.value.slice(start, start + pageSize);
});

const totalPages = computed(() =>
    Math.ceil(products.value.length / pageSize)
);

const getVisiblePages = () => {
    const pages = [];
    const maxVisible = 5; // Show max 5 pages on mobile
    let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
    let end = Math.min(totalPages.value, start + maxVisible - 1);
    
    // Adjust start if we're near the end
    if (end - start + 1 < maxVisible) {
        start = Math.max(1, end - maxVisible + 1);
    }
    
    for (let i = start; i <= end; i++) {
        pages.push(i);
    }
    
    return pages;
};

const renderStars = (rating) => {
    let html = "";
    for (let i = 1; i <= 5; i++) {
        if (rating >= i)
            html += '<i class="fas fa-star text-yellow-400"></i>';
        else if (rating >= i - 0.5)
            html += '<i class="fas fa-star-half-alt text-yellow-400"></i>';
        else html += '<i class="fas fa-star text-gray-300"></i>';
    }
    return html;
};

const formatDate = (dateStr) => {
    const d = new Date(dateStr);
    return (
        d.toLocaleDateString("vi-VN") +
        " " +
        d.toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit" })
    );
};

onMounted(() => {
    fetchProducts();
});
</script>

<style scoped>
.bg-primary {
    background-color: #3bb77e;
}

.text-primary {
    color: #3bb77e;
}

.border-primary {
    border-color: #3bb77e;
}

/* Line clamp for product names on mobile */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Smooth transitions */
.transition-shadow {
    transition: box-shadow 0.2s ease-in-out;
}

.transition-colors {
    transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
}

/* Mobile card hover effects */
@media (max-width: 640px) {
    .hover\:shadow-md:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
}
</style>