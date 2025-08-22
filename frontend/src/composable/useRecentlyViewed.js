import { ref, computed } from 'vue';

// State
const recentlyViewed = ref([]);

// Constants
const MAX_RECENTLY_VIEWED = 5; // Giới hạn tối đa 5 sản phẩm đã xem
const STORAGE_KEY = 'recentlyViewedProducts';

// Computed
const hasRecentlyViewed = computed(() => recentlyViewed.value.length > 0);

// Methods
const loadRecentlyViewed = () => {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            const parsed = JSON.parse(stored);
            // Sắp xếp theo thời gian xem gần nhất và giới hạn số lượng
            const sorted = parsed.sort((a, b) => new Date(b.viewedAt) - new Date(a.viewedAt));
            // Đảm bảo chỉ lấy tối đa MAX_RECENTLY_VIEWED sản phẩm
            recentlyViewed.value = sorted.slice(0, MAX_RECENTLY_VIEWED);
            
            // Nếu dữ liệu cũ vượt quá giới hạn, cập nhật localStorage
            if (parsed.length > MAX_RECENTLY_VIEWED) {
                saveRecentlyViewed();
                console.log(`Đã cắt bớt từ ${parsed.length} xuống ${MAX_RECENTLY_VIEWED} sản phẩm`);
            }
        }
    } catch (error) {
        console.error('Lỗi khi tải sản phẩm đã xem:', error);
        recentlyViewed.value = [];
    }
};

const saveRecentlyViewed = () => {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(recentlyViewed.value));
    } catch (error) {
        console.error('Lỗi khi lưu sản phẩm đã xem:', error);
    }
};

const addToRecentlyViewed = (product) => {
    try {
        // Kiểm tra xem sản phẩm đã có trong danh sách chưa
        const existingIndex = recentlyViewed.value.findIndex(p => p.id === product.id);
        
        if (existingIndex !== -1) {
            // Cập nhật thời gian xem
            recentlyViewed.value[existingIndex].viewedAt = new Date().toISOString();
        } else {
            // Thêm sản phẩm mới - giữ nguyên cấu trúc dữ liệu gốc
            const productData = {
                id: product.id,
                name: product.name,
                slug: product.slug,
                price: product.price,
                discount_price: product.discount_price,
                original_price: product.original_price || product.price,
                images: product.images || [], // Giữ nguyên cấu trúc images với image_path và is_main
                rating: product.rating || 0,
                review_count: product.review_count || 0,
                variants: product.variants || [], // Giữ nguyên variants
                categories_id: product.categories_id,
                brand_id: product.brand_id,
                viewedAt: new Date().toISOString()
            };
            
            // Đảm bảo images có cấu trúc đúng
            if (productData.images && productData.images.length > 0) {
                productData.images = productData.images.map(img => ({
                    image_path: img.image_path || img.path || img,
                    is_main: img.is_main || 0
                }));
            }
            
            recentlyViewed.value.unshift(productData);
            
            // Giới hạn số lượng sản phẩm
            if (recentlyViewed.value.length > MAX_RECENTLY_VIEWED) {
                recentlyViewed.value = recentlyViewed.value.slice(0, MAX_RECENTLY_VIEWED);
            }
        }
        
        // Sắp xếp lại theo thời gian xem gần nhất
        recentlyViewed.value.sort((a, b) => new Date(b.viewedAt) - new Date(a.viewedAt));
        
        saveRecentlyViewed();
    } catch (error) {
        console.error('Lỗi khi thêm sản phẩm vào danh sách đã xem:', error);
    }
};

const removeFromRecentlyViewed = (productId) => {
    try {
        const index = recentlyViewed.value.findIndex(p => p.id === productId);
        if (index !== -1) {
            recentlyViewed.value.splice(index, 1);
            saveRecentlyViewed();
        }
    } catch (error) {
        console.error('Lỗi khi xóa sản phẩm khỏi danh sách đã xem:', error);
    }
};

const clearAllRecentlyViewed = () => {
    try {
        recentlyViewed.value = [];
        localStorage.removeItem(STORAGE_KEY); // Xóa hoàn toàn khỏi localStorage
        console.log('LocalStorage cleared and all recently viewed products removed');
    } catch (error) {
        console.error('Lỗi khi xóa tất cả sản phẩm đã xem:', error);
    }
};

// Thêm function để debug và xóa localStorage
const clearLocalStorage = () => {
    try {
        localStorage.removeItem(STORAGE_KEY);
        recentlyViewed.value = [];
        console.log('LocalStorage cleared for debugging');
    } catch (error) {
        console.error('Lỗi khi xóa localStorage:', error);
    }
};

// Thêm function để force enforce giới hạn
const enforceLimit = () => {
    if (recentlyViewed.value.length > MAX_RECENTLY_VIEWED) {
        recentlyViewed.value = recentlyViewed.value.slice(0, MAX_RECENTLY_VIEWED);
        saveRecentlyViewed();
        console.log(`Đã force cắt bớt xuống ${MAX_RECENTLY_VIEWED} sản phẩm`);
        return true;
    }
    return false;
};

const getRecentlyViewed = () => {
    return recentlyViewed.value;
};

const isRecentlyViewed = (productId) => {
    return recentlyViewed.value.some(p => p.id === productId);
};

// Thêm function để lấy thông tin về số lượng sản phẩm
const getRecentlyViewedInfo = () => {
    return {
        current: recentlyViewed.value.length,
        max: MAX_RECENTLY_VIEWED,
        remaining: MAX_RECENTLY_VIEWED - recentlyViewed.value.length
    };
};

// Auto-load on import
loadRecentlyViewed();
// Force enforce limit sau khi load
enforceLimit();

export function useRecentlyViewed() {
    return {
        recentlyViewed,
        hasRecentlyViewed,
        addToRecentlyViewed,
        removeFromRecentlyViewed,
        clearAllRecentlyViewed,
        clearLocalStorage, // Thêm function debug
        enforceLimit, // Thêm function force enforce limit
        getRecentlyViewed,
        isRecentlyViewed,
        getRecentlyViewedInfo, // Thêm function thông tin
        loadRecentlyViewed,
        saveRecentlyViewed
    };
}
