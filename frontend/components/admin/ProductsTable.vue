<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <!-- Table Header with Search and Add Button -->
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
            <div class="tw-flex tw-gap-4">
                <!-- Search box -->
                <div class="tw-relative">
                    <input type="text" v-model="searchQuery" placeholder="Tìm kiếm..." @input="handleSearch"
                        class="tw-border tw-rounded tw-px-4 tw-py-2 tw-pl-10 tw-w-64">
                    <i
                        class="fas fa-search tw-absolute tw-left-3 tw-top-1/2 tw-transform -tw-translate-y-1/2 tw-text-gray-400"></i>
                </div>

                <!-- Filters -->
                <select v-if="categories.length" v-model="selectedCategory"
                    class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56">
                    <option value="">Tất cả danh mục</option>
                    <option v-for="category in categories" :key="category.value" :value="category.value">
                        {{ category.label }}
                    </option>
                </select>

                <select v-if="brands.length" v-model="selectedBrand"
                    class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56">
                    <option value="">Tất cả thương hiệu</option>
                    <option v-for="brand in brands" :key="brand.value" :value="brand.value">
                        {{ brand.label }}
                    </option>
                </select>

                <select v-model="selectedStatus" class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56">
                    <option value="">Tất cả trạng thái</option>
                    <option value="1">Hoạt động</option>
                    <option value="0">Vô hiệu</option>
                </select>
            </div>

            <!-- Add button -->
            <NuxtLink to="/admin/products/create"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-primary-dark">
                <i class="fas fa-plus"></i>
                Thêm mới
            </NuxtLink>
        </div>

        <!-- Main Table -->
        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-left">
                <thead>
                    <tr class="tw-border-b tw-bg-gray-50">
                        <th v-for="column in columns" :key="column.key" class="tw-px-4 tw-py-3 tw-font-semibold"
                            @click="sortBy(column.key)">
                            {{ column.label }}
                            <i v-if="sortKey === column.key"
                                :class="['fas', sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down']"></i>
                        </th>
                        <th class="tw-px-4 tw-py-3 tw-font-semibold">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in paginatedData" :key="index" class="tw-border-b hover:tw-bg-gray-50">
                        <td v-for="column in columns" :key="column.key" class="tw-px-4 tw-py-3">
                            <!-- Main image column -->
                            <template v-if="column.type === 'main_image'">
                                <img :src="getMainImage(item.images)?.image_path"
                                    :alt="getMainImage(item.images)?.image_path"
                                    class="tw-w-12 tw-h-12 tw-object-cover tw-rounded" />
                            </template>

                            <!-- Sub images column -->
                            <template v-else-if="column.type === 'sub_images'">
                                <div class="tw-flex tw-gap-1">
                                    <img v-for="image in getSubImages(item.images)" :key="image.id"
                                        :src="image.image_path" :alt="image.image_path"
                                        class="tw-w-8 tw-h-8 tw-object-cover tw-rounded tw-cursor-pointer hover:tw-opacity-75"
                                        @click="handleImageClick(image)" />
                                </div>
                            </template>

                            <!-- Brand column -->
                            <template v-else-if="column.type === 'brand'">
                                <span class="tw-text-sm">{{ item[column.key] }}</span>
                            </template>

                            <!-- Category column -->
                            <template v-else-if="column.type === 'category'">
                                <span class="tw-text-sm">{{ item[column.key] }}</span>
                            </template>

                            <!-- Status column -->
                            <template v-else-if="column.type === 'status'">
                                <span :class="getStatusBadgeClass(item[column.key])">
                                    {{ getStatusText(item[column.key]) }}
                                </span>
                            </template>

                            <!-- Price column -->
                            <template v-else-if="column.type === 'price'">
                                {{ formatPrice(item[column.key]) }}
                            </template>

                            <!-- Variants column -->
                            <template v-else-if="column.type === 'variants'">
                                <Badges :variants="item[column.key]" />
                            </template>

                            <!-- Default column -->
                            <template v-else>
                                {{ item[column.key] }}
                            </template>
                        </td>

                        <!-- Actions column -->
                        <td class="tw-px-4 tw-py-3">
                            <div class="tw-flex tw-gap-2">
                                <NuxtLink :to="`/admin/products/${item.id}/edit`"
                                    class="tw-bg-blue-600 tw-text-white tw-rounded tw-p-2 hover:tw-bg-blue-700">
                                    <i class="fas fa-edit"></i>
                                </NuxtLink>
                                <button @click="$emit('delete', item)"
                                    class="tw-bg-red-600 tw-text-white tw-rounded tw-p-2 hover:tw-bg-red-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="tw-flex tw-justify-between tw-items-center tw-mt-4">
            <div class="tw-text-sm tw-text-gray-600">
                Hiển thị {{ paginatedData.length }} trên tổng số {{ filteredData.length }} bản ghi
            </div>
            <div class="tw-flex tw-gap-2">
                <button :disabled="currentPage === 1" @click="currentPage--"
                    class="tw-px-3 tw-py-1 tw-border tw-rounded hover:tw-bg-gray-50 disabled:tw-opacity-50">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="tw-px-3 tw-py-1">
                    Trang {{ currentPage }} / {{ totalPages }}
                </span>
                <button :disabled="currentPage === totalPages" @click="currentPage++"
                    class="tw-px-3 tw-py-1 tw-border tw-rounded hover:tw-bg-gray-50 disabled:tw-opacity-50">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Badges from './Badges.vue'

