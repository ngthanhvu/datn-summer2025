<template>
    <div class="tw-flex tw-flex-col tw-gap-6">
        <div class="tw-grid tw-grid-cols-5 tw-gap-6">
            <div class="tw-col-span-2 tw-space-y-4 tw-p-6 tw-rounded-[10px] tw-border tw-border-gray-150 tw-bg-white">
                <div v-if="isDataLoaded">
                    <!-- Tên sản phẩm -->
                    <div class="tw-mb-4">
                        <label class="tw-block tw-font-medium">Tên sản phẩm <span
                                class="tw-text-red-500">*</span></label>
                        <input v-model="formData.name" type="text"
                            class="tw-input tw-w-full tw-border tw-rounded tw-p-2 focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                            placeholder="Nhập tên sản phẩm" />
                        <div v-if="formErrors.name" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ formErrors.name }}
                        </div>
                    </div>
                    <!-- Giá -->
                    <div class="tw-mb-4">
                        <label class="tw-block tw-font-medium">Giá bán <span class="tw-text-red-500">*</span></label>
                        <input v-model="formData.price" type="number" min="0" step="1000"
                            class="tw-input tw-w-full tw-border tw-rounded tw-p-2 focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                            placeholder="Nhập giá sản phẩm" />

                        <div v-if="formErrors.price" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ formErrors.price }}
                        </div>
                    </div>
                    <!-- Giá khuyến mãi -->
                    <div class="tw-mb-4">
                        <label class="tw-block tw-font-medium">Giá khuyến mãi</label>
                        <input v-model="formData.discount_price" type="number" min="0" step="1000"
                            class="tw-input tw-w-full tw-border tw-rounded tw-p-2 focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                            placeholder="Nhập giá khuyến mãi" />
                        <div v-if="formErrors.discount_price" class="tw-text-red-500 tw-text-sm tw-mt-1">{{
                            formErrors.discount_price }}
                        </div>
                    </div>
                    <!-- Danh mục -->
                    <div class="tw-mb-4">
                        <label class="tw-block tw-font-medium">Danh mục <span class="tw-text-red-500">*</span></label>
                        <select v-model="formData.category"
                            class="tw-input tw-w-full tw-border tw-rounded tw-p-2 focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100">
                            <option value="">Chọn danh mục</option>
                            <option v-for="opt in basicFields.find(f => f.name === 'category').options" :key="opt.value"
                                :value="opt.value">{{ opt.label }}</option>
                        </select>
                        <div v-if="formErrors.category" class="tw-text-red-500 tw-text-sm tw-mt-1">{{
                            formErrors.category }}</div>
                    </div>
                    <!-- Thương hiệu -->
                    <div class="tw-mb-4">
                        <label class="tw-block tw-font-medium">Thương hiệu <span
                                class="tw-text-red-500">*</span></label>
                        <select v-model="formData.brand"
                            class="tw-input tw-w-full tw-border tw-rounded tw-p-2 focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100">
                            <option value="">Chọn thương hiệu</option>
                            <option v-for="opt in basicFields.find(f => f.name === 'brand').options" :key="opt.value"
                                :value="opt.value">{{ opt.label }}</option>
                        </select>
                        <div v-if="formErrors.brand" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ formErrors.brand }}
                        </div>
                    </div>
                    <!-- Mô tả -->
                    <div class="tw-mb-4">
                        <label class="tw-block tw-font-medium">Mô tả <span class="tw-text-red-500">*</span></label>
                        <CKEditor v-model="formData.description" />
                        <div v-if="formErrors.description" class="tw-text-red-500 tw-text-sm tw-mt-1">{{
                            formErrors.description }}</div>
                    </div>
                    <!-- Trạng thái -->
                    <div class="toggle">
                        <input type="checkbox" id="status" v-model="formData.status" />
                        <label for="status"></label>
                    </div>
                    <span class="tw-ml-2">{{ formData.status ? 'Hiển thị' : 'Ẩn' }}</span>
                </div>
                <div v-else class="tw-text-center tw-text-gray-500">Đang tải danh mục và thương hiệu...</div>
            </div>
            <div class="tw-col-span-3 tw-space-y-4">
                <!-- Ảnh chính -->
                <div class="tw-mb-4 tw-border tw-border-gray-150 tw-p-5 tw-rounded-[10px] tw-bg-white">
                    <label class="tw-block tw-font-medium">Ảnh chính <span class="tw-text-red-500">*</span></label>
                    <div>
                        <label
                            class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-w-full tw-h-40 tw-border-2 tw-border-gray-300 tw-border-dashed tw-rounded-lg tw-cursor-pointer hover:tw-bg-gray-50">
                            <div class="tw-flex tw-flex-col tw-items-center tw-justify-center">
                                <i class="fas fa-cloud-upload-alt tw-text-3xl tw-text-gray-400 tw-mb-2"></i>
                                <span class="tw-text-gray-500 tw-font-semibold">Click để tải ảnh lên</span>
                                <span class="tw-text-xs tw-text-gray-400">PNG, JPG, GIF (tối đa 2MB)</span>
                            </div>
                            <input type="file" accept="image/*" class="tw-hidden" @change="onMainImageChange" />
                        </label>
                    </div>
                    <div v-if="formErrors.mainImage" class="tw-text-red-500 tw-text-sm tw-mt-1">{{
                        formErrors.mainImage }}</div>
                    <div v-if="formData.mainImagePreview" class="tw-relative tw-w-48 tw-h-48 tw-mt-4">
                        <img :src="formData.mainImagePreview"
                            class="tw-w-full tw-h-full tw-object-cover tw-rounded-lg tw-shadow" />
                        <button @click="removeMainImage"
                            class="tw-absolute tw-top-2 tw-right-2 tw-p-2 tw-rounded-full tw-bg-white tw-shadow hover:tw-bg-gray-100"
                            title="Xóa ảnh">
                            <i class="fas fa-times tw-text-red-500"></i>
                        </button>
                    </div>
                </div>
                <!-- Ảnh phụ -->
                <div class="tw-mb-4 tw-border tw-border-gray-150 tw-p-5 tw-rounded-[10px] tw-bg-white">
                    <label class="tw-block tw-font-medium">Ảnh phụ <span class="tw-text-red-500">*</span></label>
                    <div>
                        <label
                            class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-w-full tw-h-40 tw-border-2 tw-border-gray-300 tw-border-dashed tw-rounded-lg tw-cursor-pointer hover:tw-bg-gray-50">
                            <div class="tw-flex tw-flex-col tw-items-center tw-justify-center">
                                <i class="fas fa-cloud-upload-alt tw-text-3xl tw-text-gray-400 tw-mb-2"></i>
                                <span class="tw-text-gray-500 tw-font-semibold">Click để tải ảnh lên</span>
                                <span class="tw-text-xs tw-text-gray-400">PNG, JPG, GIF (tối đa 2MB)</span>
                            </div>
                            <input type="file" accept="image/*" multiple class="tw-hidden"
                                @change="onAdditionalImagesChange" />
                        </label>
                    </div>
                    <div v-if="formErrors.additionalImages" class="tw-text-red-500 tw-text-sm tw-mt-1">{{
                        formErrors.additionalImages }}</div>
                    <div v-if="formData.additionalImagePreviews.length > 0"
                        class="tw-grid tw-grid-cols-4 tw-gap-4 tw-mt-4">
                        <div v-for="(img, idx) in formData.additionalImagePreviews" :key="idx"
                            class="tw-relative tw-group">
                            <img :src="img" class="tw-w-full tw-h-32 tw-object-cover tw-rounded-lg tw-shadow" />
                            <button @click="removeAdditionalImage(idx)"
                                class="tw-absolute tw-top-2 tw-right-2 tw-p-2 tw-rounded-full tw-bg-white tw-shadow tw-opacity-0 group-hover:tw-opacity-100 tw-transition-opacity"
                                title="Xóa ảnh">
                                <i class="fas fa-times tw-text-red-500"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                    <h2 class="tw-text-xl tw-font-semibold">Biến thể sản phẩm</h2>
                    <div class="tw-flex tw-gap-2">
                        <button v-if="!showVariants" @click="showVariantsSection"
                            class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
                            <i class="fas fa-plus"></i>
                            Thêm biến thể
                        </button>
                        <button v-if="showVariants" @click="addVariant"
                            class="tw-bg-white tw-border tw-border-gray-150 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">

                            <i class="fas fa-plus"></i>
                            Thêm biến thể
                        </button>
                    </div>
                </div>

                <div v-if="showVariants" class="tw-w-full">
                    <div class="tw-space-y-4">
                        <div v-for="(variant, vIdx) in formData.variants" :key="vIdx"
                            class="tw-bg-white tw-border tw-border-gray-150 tw-p-4 tw-rounded-lg">
                            <div class="tw-flex tw-justify-between tw-mb-4">
                                <h3 class="tw-font-medium">Biến thể {{ vIdx + 1 }}</h3>
                                <button @click="removeVariantColor(vIdx)" class="tw-text-red-500 hover:tw-text-red-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                            <!-- Header row for labels -->
                            <div class="tw-grid tw-grid-cols-5 tw-gap-4 tw-mb-2">
                                <div class="tw-font-medium">Màu sắc</div>
                                <div class="tw-font-medium">Kích thước</div>
                                <div class="tw-font-medium">Giá</div>
                                <div class="tw-font-medium">SKU</div>
                                <div class="tw-font-medium">Ảnh phụ</div>
                            </div>

                            <!-- Variant rows -->
                            <div v-for="(sizeObj, sIdx) in variant.sizes" :key="sIdx"
                                class="tw-grid tw-grid-cols-5 tw-gap-2 tw-items-center tw-mb-2 tw-p-2 tw-bg-white tw-rounded tw-border-b tw-border-gray-100 last:tw-border-b-0">
                                <!-- Màu sắc -->
                                <div>
                                    <input v-model="variant.colorName" type="text"
                                        class="tw-input tw-w-40 tw-border tw-rounded tw-p-2 focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                                        placeholder="Nhập tên màu" />
                                    <div v-if="formErrors.variants[vIdx]?.color"
                                        class="tw-text-red-500 tw-text-sm tw-mt-1">
                                        {{ formErrors.variants[vIdx].color }}
                                    </div>
                                </div>
                                <!-- Kích thước -->
                                <div>
                                    <input v-model="sizeObj.size" type="text"
                                        class="tw-input tw-w-40 tw-border tw-rounded tw-p-2 focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                                        placeholder="Nhập kích thước" />
                                    <div v-if="formErrors.variants[vIdx]?.sizes[sIdx]?.size"
                                        class="tw-text-red-500 tw-text-sm tw-mt-1">{{
                                            formErrors.variants[vIdx].sizes[sIdx].size }}</div>
                                </div>
                                <!-- Giá -->
                                <div>
                                    <input v-model="sizeObj.price" type="number" min="0" step="1000"
                                        class="tw-input tw-w-40 tw-border tw-rounded tw-p-2 focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                                        placeholder="Nhập giá" />
                                    <div v-if="formErrors.variants[vIdx]?.sizes[sIdx]?.price"
                                        class="tw-text-red-500 tw-text-sm tw-mt-1">{{
                                            formErrors.variants[vIdx].sizes[sIdx].price }}</div>
                                </div>
                                <!-- SKU -->
                                <div class="tw-flex tw-gap-2 tw-items-center tw-w-40">
                                    <input v-model="sizeObj.sku" type="text"
                                        class="tw-input tw-w-full tw-border tw-rounded tw-p-2 focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                                        placeholder="Nhập mã SKU" />
                                    <button v-if="variant.sizes.length > 1" @click="removeSizeFromVariant(vIdx, sIdx)"
                                        class="tw-text-red-500 hover:tw-text-red-700 tw-p-2 focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <div v-if="formErrors.variants[vIdx]?.sizes[sIdx]?.sku"
                                        class="tw-text-red-500 tw-text-sm tw-mt-1">{{
                                            formErrors.variants[vIdx].sizes[sIdx].sku }}</div>
                                </div>
                                <!-- Ảnh phụ -->
                                <div v-if="sIdx === 0" class="tw-flex tw-items-center tw-gap-3"
                                    style="min-width:110px;">
                                    <input type="file" multiple :id="`variant-upload-${vIdx}`"
                                        @change="onVariantImagesChange($event, vIdx)" class="tw-hidden" />
                                    <label :for="`variant-upload-${vIdx}`"
                                        class="tw-inline-flex tw-items-center tw-bg-gray-100 hover:tw-bg-blue-500 tw-text-blue-600 hover:tw-text-white tw-font-medium tw-px-3 tw-py-1 tw-rounded tw-cursor-pointer tw-gap-1 tw-text-sm tw-transition"
                                        style="user-select: none; min-width: 90px; justify-content: center;">
                                        <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5-5m0 0l5 5m-5-5v12" />
                                        </svg>
                                        <span> Tải ảnh </span>
                                    </label>
                                    <div class="tw-flex tw-gap-1"
                                        v-if="variant.imagesPreview && variant.imagesPreview.length">
                                        <img v-for="(img, i) in variant.imagesPreview" :key="i" :src="img"
                                            class="tw-w-10 tw-border-gray-100 tw-border tw-h-10 tw-object-cover tw-rounded" />
                                    </div>
                                </div>
                                <div v-else style="min-width:110px;"></div>
                            </div>

                            <!-- Add size button -->
                            <div class="tw-mt-2">
                                <button @click="addSizeToVariant(vIdx)"
                                    class="tw-bg-blue-100 tw-text-blue-600 tw-rounded tw-px-3 tw-py-1 tw-text-sm hover:tw-bg-blue-200">
                                    <i class="fas fa-plus tw-mr-1"></i>
                                    Thêm kích thước
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tw-flex tw-justify-end tw-gap-4 tw-mt-6">
            <button @click="handleSubmit" :disabled="isSubmitting"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-primary-dark disabled:tw-opacity-50 disabled:tw-cursor-not-allowed">
                {{ isSubmitting ? 'Đang tạo...' : 'Tạo sản phẩm' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useProducts } from '~/composables/useProducts'
import { useCategoryStore } from '~/stores/useCategoryStore'
import { useBrandStore } from '~/stores/useBrandStore'
import CKEditor from '~/components/CKEditor.vue'
import Swal from 'sweetalert2'

const notyf = useNuxtApp().$notyf

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
})

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

const { createProduct } = useProducts()
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
                variant.sizes.forEach((sizeObj, sIdx) => {
                    const sizeErr = { size: '', price: '', sku: '' }
                    if (!sizeObj.size) {
                        sizeErr.size = 'Nhập kích thước'
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
    console.log('ValidateForm errors:', errors)
    console.log('ValidateForm data:', formData.value)
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
        notyf.success('Tạo sản phẩm thành công!')
        await navigateTo('/admin/products')
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
.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
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
</style>
