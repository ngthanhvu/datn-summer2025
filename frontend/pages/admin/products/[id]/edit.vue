<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Chỉnh sửa sản phẩm</h1>
                <p class="tw-text-gray-600">Cập nhật thông tin sản phẩm</p>
            </div>
            <NuxtLink to="/admin/products"
                class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </NuxtLink>
        </div>
        <div class="tw-flex tw-flex-col tw-gap-6">
            <div class="tw-grid tw-grid-cols-5 tw-gap-6">
                <div
                    class="tw-col-span-2 tw-space-y-4 tw-p-6 tw-rounded-[10px] tw-border tw-border-gray-150 tw-bg-white">
                    <div v-if="isDataLoaded">
                        <!-- Tên sản phẩm -->
                        <div class="tw-mb-4">
                            <label class="tw-block tw-font-medium">Tên sản phẩm</label>
                            <input v-model="formData.name" type="text"
                                class="tw-input tw-w-full tw-border tw-rounded tw-p-2"
                                placeholder="Nhập tên sản phẩm" />
                            <div v-if="formErrors.name" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ formErrors.name }}
                            </div>
                        </div>
                        <!-- Giá -->
                        <div class="tw-mb-4">
                            <label class="tw-block tw-font-medium">Giá bán</label>
                            <input v-model="formData.price" type="number" min="0" step="1000"
                                class="tw-input tw-w-full tw-border tw-rounded tw-p-2"
                                placeholder="Nhập giá sản phẩm" />
                            <div v-if="formErrors.price" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ formErrors.price
                            }}</div>
                        </div>
                        <!-- Giá gốc -->
                        <div class="tw-mb-4">
                            <label class="tw-block tw-font-medium">Giá gốc</label>
                            <input v-model="formData.original_price" type="number" min="0" step="1000"
                                class="tw-input tw-w-full tw-border tw-rounded tw-p-2" placeholder="Nhập giá gốc" />
                        </div>
                        <!-- Giá khuyến mãi -->
                        <div class="tw-mb-4">
                            <label class="tw-block tw-font-medium">Giá khuyến mãi</label>
                            <input v-model="formData.discount_price" type="number" min="0" step="1000"
                                class="tw-input tw-w-full tw-border tw-rounded tw-p-2"
                                placeholder="Nhập giá khuyến mãi" />
                            <div v-if="formErrors.discount_price" class="tw-text-red-500 tw-text-sm tw-mt-1">{{
                                formErrors.discount_price }}</div>
                        </div>
                        <!-- Danh mục -->
                        <div class="tw-mb-4">
                            <label class="tw-block tw-font-medium">Danh mục</label>
                            <select v-model="formData.category" class="tw-input tw-w-full tw-border tw-rounded tw-p-2">
                                <option value="">Chọn danh mục</option>
                                <option v-for="opt in categoryOptions" :key="opt.value" :value="opt.value">{{ opt.label
                                    }}</option>
                            </select>
                            <div v-if="formErrors.category" class="tw-text-red-500 tw-text-sm tw-mt-1">{{
                                formErrors.category }}</div>
                        </div>
                        <!-- Thương hiệu -->
                        <div class="tw-mb-4">
                            <label class="tw-block tw-font-medium">Thương hiệu</label>
                            <select v-model="formData.brand" class="tw-input tw-w-full tw-border tw-rounded tw-p-2">
                                <option value="">Chọn thương hiệu</option>
                                <option v-for="opt in brandOptions" :key="opt.value" :value="opt.value">{{ opt.label }}
                                </option>
                            </select>
                            <div v-if="formErrors.brand" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ formErrors.brand
                            }}</div>
                        </div>
                        <!-- Mô tả -->
                        <div class="tw-mb-4">
                            <label class="tw-block tw-font-medium">Mô tả</label>
                            <QuillEditor v-model:content="formData.description" contentType="html"
                                class="tw-input tw-w-full tw-border tw-rounded tw-p-2" placeholder="Nhập mô tả sản phẩm"
                                style="height: 140px;" />
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
                        <label class="tw-block tw-font-medium">Ảnh chính</label>
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
                        <label class="tw-block tw-font-medium">Ảnh phụ</label>
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
                </div>
            </div>
            <!-- Biến thể sản phẩm -->
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
                                    class="tw-input tw-w-40 tw-border tw-rounded tw-p-2" placeholder="Nhập tên màu" />
                                <div v-if="formErrors.variants[vIdx]?.color" class="tw-text-red-500 tw-text-sm tw-mt-1">
                                    {{ formErrors.variants[vIdx].color }}
                                </div>
                            </div>
                            <!-- Kích thước -->
                            <div>
                                <input v-model="sizeObj.size" type="text"
                                    class="tw-input tw-w-40 tw-border tw-rounded tw-p-2"
                                    placeholder="Nhập kích thước" />
                                <div v-if="formErrors.variants[vIdx]?.sizes[sIdx]?.size"
                                    class="tw-text-red-500 tw-text-sm tw-mt-1">{{
                                        formErrors.variants[vIdx].sizes[sIdx].size }}</div>
                            </div>
                            <!-- Giá -->
                            <div>
                                <input v-model="sizeObj.price" type="number" min="0" step="1000"
                                    class="tw-input tw-w-40 tw-border tw-rounded tw-p-2" placeholder="Nhập giá" />
                                <div v-if="formErrors.variants[vIdx]?.sizes[sIdx]?.price"
                                    class="tw-text-red-500 tw-text-sm tw-mt-1">{{
                                        formErrors.variants[vIdx].sizes[sIdx].price }}</div>
                            </div>
                            <!-- SKU -->
                            <div class="tw-flex tw-gap-2 tw-items-center tw-w-40">
                                <input v-model="sizeObj.sku" type="text"
                                    class="tw-input tw-w-full tw-border tw-rounded tw-p-2" placeholder="Nhập mã SKU" />
                                <button v-if="variant.sizes.length > 1" @click="removeSizeFromVariant(vIdx, sIdx)"
                                    class="tw-text-red-500 hover:tw-text-red-700 tw-p-2">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <div v-if="formErrors.variants[vIdx]?.sizes[sIdx]?.sku"
                                    class="tw-text-red-500 tw-text-sm tw-mt-1">{{
                                        formErrors.variants[vIdx].sizes[sIdx].sku }}</div>
                            </div>
                            <!-- Ảnh phụ -->
                            <div v-if="sIdx === 0" class="tw-flex tw-items-center tw-gap-3" style="min-width:110px;">
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
            <div class="tw-flex tw-justify-end tw-gap-4 tw-mt-6">
                <NuxtLink to="/admin/products"
                    class="tw-px-4 tw-py-2 tw-border tw-rounded tw-text-gray-600 hover:tw-bg-gray-50">
                    Hủy
                </NuxtLink>
                <button @click="handleSubmit"
                    class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-primary-dark">
                    Lưu thay đổi
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
useHead({
    title: 'Chỉnh sửa sản phẩm'
})

