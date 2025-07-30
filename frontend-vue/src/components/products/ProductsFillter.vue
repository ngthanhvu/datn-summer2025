<template>
    <aside class="bg-white rounded-lg shadow-sm p-6 w-full md:w-72">
        <h2 class="text-lg font-semibold mb-2">Tất cả sản phẩm</h2>
        <h3 class="text-base font-medium mb-4">Bộ lọc sản phẩm</h3>

        <!-- Price Range -->
        <div class="mb-4">
            <h4 class="font-medium mb-2 text-md">Khoảng giá</h4>
            <div class="flex gap-2">
                <input type="number" v-model="filters.min_price" placeholder="Từ"
                    class="w-full px-2 py-1 border rounded text-md border-gray-300" />
                <input type="number" v-model="filters.max_price" placeholder="Đến"
                    class="w-full px-2 py-1 border rounded text-md border-gray-300" />
            </div>
        </div>

        <!-- Categories -->
        <div class="mb-4">
            <h4 class="font-medium mb-2 text-md">Danh mục</h4>
            <div class="space-y-1 text-md">
                <label v-for="category in filterOptions.categories" :key="category.id" class="flex items-center gap-2">
                    <input type="checkbox" :value="category.id" v-model="filters.category" />
                    {{ category.name }}
                </label>
            </div>
        </div>

        <!-- Brands -->
        <div class="mb-4">
            <h4 class="font-medium mb-2 text-md">Thương hiệu</h4>
            <div class="space-y-1 text-md">
                <label v-for="brand in filterOptions.brands" :key="brand.id" class="flex items-center gap-2">
                    <input type="checkbox" :value="brand.id" v-model="filters.brand" />
                    {{ brand.name }}
                </label>
            </div>
        </div>

        <!-- Colors -->
        <div class="mb-4">
            <h4 class="font-medium mb-2 text-md">Màu sắc</h4>
            <div class="space-y-1 text-md">
                <label v-for="color in filterOptions.colors" :key="color" class="flex items-center gap-2">
                    <input type="checkbox" :value="color" v-model="filters.color" />
                    {{ color }}
                </label>
            </div>
        </div>

        <!-- Sizes -->
        <div class="mb-4">
            <h4 class="font-medium mb-2 text-md">Kích thước</h4>
            <div class="space-y-1 text-md">
                <label v-for="size in filterOptions.sizes" :key="size" class="flex items-center gap-2">
                    <input type="checkbox" :value="size" v-model="filters.size" />
                    {{ size }}
                </label>
            </div>
        </div>

        <!-- Apply Filters Button -->
        <button @click="applyFilters"
            class="w-full bg-[#81AACC] text-white py-2 px-4 rounded hover:bg-[#6ba0cc] text-md">
            Áp dụng bộ lọc
        </button>
    </aside>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useProducts } from '../../composable/useProducts' // Cập nhật path nếu khác
const emit = defineEmits(['filter'])

const filters = ref({
    min_price: null,
    max_price: null,
    category: [],
    brand: [],
    color: [],
    size: []
})

const filterOptions = ref({
    categories: [],
    brands: [],
    colors: [],
    sizes: []
})

const { getFilterOptions } = useProducts()

onMounted(async () => {
    try {
        const options = await getFilterOptions()
        filterOptions.value = {
            categories: options.categories || [],
            brands: options.brands || [],
            colors: options.colors || [],
            sizes: options.sizes || []
        }
    } catch (err) {
        console.error('Không thể tải filter options:', err)
    }
})

const applyFilters = () => {
    emit('filter', { ...filters.value })
}
</script>
