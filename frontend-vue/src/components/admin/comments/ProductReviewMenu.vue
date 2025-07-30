<template>
    <div class="bg-white rounded-lg shadow mb-6">
        <div v-if="tab === 'products'" class="p-4">
            <table class="w-full overflow-x-auto overflow-hidden border border-gray-200 bg-white">
                <thead>
                    <tr class="border border-gray-300">
                        <th class="px-4 py-2 text-left"> </th>
                        <th class="px-4 py-2 text-left">Tên sản phẩm</th>
                        <th class="px-4 py-2 text-center">Đánh giá</th>
                        <th class="px-4 py-2 text-center">Số lượng</th>
                        <th class="px-4 py-2 text-center">Ngày gần nhất</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in paginatedProducts" :key="product.id"
                        class="border-b border-gray-300 hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <img :src="getImageUrl(product.image)" :alt="product.name"
                                class="w-12 h-12 object-cover rounded" />
                        </td>
                        <td class="px-4 py-2">
                            <a href="#" class="text-blue-600 hover:underline">{{ product.name }}</a>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span v-html="renderStars(product.average_rating)"></span>
                        </td>
                        <td class="px-4 py-2 text-center">{{ product.review_count }}</td>
                        <td class="px-4 py-2 text-center">{{ product.latest_review_date ?
                            formatDate(product.latest_review_date) : '' }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="flex justify-between items-center mt-4">
                <span class="text-xs text-gray-600">
                    Hiển thị kết quả từ {{ (currentPage - 1) * pageSize + 1 }} - {{ Math.min(currentPage * pageSize,
                        products.length) }} trên tổng {{ products.length }}
                </span>
                <div class="flex items-center gap-1">
                    <button :disabled="currentPage === 1" @click="currentPage--"
                        class="px-2 py-1 rounded border border-gray-300 bg-white text-gray-700"
                        :class="{ 'opacity-50': currentPage === 1 }">«</button>
                    <button v-for="page in totalPages" :key="page" @click="currentPage = page"
                        :class="['px-2 py-1 rounded border', currentPage === page ? 'bg-primary text-white' : 'bg-white text-gray-700']">{{
                            page }}</button>
                    <button :disabled="currentPage === totalPages" @click="currentPage++"
                        class="px-2 py-1 rounded border border-gray-300 bg-white text-gray-700"
                        :class="{ 'opacity-50': currentPage === totalPages }">»</button>
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
</style>