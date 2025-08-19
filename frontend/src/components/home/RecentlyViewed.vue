<template>
    <div v-if="recentlyViewed.length > 0" class="mt-3 bg-white p-4 md:p-8 rounded-[5px]">
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

        <div class="flex gap-4 overflow-x-auto scroll-smooth md:grid md:grid-cols-1 md:sm:grid-cols-2 md:lg:grid-cols-4 md:xl:grid-cols-5 md:gap-4">
            <div v-for="product in recentlyViewed" :key="product.id" class="flex-shrink-0 w-64 md:w-auto relative group">
                <!-- View Time Badge -->
                <div class="absolute top-2 left-2 z-10">
                    <span class="bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded-full">
                        {{ formatViewTime(product.viewedAt) }}
                    </span>
                </div>
                
                <!-- Remove Button -->
                <div class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                    <button @click="removeFromRecentlyViewed(product.id)"
                            class="w-7 h-7 bg-white rounded-full shadow-md flex items-center justify-center text-gray-600 hover:text-red-500 hover:bg-red-50 transition-colors duration-200"
                            title="Xóa khỏi danh sách đã xem">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                
                <Card :product="product" @quick-view="openQuickView" />
            </div>
        </div>

        <!-- Quick View Modal -->
        <QuickView :show="showQuickView" :product="quickViewProduct" @close="closeQuickView" />
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRecentlyViewed } from '../../composable/useRecentlyViewed';
import Card from '../ui/Card.vue';
import QuickView from '../products/Quick-view.vue';

const {
    recentlyViewed,
    hasRecentlyViewed,
    addToRecentlyViewed,
    removeFromRecentlyViewed,
    clearAllRecentlyViewed,
    clearLocalStorage,
    enforceLimit
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

const handleClearAllRecentlyViewed = () => {
    if (confirm('Bạn có chắc chắn muốn xóa tất cả sản phẩm đã xem?')) {
        try {
            clearAllRecentlyViewed();
            console.log('All recently viewed products cleared');
        } catch (error) {
            console.error('Lỗi khi xóa tất cả sản phẩm đã xem:', error);
            alert('Không thể xóa tất cả sản phẩm. Vui lòng thử lại.');
        }
    }
};

const handleForceLimit = () => {
    try {
        const wasEnforced = enforceLimit();
        if (wasEnforced) {
            console.log('Đã force cắt bớt sản phẩm xuống 5');
            alert('Đã cắt bớt sản phẩm xuống 5!');
        } else {
            console.log('Không cần cắt bớt, đã đúng giới hạn');
            alert('Không cần cắt bớt, đã đúng giới hạn 5 sản phẩm!');
        }
    } catch (error) {
        console.error('Lỗi khi force limit:', error);
        alert('Lỗi khi force limit!');
    }
};


onMounted(() => {

});

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