definePageMeta({
    layout: 'admin',
    middleware: 'auth'
})

import { ref, onMounted } from 'vue'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import { useProducts } from '~/composables/useProducts'
import { useCategory } from '~/composables/useCategory'
import { useBrand } from '~/composables/useBrand'
const notyf = useNuxtApp().$notyf

const route = useRoute()
const isDataLoaded = ref(false)
const showVariants = ref(false)
const config = useRuntimeConfig()
const apiBaseUrl = config.public.apiBaseUrl

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

const variantFields = [
    {
        name: 'color',
        label: 'Màu sắc',
        type: 'text',
        placeholder: 'Nhập màu sắc',
        required: false
    },
    {
        name: 'size',
        label: 'Kích thước',
        type: 'text',
        placeholder: 'Nhập kích thước',
        required: false
    },
    {
        name: 'price',
        label: 'Giá',
        type: 'number',
        placeholder: 'Nhập giá biến thể',
        required: true,
        min: 0,
        step: 1000
    },
    {
        name: 'sku',
        label: 'SKU',
        type: 'text',
        placeholder: 'Nhập mã SKU',
        required: false
    }
]

const imageFields = [
    {
        name: 'mainImage',
        label: 'Ảnh chính',
        type: 'mainImage',
        required: true,
        description: 'Chọn ảnh chính cho sản phẩm'
    },
    {
        name: 'additionalImages',
        label: 'Ảnh phụ',
        type: 'additionalImages',
        description: 'Chọn thêm các ảnh khác cho sản phẩm'
    }
]

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

const { getProductById, updateProduct } = useProducts()
const { getCategories } = useCategory()
const { getBrands } = useBrand()

