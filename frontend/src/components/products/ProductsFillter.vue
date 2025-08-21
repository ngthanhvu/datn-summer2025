<template>
    <aside class="bg-white rounded-lg shadow-sm p-4 md:p-6 w-full">
        <h2 class="text-xl font-semibold mb-4">Bộ lọc</h2>

        <!-- Khoảng giá -->
        <div class="mb-6">
            <h4 class="font-medium mb-2 text-sm">Khoảng giá</h4>
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <input type="number" v-model="filters.min_price" placeholder="Từ"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <span class="text-gray-500">-</span>
                    <input type="number" v-model="filters.max_price" placeholder="Đến"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                    <span>{{ formatPrice(filters.min_price || 0) }}</span>
                    <span>{{ formatPrice(filters.max_price || 1000000) }}</span>
                </div>
            </div>
        </div>

        <!-- Danh mục -->
        <div class="mb-6">
            <h4 class="font-medium mb-2 text-sm">Danh mục</h4>
            <div class="flex flex-wrap gap-2">
                <button v-for="category in filterOptions.categories" :key="category.id"
                    @click="toggleFilter('category', category.id)" :class="[
                        'px-3 py-1 text-xs font-medium border rounded cursor-pointer transition-colors',
                        filters.category.includes(category.id) ? 'bg-[#81aacc] text-white' : 'bg-white text-black hover:bg-gray-50'
                    ]">
                    {{ category.name }}
                </button>
            </div>
        </div>

        <!-- Thương hiệu -->
        <div class="mb-6">
            <h4 class="font-medium mb-2 text-sm">Thương hiệu</h4>
            <div class="flex flex-wrap gap-2">
                <button v-for="brand in filterOptions.brands" :key="brand.id" @click="toggleFilter('brand', brand.id)"
                    :class="[
                        'px-3 py-1 text-xs font-medium border rounded cursor-pointer transition-colors',
                        filters.brand.includes(brand.id) ? 'bg-[#81aacc] text-white' : 'bg-white text-black hover:bg-gray-50'
                    ]">
                    {{ brand.name }}
                </button>
            </div>
        </div>

        <!-- Kích thước -->
        <div class="mb-6">
            <h4 class="font-medium mb-2 text-sm">Kích thước</h4>
            <div v-if="groupedSizes.alpha.length" class="mb-3">
                <p class="text-xs text-gray-500 mb-1">Chữ</p>
                <div class="flex flex-wrap gap-2">
                    <button v-for="size in groupedSizes.alpha" :key="'alpha-' + size"
                        @click="toggleFilter('size', size)" :class="[
                            'w-8 h-8 text-sm font-medium border rounded flex items-center justify-center cursor-pointer transition-colors',
                            filters.size.includes(size) ? 'bg-[#81aacc] text-white' : 'bg-white text-black hover:bg-gray-50'
                        ]">
                        {{ size }}
                    </button>
                </div>
            </div>

            <div v-if="groupedSizes.numeric.length">
                <p class="text-xs text-gray-500 mb-1">Số</p>
                <div class="flex flex-wrap gap-2">
                    <button v-for="size in groupedSizes.numeric" :key="'numeric-' + size"
                        @click="toggleFilter('size', size)" :class="[
                            'w-8 h-8 text-sm font-medium border rounded flex items-center justify-center cursor-pointer transition-colors',
                            filters.size.includes(size) ? 'bg-[#81aacc] text-white' : 'bg-white text-black hover:bg-gray-50'
                        ]">
                        {{ size }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Màu sắc -->
        <div class="mb-6">
            <h4 class="font-medium mb-2 text-sm">Màu sắc</h4>
            <div class="flex flex-wrap gap-2">
                <button v-for="color in filterOptions.colors" :key="color" @click="toggleFilter('color', color)"
                    :style="{ backgroundColor: color }" :class="[
                        'w-8 h-8 rounded-full border border-gray-300 cursor-pointer transition-transform hover:scale-110',
                        filters.color.includes(color) ? 'ring-2 ring-blue-500' : ''
                    ]">
                </button>
            </div>
        </div>

        <!-- Nút hành động -->
        <div class="flex gap-2">
            <button @click="clearFilters"
                class="flex-1 py-2 border border-gray-400 rounded text-sm font-medium cursor-pointer hover:bg-gray-50 transition-colors">
                XÓA
            </button>
            <button @click="applyFilters"
                class="flex-1 py-2 bg-[#81aacc] text-white rounded text-sm font-medium cursor-pointer hover:bg-[#81aacc]/80 transition-colors">
                ÁP DỤNG
            </button>
        </div>
    </aside>
</template>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useProducts } from "../../composable/useProducts";

const emit = defineEmits(["filter"]);

const initialFilters = {
    min_price: null,
    max_price: null,
    category: [],
    brand: [],
    color: [],
    size: [],
};

const filters = ref({ ...initialFilters });

const filterOptions = ref({
    categories: [],
    brands: [],
    colors: [],
    sizes: [],
});

const groupedSizes = computed(() => {
    const allSizes = filterOptions.value.sizes || [];
    const normalized = allSizes.map((s) => String(s));
    const numeric = normalized.filter((s) => /^\d+$/i.test(s));
    const alpha = normalized.filter((s) => !/^\d+$/i.test(s));
    return { alpha, numeric };
});

const { getFilterOptions } = useProducts();

onMounted(async () => {
    try {
        const options = await getFilterOptions();
        filterOptions.value = {
            categories: options.categories || [],
            brands: options.brands || [],
            colors: options.colors || [],
            sizes: options.sizes || [],
        };
    } catch (err) {
        console.error("Không thể tải filter options:", err);
    }
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price);
};

const toggleFilter = (key, value) => {
    const arr = filters.value[key];
    if (arr.includes(value)) {
        filters.value[key] = arr.filter((v) => v !== value);
    } else {
        filters.value[key] = [...arr, value];
    }
};

const clearFilters = () => {
    // Reset về trạng thái ban đầu
    filters.value = { ...initialFilters };
    // Emit với filters rỗng để hiển thị tất cả sản phẩm
    emit("filter", { ...initialFilters });
};

const applyFilters = () => {
    // Chỉ emit những filter có giá trị
    const activeFilters = {};

    if (filters.value.min_price !== null && filters.value.min_price !== '') {
        activeFilters.min_price = filters.value.min_price;
    }
    if (filters.value.max_price !== null && filters.value.max_price !== '') {
        activeFilters.max_price = filters.value.max_price;
    }
    if (filters.value.category.length > 0) {
        activeFilters.category = filters.value.category;
    }
    if (filters.value.brand.length > 0) {
        activeFilters.brand = filters.value.brand;
    }
    if (filters.value.color.length > 0) {
        activeFilters.color = filters.value.color;
    }
    if (filters.value.size.length > 0) {
        activeFilters.size = filters.value.size;
    }

    emit("filter", activeFilters);
};

// Watch để tự động áp dụng filter khi thay đổi
watch(filters, (newFilters) => {
    // Chỉ áp dụng filter khi có thay đổi thực sự
    const hasActiveFilters = Object.values(newFilters).some(value => {
        if (Array.isArray(value)) {
            return value.length > 0;
        }
        return value !== null && value !== '';
    });

    if (!hasActiveFilters) {
        // Nếu không có filter nào active, hiển thị tất cả sản phẩm
        emit("filter", {});
    }
}, { deep: true });
</script>
