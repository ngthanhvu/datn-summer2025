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
            <div class="flex flex-wrap gap-2">
                <button v-for="size in filterOptions.sizes" :key="size" @click="toggleFilter('size', size)" :class="[
                    'w-8 h-8 text-sm font-medium border rounded flex items-center justify-center cursor-pointer transition-colors',
                    filters.size.includes(size) ? 'bg-[#81aacc] text-white' : 'bg-white text-black hover:bg-gray-50'
                ]">
                    {{ size }}
                </button>
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
import { ref, onMounted } from "vue";
import { useProducts } from "../../composable/useProducts";

const emit = defineEmits(["filter"]);

const initialFilters = {
    min_price: 0,
    max_price: 1000000,
    brand: [],
    color: [],
    size: [],
};

const filters = ref({ ...initialFilters });

const filterOptions = ref({
    brands: [],
    colors: [],
    sizes: [],
});

const { getFilterOptions } = useProducts();

onMounted(async () => {
    try {
        const options = await getFilterOptions();
        filterOptions.value = {
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
    filters.value = { ...initialFilters };
    emit("filter", { ...initialFilters });
};

const applyFilters = () => {
    emit("filter", { ...filters.value });
};
</script>