onMounted(async () => {
    try {
        console.log('Bắt đầu tải dữ liệu...')

        // Load product data
        const product = await getProductById(route.params.id)
        console.log('Dữ liệu sản phẩm:', product)

        // Load categories and brands
        const [categories, brands] = await Promise.all([
            getCategories(),
            getBrands()
        ])
        console.log('Danh mục:', categories)
        console.log('Thương hiệu:', brands)

        // Update category options
        if (categories && Array.isArray(categories)) {
            const catOptions = categories.map(cat => ({ value: String(cat.id), label: cat.name }))
            const categoryField = basicFields.value.find(f => f.name === 'category')
            if (categoryField) {
                categoryField.options = catOptions
            }
        }

        // Update brand options
        if (brands && Array.isArray(brands)) {
            const brandOptions = brands.map(brand => ({ value: String(brand.id), label: brand.name }))
            const brandField = basicFields.value.find(f => f.name === 'brand')
            if (brandField) {
                brandField.options = brandOptions
            }
        }

        // Update product data
        if (product) {
            console.log('Dữ liệu sản phẩm gốc:', product)
            const mainImage = product.images.find(img => img.is_main)
            const additionalImages = product.images.filter(img => !img.is_main)

            // Initialize formData with product data
            formData.value.name = product.name || ''
            formData.value.price = product.price || 0
            formData.value.original_price = product.original_price || 0
            formData.value.discount_price = product.discount_price || 0
            formData.value.quantity = product.quantity || 0
            formData.value.category = product.categories_id ? String(product.categories_id) : ''
            formData.value.brand = product.brand_id ? String(product.brand_id) : ''
            formData.value.description = product.description || ''
            formData.value.status = !!product.is_active
            formData.value.mainImage = null
            formData.value.mainImagePreview = mainImage ? `${apiBaseUrl}/storage/${mainImage.image_path}` : null
            formData.value.additionalImages = []
            formData.value.additionalImagePreviews = additionalImages.map(img => `${apiBaseUrl}/storage/${img.image_path}`)
            formData.value.variants = product.variants ? product.variants.map(variant => ({
                colorName: variant.color || '',
                sizes: [{
                    size: variant.size || '',
                    price: variant.price || 0,
                    sku: variant.sku || ''
                }],
                images: variant.images ? variant.images.map(img => img.image_path) : [],
                imagesPreview: variant.images ? variant.images.map(img => `${apiBaseUrl}/storage/${img.image_path}`) : []
            })) : []
            console.log('Dữ liệu form sau khi xử lý:', formData.value)
            if (product.variants && product.variants.length > 0) {
                showVariants.value = true
            }
        }

        // Mark data as loaded
        isDataLoaded.value = true
        console.log('Đã tải xong dữ liệu')
    } catch (err) {
        console.error('Lỗi khi tải dữ liệu:', err)
        notyf.error('Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại sau.')
        isDataLoaded.value = true
    }
})

const updateFormData = (newData) => {
    // Merge new data without triggering deep reactivity issues
    Object.assign(formData.value, newData)
}

const updateVariant = (val, index) => {
    const updatedVariants = [...formData.value.variants]
    updatedVariants[index] = { ...val }
    formData.value = { ...formData.value, variants: updatedVariants }
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

const handleSubmit = async () => {
    try {
        if (!formData.value.mainImage && !formData.value.mainImagePreview) {
            notyf.error('Vui lòng chọn ảnh chính cho sản phẩm')
            return
        }
        const productData = new FormData()
        productData.append('name', formData.value.name || '')
        productData.append('description', formData.value.description || '')
        productData.append('price', String(formData.value.price || 0))
        productData.append('original_price', String(formData.value.original_price || 0))
        productData.append('discount_price', String(formData.value.discount_price || 0))
        productData.append('quantity', String(formData.value.quantity || 0))
        productData.append('is_active', formData.value.status ? '1' : '0')
        productData.append('categories_id', String(formData.value.category || ''))
        productData.append('brand_id', String(formData.value.brand || ''))
        if (formData.value.mainImage instanceof File) {
            productData.append('is_main', formData.value.mainImage)
        }
        formData.value.additionalImages.forEach((img) => {
            if (img instanceof File) {
                productData.append('image_path[]', img)
            }
        })
        if (formData.value.variants.length > 0) {
            formData.value.variants.forEach((variant, idx) => {
                productData.append(`variants[${idx}][color]`, variant.color || '')
                productData.append(`variants[${idx}][size]`, variant.size || '')
                productData.append(`variants[${idx}][price]`, String(variant.price || 0))
                productData.append(`variants[${idx}][quantity]`, String(variant.quantity || 0))
                productData.append(`variants[${idx}][sku]`, variant.sku || '')
                if (variant.images && variant.images.length > 0) {
                    variant.images.forEach(imgFile => {
                        productData.append(`variants[${idx}][images][]`, imgFile)
                    })
                }
            })
        }
        await updateProduct(route.params.id, productData)
        await navigateTo('/admin/products')
    } catch (error) {
        console.error('Error updating product:', error)
        notyf.error('Có lỗi khi cập nhật sản phẩm')
    }
}

const showVariantsSection = () => {
    showVariants.value = true
    addVariant()
}

const addVariant = () => {
    formData.value.variants.push({
        color: '',
        size: '',
        price: 0,
        quantity: 0,
        sku: '',
        images: [],
        imagesPreview: []
    })
}

const removeVariantColor = (index) => {
    formData.value.variants.splice(index, 1)
    if (formData.value.variants.length === 0) {
        showVariants.value = false
    }
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