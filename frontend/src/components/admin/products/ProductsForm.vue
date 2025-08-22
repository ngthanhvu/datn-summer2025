<template>
    <div class="flex flex-col gap-6">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Left Column - Basic Info -->
            <div class="xl:col-span-1 space-y-6">
                <!-- Basic Info Section -->
                <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        Thông tin cơ bản
                    </h2>

                    <div v-if="isDataLoaded" class="space-y-4">
                        <!-- Tên sản phẩm -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tên sản phẩm <span class="text-red-500">*</span>
                            </label>
                            <input v-model="formData.name" type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                placeholder="Nhập tên sản phẩm" />
                            <div v-if="formErrors.name" class="text-red-500 text-sm mt-1">{{ formErrors.name }}</div>
                        </div>

                        <!-- Giá -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Giá bán <span class="text-red-500">*</span>
                            </label>
                            <input v-model="formData.price" type="number" min="0" step="1000"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                placeholder="Nhập giá sản phẩm" />
                            <div v-if="formErrors.price" class="text-red-500 text-sm mt-1">{{ formErrors.price }}</div>
                        </div>

                        <!-- Giá khuyến mãi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Giá khuyến mãi
                            </label>
                            <input v-model="formData.discount_price" type="number" min="0" step="1000"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                placeholder="Nhập giá khuyến mãi" />
                            <div v-if="formErrors.discount_price" class="text-red-500 text-sm mt-1">{{
                                formErrors.discount_price }}</div>
                        </div>

                        <!-- Danh mục & Thương hiệu -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Danh mục <span class="text-red-500">*</span>
                                </label>
                                <select v-model="formData.category"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="">Chọn danh mục</option>
                                    <option v-for="opt in basicFields.find(f => f.name === 'category').options"
                                        :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                                <div v-if="formErrors.category" class="text-red-500 text-sm mt-1">{{ formErrors.category
                                }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Thương hiệu <span class="text-red-500">*</span>
                                </label>
                                <select v-model="formData.brand"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="">Chọn thương hiệu</option>
                                    <option v-for="opt in basicFields.find(f => f.name === 'brand').options"
                                        :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                                <div v-if="formErrors.brand" class="text-red-500 text-sm mt-1">{{ formErrors.brand }}
                                </div>
                            </div>
                        </div>

                        <!-- Mô tả -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Mô tả <span class="text-red-500">*</span>
                            </label>
                            <CKEditor v-model="formData.description" />
                            <div v-if="formErrors.description" class="text-red-500 text-sm mt-1">{{
                                formErrors.description }}</div>
                        </div>

                        <!-- Trạng thái -->
                        <div class="flex items-center gap-3 pt-2">
                            <div class="toggle">
                                <input type="checkbox" id="status" v-model="formData.status" />
                                <label for="status"></label>
                            </div>
                            <span class="text-sm font-medium text-gray-700">{{ formData.status ? 'Hiển thị' : 'Ẩn'
                            }}</span>
                        </div>
                    </div>

                    <div v-else class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-2"></div>
                        <p class="text-gray-500">Đang tải danh mục và thương hiệu...</p>
                    </div>
                </div>
            </div>

            <!-- Right Column - Images & Variants -->
            <div class="xl:col-span-2 space-y-6">
                <!-- Images Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Ảnh chính -->
                    <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-image text-green-500"></i>
                            Ảnh chính <span class="text-red-500">*</span>
                        </h3>

                        <div class="space-y-4">
                            <label
                                class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                    <span class="text-gray-600 font-medium">Click để tải ảnh lên</span>
                                    <span class="text-xs text-gray-400 mt-1">PNG, JPG, GIF (tối đa 2MB)</span>
                                </div>
                                <input type="file" accept="image/*" class="hidden" @change="onMainImageChange" />
                            </label>

                            <div v-if="formErrors.mainImage" class="text-red-500 text-sm">{{ formErrors.mainImage }}
                            </div>

                            <div v-if="formData.mainImagePreview" class="relative w-full">
                                <img :src="formData.mainImagePreview"
                                    class="w-full h-48 object-cover rounded-lg shadow-md" />
                                <button @click="removeMainImage"
                                    class="absolute top-2 right-2 p-2 rounded-full bg-white shadow-lg hover:bg-gray-100 transition-colors"
                                    title="Xóa ảnh">
                                    <i class="fas fa-times text-red-500"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Ảnh phụ -->
                    <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-images text-purple-500"></i>
                            Ảnh phụ
                        </h3>

                        <div class="space-y-4">
                            <label
                                class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                    <span class="text-gray-600 font-medium">Click để tải ảnh lên</span>
                                    <span class="text-xs text-gray-400 mt-1">PNG, JPG, GIF (tối đa 2MB)</span>
                                </div>
                                <input type="file" accept="image/*" multiple class="hidden"
                                    @change="onAdditionalImagesChange" />
                            </label>

                            <div v-if="formErrors.additionalImages" class="text-red-500 text-sm">{{
                                formErrors.additionalImages }}</div>

                            <div v-if="formData.additionalImagePreviews.length > 0" class="grid grid-cols-3 gap-3">
                                <div v-for="(img, idx) in formData.additionalImagePreviews" :key="idx"
                                    class="relative group">
                                    <img :src="img" class="w-full h-24 object-cover rounded-lg shadow" />
                                    <button @click="removeAdditionalImage(idx)"
                                        class="absolute top-1 right-1 p-1 rounded-full bg-white shadow opacity-0 group-hover:opacity-100 transition-opacity"
                                        title="Xóa ảnh">
                                        <i class="fas fa-times text-red-500 text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Variants Section -->
                <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-palette text-orange-500"></i>
                            Biến thể sản phẩm
                        </h2>
                        <div class="flex gap-2 mt-3 sm:mt-0">
                            <button v-if="!showVariants" @click="showVariantsSection"
                                class="bg-blue-500 text-white rounded-lg px-4 py-2 flex items-center gap-2 hover:bg-blue-600 transition-colors">
                                <i class="fas fa-plus"></i>
                                <span>Thêm biến thể</span>
                            </button>
                            <button v-if="showVariants" @click="addVariant"
                                class="bg-green-500 text-white rounded-lg px-4 py-2 flex items-center gap-2 hover:bg-green-600 transition-colors cursor-pointer">
                                <i class="fas fa-plus"></i>
                                <span>Thêm biến thể</span>
                            </button>
                        </div>
                    </div>

                    <div v-if="showVariants" class="space-y-6">
                        <div v-for="(variant, vIdx) in formData.variants" :key="vIdx"
                            class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-800">Biến thể {{ vIdx + 1 }}</h3>
                                <button @click="removeVariantColor(vIdx)"
                                    class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition-colors cursor-pointer"
                                    title="Xóa biến thể">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                            <!-- Variant Form -->
                            <div class="space-y-4">
                                <!-- Color Input -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Màu sắc <span
                                            class="text-red-500">*</span></label>
                                    <input v-model="variant.colorName" type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                        placeholder="Nhập tên màu" />
                                    <div v-if="formErrors.variants[vIdx]?.color" class="text-red-500 text-sm mt-1">
                                        {{ formErrors.variants[vIdx].color }}
                                    </div>
                                </div>

                                <!-- Sizes Table -->
                                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                        <h4 class="font-medium text-gray-700">Kích thước và giá</h4>
                                    </div>

                                    <div class="p-4 ml-[50px]">
                                        <div v-for="(sizeObj, sIdx) in variant.sizes" :key="sIdx"
                                            class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end mb-4 p-4 bg-gray-50 rounded-lg">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Kích
                                                    thước</label>
                                                <input v-model="sizeObj.size" type="text"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                                    placeholder="Nhập kích thước" />
                                                <div v-if="formErrors.variants[vIdx]?.sizes[sIdx]?.size"
                                                    class="text-red-500 text-sm mt-1">
                                                    {{ formErrors.variants[vIdx].sizes[sIdx].size }}
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Giá</label>
                                                <input v-model="sizeObj.price" type="number" min="0" step="1000"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                                    placeholder="Nhập giá" />
                                                <div v-if="formErrors.variants[vIdx]?.sizes[sIdx]?.price"
                                                    class="text-red-500 text-sm mt-1">
                                                    {{ formErrors.variants[vIdx].sizes[sIdx].price }}
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">SKU</label>
                                                <div class="flex gap-2">
                                                    <input v-model="sizeObj.sku" type="text"
                                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                                        placeholder="Nhập mã SKU" />
                                                    <button v-if="variant.sizes.length > 1"
                                                        @click="removeSizeFromVariant(vIdx, sIdx)"
                                                        class="px-3 py-2 text-red-500 hover:text-red-700 border border-gray-300 rounded-md hover:bg-red-50 transition-colors cursor-pointer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <div v-if="formErrors.variants[vIdx]?.sizes[sIdx]?.sku"
                                                    class="text-red-500 text-sm mt-1">
                                                    {{ formErrors.variants[vIdx].sizes[sIdx].sku }}
                                                </div>
                                            </div>

                                            <!-- Variant Images (only for first size) -->
                                            <div v-if="sIdx === 0">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Ảnh biến
                                                    thể</label>
                                                <input type="file" multiple :id="`variant-upload-${vIdx}`"
                                                    @change="onVariantImagesChange($event, vIdx)" class="hidden" />
                                                <label :for="`variant-upload-${vIdx}`"
                                                    class="inline-flex items-center bg-blue-100 hover:bg-blue-500 text-blue-600 hover:text-white font-medium px-3 py-2 rounded-md cursor-pointer gap-2 text-sm transition-colors">
                                                    <i class="fas fa-upload"></i>
                                                    <span>Tải ảnh</span>
                                                </label>

                                                <div v-if="variant.imagesPreview && variant.imagesPreview.length"
                                                    class="flex gap-2 mt-2">
                                                    <img v-for="(img, i) in variant.imagesPreview" :key="i" :src="img"
                                                        class="w-12 h-12 object-cover rounded border border-gray-200" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Add Size Button -->
                                        <button @click="addSizeToVariant(vIdx)"
                                            class="bg-blue-100 text-blue-600 rounded-lg px-4 py-2 text-sm hover:bg-blue-200 transition-colors cursor-pointer">
                                            <i class="fas fa-plus mr-2"></i>
                                            Thêm kích thước
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-4 mt-8">
            <button @click="handleSubmit" :disabled="isSubmitting"
                class="bg-blue-500 text-white rounded-lg px-8 py-3 hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium cursor-pointer">
                <i v-if="isSubmitting" class="fas fa-spinner fa-spin mr-2"></i>
                {{ isSubmitting ? 'Đang tạo...' : 'Tạo sản phẩm' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useProducts } from '../../../composable/useProducts'
import CKEditor from '../../CKEditor.vue'
import { useRouter } from 'vue-router'
import { push } from 'notivue'

import { useCategoryStore } from '../../../stores/categories'
import { useBrandStore } from '../../../stores/brands'

const { createProduct } = useProducts()
const router = useRouter()

const isDataLoaded = ref(false)
const isSubmitting = ref(false)
const basicFields = ref([
    {
        name: 'name',
        label: 'Tên sản phẩm',
        type: 'text',
        placeholder: 'Nhập tên sản phẩm',
        required: true
    },
    {
        name: 'price',
        label: 'Giá bán',
        type: 'number',
        placeholder: 'Nhập giá sản phẩm',
        required: true,
        min: 0,
        step: 1000
    },
    {
        name: 'discount_price',
        label: 'Giá khuyến mãi',
        type: 'number',
        placeholder: 'Nhập giá khuyến mãi',
        required: false,
        min: 0,
        step: 1000
    },
    {
        name: 'category',
        label: 'Danh mục',
        type: 'select',
        placeholder: 'Chọn danh mục',
        required: true,
        options: []
    },
    {
        name: 'brand',
        label: 'Thương hiệu',
        type: 'select',
        placeholder: 'Chọn thương hiệu',
        required: true,
        options: []
    },
    {
        name: 'description',
        label: 'Mô tả',
        type: 'textarea',
        placeholder: 'Nhập mô tả sản phẩm',
        rows: 4
    },
    {
        name: 'status',
        label: 'Trạng thái',
        type: 'toggle'
    }
])

const formData = ref({
    name: '',
    price: 0,
    discount_price: 0,
    category: '',
    brand: '',
    description: '',
    status: true,
    mainImage: null,
    mainImagePreview: null,
    additionalImages: [],
    additionalImagePreviews: [],
    variants: []
})

const formErrors = ref({
    name: '',
    price: '',
    discount_price: '',
    category: '',
    brand: '',
    description: '',
    mainImage: '',
    additionalImages: '',
    variants: []
})

const showVariants = ref(false)

// const { createProduct } = useProducts()
const categoryStore = useCategoryStore()
const brandStore = useBrandStore()

onMounted(async () => {
    try {
        if (!categoryStore.categories.length) {
            await categoryStore.fetchCategories()
        }
        if (!brandStore.brands.length) {
            await brandStore.fetchBrands()
        }
        const catOptions = categoryStore.categories.map(cat => ({ value: String(cat.id), label: cat.name }))
        const categoryField = basicFields.value.find(f => f.name === 'category')
        if (categoryField) {
            categoryField.options = catOptions
        }
        const brandOptions = brandStore.brands.map(brand => ({ value: String(brand.id), label: brand.name }))
        const brandField = basicFields.value.find(f => f.name === 'brand')
        if (brandField) {
            brandField.options = brandOptions
        }
        isDataLoaded.value = true
    } catch (err) {
        console.error('Không thể tải danh mục/thương hiệu', err)
    }
})

const validateImage = (file) => {
    const validTypes = ['image/png', 'image/jpeg', 'image/gif']
    const maxSize = 2 * 1024 * 1024 // 2MB
    if (!validTypes.includes(file.type)) {
        return 'Định dạng ảnh không hợp lệ. Chỉ chấp nhận PNG, JPG, GIF.'
    }
    if (file.size > maxSize) {
        return 'Kích thước ảnh vượt quá 2MB.'
    }
    return ''
}

const validateForm = () => {
    let hasError = false
    const errors = {
        name: '',
        price: '',
        discount_price: '',
        category: '',
        brand: '',
        description: '',
        mainImage: '',
        additionalImages: '',
        variants: formData.value.variants.map(() => ({
            color: '',
            sizes: []
        }))
    }

    if (!formData.value.name) {
        errors.name = 'Vui lòng nhập tên sản phẩm'
        hasError = true
    }
    if (!formData.value.price || formData.value.price <= 0) {
        errors.price = 'Vui lòng nhập giá sản phẩm hợp lệ'
        hasError = true
    }
    if (!formData.value.category) {
        errors.category = 'Vui lòng chọn danh mục'
        hasError = true
    }
    if (!formData.value.brand) {
        errors.brand = 'Vui lòng chọn thương hiệu'
        hasError = true
    }

    const descriptionText = formData.value.description
        ?.replace(/<(.|\n)*?>/g, '')
        .trim()

    if (!descriptionText) {
        errors.description = 'Vui lòng nhập mô tả sản phẩm'
        hasError = true
    }

    if (!formData.value.mainImage) {
        errors.mainImage = 'Vui lòng chọn ảnh chính'
        hasError = true
    } else {
        const mainImageError = validateImage(formData.value.mainImage)
        if (mainImageError) {
            errors.mainImage = mainImageError
            hasError = true
        }
    }

    if (formData.value.additionalImages.length > 0) {
        formData.value.additionalImages.forEach((file, idx) => {
            const error = validateImage(file)
            if (error) {
                errors.additionalImages = error
                hasError = true
            }
        })
    }

    if (showVariants.value && formData.value.variants.length > 0) {
        // Kiểm tra trùng màu
        const colorNames = formData.value.variants.map(v => v.colorName.trim().toLowerCase()).filter(c => c)
        const duplicateColors = colorNames.filter((color, index) => colorNames.indexOf(color) !== index)

        if (duplicateColors.length > 0) {
            formData.value.variants.forEach((variant, vIdx) => {
                if (variant.colorName.trim().toLowerCase() === duplicateColors[0]) {
                    errors.variants[vIdx].color = 'Màu sắc này đã tồn tại, vui lòng chọn màu khác'
                    hasError = true
                }
            })
        }

        formData.value.variants.forEach((variant, vIdx) => {
            if (!variant.colorName) {
                errors.variants[vIdx].color = 'Vui lòng nhập tên màu sắc'
                hasError = true
            }
            if (!variant.sizes || variant.sizes.length === 0) {
                errors.variants[vIdx].sizes = [{ size: 'Thêm ít nhất 1 size' }]
                hasError = true
            } else {
                errors.variants[vIdx].sizes = []

                // Kiểm tra trùng kích thước trong cùng một variant
                const sizesInVariant = variant.sizes.map(s => s.size.trim().toLowerCase()).filter(s => s)
                const duplicateSizesInVariant = sizesInVariant.filter((size, index) => sizesInVariant.indexOf(size) !== index)

                variant.sizes.forEach((sizeObj, sIdx) => {
                    const sizeErr = { size: '', price: '', sku: '' }

                    if (!sizeObj.size) {
                        sizeErr.size = 'Nhập kích thước'
                        hasError = true
                    } else if (duplicateSizesInVariant.includes(sizeObj.size.trim().toLowerCase())) {
                        sizeErr.size = 'Kích thước này đã tồn tại trong màu sắc này'
                        hasError = true
                    }

                    if (!sizeObj.price || sizeObj.price <= 0) {
                        sizeErr.price = 'Nhập giá hợp lệ'
                        hasError = true
                    }
                    if (!sizeObj.sku) {
                        sizeErr.sku = 'Nhập mã SKU'
                        hasError = true
                    }
                    errors.variants[vIdx].sizes.push(sizeErr)
                })
            }
        })
    }

    formErrors.value = errors
    return !hasError
}

const handleSubmit = async () => {
    try {
        if (!validateForm()) {
            Toast.fire({
                icon: 'error',
                title: 'Vui lòng kiểm tra và điền đầy đủ thông tin'
            })
            return
        }

        isSubmitting.value = true
        const productData = new FormData()

        productData.append('name', formData.value.name)
        productData.append('description', formData.value.description)
        productData.append('price', String(formData.value.price))
        productData.append('discount_price', String(formData.value.discount_price))
        productData.append('is_active', formData.value.status ? '1' : '0')
        productData.append('categories_id', String(formData.value.category))
        productData.append('brand_id', String(formData.value.brand))

        if (formData.value.mainImage) {
            productData.append('is_main', formData.value.mainImage)
        }

        formData.value.additionalImages.forEach(img => {
            productData.append('image_path[]', img)
        })

        formData.value.variants.forEach((variant, vIdx) => {
            // Gửi color cho variant
            productData.append(`variants[${vIdx}][color]`, variant.colorName)
            // Gửi ảnh cho variant
            if (variant.images && variant.images.length > 0) {
                variant.images.forEach(imgFile => {
                    productData.append(`variants[${vIdx}][images][]`, imgFile)
                })
            }
            // Gửi từng size cho variant
            variant.sizes.forEach((sizeObj, sIdx) => {
                productData.append(`variants[${vIdx}][sizes][${sIdx}][size]`, sizeObj.size)
                productData.append(`variants[${vIdx}][sizes][${sIdx}][price]`, sizeObj.price)
                productData.append(`variants[${vIdx}][sizes][${sIdx}][sku]`, sizeObj.sku)
            })
        })

        const response = await createProduct(productData)
        push.success('Tạo sản phẩm thành công!')
        // await navigateTo('/admin/products')
        await router.push('/admin/products')
    } catch (error) {
        notyf.error(error.response?.data?.message || 'Có lỗi khi tạo sản phẩm')
    } finally {
        isSubmitting.value = false
    }
}

const showVariantsSection = () => {
    showVariants.value = true
    addVariant()
}

const generateSKU = (name) => {
    const randomNum = Math.floor(Math.random() * 1000000).toString().padStart(6, '0')
    const namePart = name
        .toUpperCase()
        .replace(/[^A-Z0-9]/g, '')
        .substring(0, 4)
    return `${namePart}-${randomNum}`
}

const addVariant = () => {
    // Kiểm tra xem có thể thêm variant mới không
    if (formData.value.variants.length > 0) {
        const lastVariant = formData.value.variants[formData.value.variants.length - 1]
        if (!lastVariant.colorName || !lastVariant.sizes || lastVariant.sizes.length === 0) {
            push.error('Vui lòng hoàn thành biến thể hiện tại trước khi thêm mới')
            return
        }

        // Kiểm tra xem có trùng màu không
        const newColorName = lastVariant.colorName.trim().toLowerCase()
        const existingColors = formData.value.variants.slice(0, -1).map(v => v.colorName.trim().toLowerCase())
        if (existingColors.includes(newColorName)) {
            push.error('Màu sắc này đã tồn tại, vui lòng chọn màu khác')
            return
        }
    }

    formData.value.variants.push({
        colorName: '',
        sizes: [{
            size: '',
            price: formData.value.price || 0,
            sku: generateSKU(formData.value.name)
        }]
    })
    formErrors.value.variants.push({
        color: '',
        sizes: [{
            size: '',
            price: '',
            sku: ''
        }]
    })
}

const removeVariantColor = (vIdx) => {
    formData.value.variants.splice(vIdx, 1)
    formErrors.value.variants.splice(vIdx, 1)
}

const addSizeToVariant = (vIdx) => {
    const variant = formData.value.variants[vIdx]
    if (!variant.colorName) {
        push.error('Vui lòng nhập tên màu sắc trước khi thêm kích thước')
        return
    }

    const productPrice = formData.value.price || 0
    formData.value.variants[vIdx].sizes.push({
        size: '',
        price: productPrice,
        sku: generateSKU(formData.value.name)
    })
    formErrors.value.variants[vIdx].sizes.push({
        size: '',
        price: '',
        sku: ''
    })
}

const removeSizeFromVariant = (vIdx, sIdx) => {
    if (formData.value.variants[vIdx].sizes.length <= 1) return
    formData.value.variants[vIdx].sizes.splice(sIdx, 1)
    formErrors.value.variants[vIdx].sizes.splice(sIdx, 1)
}

const onMainImageChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        const error = validateImage(file)
        if (error) {
            formErrors.value.mainImage = error
            return
        }
        formData.value.mainImage = file
        const reader = new FileReader()
        reader.onload = (ev) => {
            formData.value.mainImagePreview = ev.target.result
        }
        reader.readAsDataURL(file)
        formErrors.value.mainImage = ''
    } else {
        formData.value.mainImage = null
        formData.value.mainImagePreview = null
    }
}

