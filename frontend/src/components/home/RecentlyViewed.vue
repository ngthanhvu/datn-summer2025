<template>
    <!-- Variant for detail page: match RelatedProducts layout (swiper on mobile, grid on desktop) -->
    <div v-if="variant === 'detail' && recentlyViewed.length > 0"
        class="mt-5 mb-5 bg-white p-8 rounded-[10px] border border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Sản phẩm đã xem gần đây</h2>
            <button @click="handleClearAllRecentlyViewed"
                class="text-red-600 hover:text-red-800 font-medium text-sm cursor-pointer">Xóa tất cả</button>
        </div>

        <!-- Mobile Slider -->
        <div class="lg:hidden">
            <swiper :slides-per-view="1.2" :space-between="16"
                :breakpoints="{ '640': { slidesPerView: 2.2 }, '768': { slidesPerView: 3.2 } }" class="w-full">
                <swiper-slide v-for="product in recentlyViewed" :key="product.id">
                    <div v-if="product?.slug" class="relative group">
                        <div class="absolute top-2 left-2 z-10">
                            <span class="bg-black/70 text-white text-xs px-2 py-1 rounded-full">{{
                                formatViewTime(product.viewedAt) }}</span>
                        </div>
                        <Card :product="product" @quick-view="openQuickView" />
                    </div>
                </swiper-slide>
            </swiper>
        </div>

        <!-- Desktop Grid -->
        <div class="hidden lg:grid lg:grid-cols-5 gap-6">
            <template v-for="product in recentlyViewed" :key="product.id">
                <div class="relative group">
                    <div class="absolute top-2 left-2 z-10">
                        <span class="bg-black/70 text-white text-xs px-2 py-1 rounded-full">{{
                            formatViewTime(product.viewedAt) }}</span>
                    </div>
                    <Card v-if="product?.slug" :product="product" @quick-view="openQuickView" />
                </div>
            </template>
        </div>

        <QuickView :show="showQuickView" :product="quickViewProduct" @close="closeQuickView" />
    </div>

    <!-- Default/home variant -->
    <div v-else-if="recentlyViewed.length > 0" class="mt-3 bg-white p-4 md:p-8 rounded-[5px]">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-lg md:text-2xl font-semibold text-gray-800 flex items-center gap-2">
                Sản Phẩm Đã Xem Gần Đây
            </h2>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">({{ recentlyViewed.length }} sản phẩm)</span>
                <button @click="handleClearAllRecentlyViewed"
                    class="text-red-600 hover:text-red-800 font-medium transition-colors text-sm md:text-base">
                    <i class="fas fa-trash-alt mr-1"></i>Xóa tất cả
                </button>
            </div>
        </div>

        <div
            class="flex gap-4 overflow-x-auto scroll-smooth md:grid md:grid-cols-1 md:sm:grid-cols-2 md:lg:grid-cols-4 md:xl:grid-cols-5 md:gap-4">
            <div v-for="product in recentlyViewed" :key="product.id"
                class="flex-shrink-0 w-64 md:w-auto relative group">
                <div class="absolute top-2 left-2 z-10">
                    <span class="bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded-full">
                        {{ formatViewTime(product.viewedAt) }}
                    </span>
                </div>
                <div
                    class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                    <button @click="removeFromRecentlyViewed(product.id)"
                        class="w-7 h-7 bg-white rounded-full shadow-md flex items-center justify-center text-gray-600 hover:text-red-500 hover:bg-red-50 transition-colors duration-200"
                        title="Xóa khỏi danh sách đã xem">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                <Card :product="product" @quick-view="openQuickView" />
            </div>
        </div>

        <QuickView :show="showQuickView" :product="quickViewProduct" @close="closeQuickView" />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRecentlyViewed } from '../../composable/useRecentlyViewed';
import Card from '../ui/Card.vue';
import QuickView from '../products/Quick-view.vue';
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import Swal from 'sweetalert2';
import { push } from 'notivue';

const props = defineProps({
    variant: { type: String, default: 'home' }
})

const {
    recentlyViewed,
    hasRecentlyViewed,
    addToRecentlyViewed,
    removeFromRecentlyViewed,
    clearAllRecentlyViewed,
} = useRecentlyViewed();

const showQuickView = ref(false);
const quickViewProduct = ref(null);

// Methods
const formatViewTime = (viewedAt) => {
    const now = new Date();
    const viewed = new Date(viewedAt);
    const diffInMinutes = Math.floor((now - viewed) / (1000 * 60));

    if (diffInMinutes < 1) return 'Vừa xem';
    if (diffInMinutes < 60) return `${diffInMinutes}p trước`;

    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `${diffInHours}h trước`;

    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) return `${diffInDays} ngày trước`;

    return viewed.toLocaleDateString('vi-VN');
};

// Methods for Quick View
const openQuickView = (product) => {
    quickViewProduct.value = product;
    showQuickView.value = true;
};

const closeQuickView = () => {
    showQuickView.value = false;
    quickViewProduct.value = null;
};

const addProductToRecentlyViewed = (product) => {
    addToRecentlyViewed(product);
};


const handleClearAllRecentlyViewed = async () => {
    const result = await Swal.fire({
        title: 'Xác nhận xóa',
        text: 'Bạn có chắc chắn muốn xóa tất cả sản phẩm đã xem?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Có, xóa hết',
        cancelButtonText: 'Hủy'
    })

    if (result.isConfirmed) {
        try {
            clearAllRecentlyViewed()
            push.success('Xóa tất cả sản phẩm đã xem gần đây!')
        } catch (error) {
            console.error('Lỗi khi xóa tất cả sản phẩm đã xem:', error)
        }
    }
}

defineExpose({
    addProductToRecentlyViewed
});
</script>

<style scoped>
.overflow-x-auto {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.group:hover .opacity-0 {
    opacity: 1;
}

.opacity-0 {
    transition: opacity 0.2s ease-in-out;
}

@media (max-width: 768px) {
    .flex-shrink-0 {
        flex-shrink: 0;
    }

    .w-64 {
        width: 16rem;
    }
}
</style>