const props = defineProps({
    data: {
        type: Array,
        required: true,
        default: () => []
    },
    columns: {
        type: Array,
        required: true,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    },
    brands: {
        type: Array,
        default: () => []
    },
    itemsPerPage: {
        type: Number,
        default: 10
    }
})

const emit = defineEmits(['delete', 'filter-change'])

// State
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedBrand = ref('')
const selectedStatus = ref('')
const currentPage = ref(1)
const sortKey = ref('')
const sortOrder = ref('asc')

// Computed
const filteredData = computed(() => {
    let result = [...props.data]

    // Search
    if (searchQuery.value) {
        result = result.filter(item =>
            Object.values(item).some(val =>
                String(val).toLowerCase().includes(searchQuery.value.toLowerCase())
            )
        )
    }

    if (selectedCategory.value) {
        result = result.filter(item => item.category === selectedCategory.value)
    }

    if (selectedBrand.value) {
        result = result.filter(item => item.brand === selectedBrand.value)
    }

    if (selectedStatus.value) {
        result = result.filter(item => item.is_active === parseInt(selectedStatus.value))
    }

    if (sortKey.value) {
        result.sort((a, b) => {
            const aVal = a[sortKey.value]
            const bVal = b[sortKey.value]
            if (sortOrder.value === 'asc') {
                return aVal > bVal ? 1 : -1
            } else {
                return aVal < bVal ? 1 : -1
            }
        })
    }

    return result
})

const totalPages = computed(() =>
    Math.ceil(filteredData.value.length / props.itemsPerPage)
)

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * props.itemsPerPage
    const end = start + props.itemsPerPage
    return filteredData.value.slice(start, end)
})

// Methods
const handleSearch = () => {
    currentPage.value = 1
}

const sortBy = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortKey.value = key
        sortOrder.value = 'asc'
    }
}

// Watch for filter changes
watch([selectedCategory, selectedBrand, selectedStatus], () => {
    currentPage.value = 1
    emit('filter-change', {
        category: selectedCategory.value,
        brand: selectedBrand.value,
        status: selectedStatus.value
    })
})

// Utility functions
const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const getStatusBadgeClass = (status) => {
    return status === 1
        ? 'tw-bg-green-100 tw-text-green-700 tw-px-2 tw-py-1 tw-rounded-full tw-text-xs'
        : 'tw-bg-red-100 tw-text-red-700 tw-px-2 tw-py-1 tw-rounded-full tw-text-xs'
}

const getStatusText = (status) => {
    return status === 1 ? 'Hoạt động' : 'Vô hiệu'
}

const handleImageClick = (image) => {
    // Handle image click if needed
    console.log('Image clicked:', image)
}

const getMainImage = (images) => {
    return images?.find(img => img.is_main === 1)
}

const getSubImages = (images) => {
    return images?.filter(img => img.is_main === 0) || []
}
</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}
</style>