const removeMainImage = () => {
    formData.value.mainImage = null
    formData.value.mainImagePreview = null
    formErrors.value.mainImage = 'Vui lòng chọn ảnh chính'
}

const onAdditionalImagesChange = (e) => {
    const files = Array.from(e.target.files)
    let hasError = false
    files.forEach(file => {
        const error = validateImage(file)
        if (error) {
            formErrors.value.additionalImages = error
            hasError = true
        }
    })
    if (!hasError) {
        formData.value.additionalImages = files
        formData.value.additionalImagePreviews = []
        files.forEach(file => {
            const reader = new FileReader()
            reader.onload = (ev) => {
                formData.value.additionalImagePreviews.push(ev.target.result)
            }
            reader.readAsDataURL(file)
        })
        formErrors.value.additionalImages = ''
    }
}

const removeAdditionalImage = (idx) => {
    formData.value.additionalImages.splice(idx, 1)
    formData.value.additionalImagePreviews.splice(idx, 1)
}

const onVariantImagesChange = (e, vIdx) => {
    const files = Array.from(e.target.files)
    if (!formData.value.variants[vIdx].images) formData.value.variants[vIdx].images = []
    if (!formData.value.variants[vIdx].imagesPreview) formData.value.variants[vIdx].imagesPreview = []
    formData.value.variants[vIdx].images = files
    formData.value.variants[vIdx].imagesPreview = []
    files.forEach(file => {
        const reader = new FileReader()
        reader.onload = (ev) => {
            formData.value.variants[vIdx].imagesPreview.push(ev.target.result)
        }
        reader.readAsDataURL(file)
    })
}
</script>

<style scoped>
.bg-primary {
    background-color: #3bb77e;
}

.bg-primary-dark {
    background-color: #2ea16d;
}

.toggle {
    position: relative;
    display: inline-block;
}

.toggle input {
    display: none;
}

.toggle label {
    display: block;
    width: 48px;
    height: 24px;
    background: #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    transition: background 0.3s;
}

.toggle label::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    transition: transform 0.3s;
}

.toggle input:checked+label {
    background: #3bb77e;
}

.toggle input:checked+label::after {
    transform: translateX(24px);
}

:deep(.ck-editor__editable_inline) {
    min-height: 200px;
    max-height: 400px;
}

/* Responsive Styles */
@media (max-width: 1024px) {
    .variants-section {
        margin-top: 1rem;
    }
}

@media (max-width: 768px) {
    .input {
        font-size: 16px;
        /* Prevent zoom on iOS */
    }

    :deep(.ck-editor__editable_inline) {
        min-height: 150px;
        max-height: 300px;
    }
}

@media (max-width: 640px) {
    .variants-section {
        margin-top: 0.5rem;
    }

    .variants-section h2 {
        font-size: 1.125rem;
    }

    .variants-section button {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
}

/* Mobile-specific improvements */
@media (max-width: 480px) {
    .grid {
        gap: 0.75rem;
    }

    .space-y-4>*+* {
        margin-top: 1rem;
    }

    .p-4 {
        padding: 1rem;
    }

    .rounded-\[10px\] {
        border-radius: 0.5rem;
    }
}

/* Touch-friendly improvements */
@media (hover: none) and (pointer: coarse) {

    .action-btn,
    .toggle label,
    input[type="file"]+label {
        min-height: 44px;
        /* iOS recommended touch target size */
    }

    .toggle label {
        width: 52px;
        height: 28px;
    }

    .toggle label::after {
        width: 24px;
        height: 24px;
    }

    .toggle input:checked+label::after {
        transform: translateX(24px);
    }
}
</style>
