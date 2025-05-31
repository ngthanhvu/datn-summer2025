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
            <div class="tw-grid tw-grid-cols-2 tw-gap-6">
                <div class="tw-space-y-4">
                    <Form v-if="isDataLoaded" :fields="basicFields" :initial-data="formData"
                        @update:modelValue="updateFormData" @submit="handleSubmit" />
                    <div v-else class="tw-text-center tw-text-gray-500">Đang tải danh mục và thương hiệu...</div>
                </div>
                <div class="tw-space-y-4">
                    <Form :fields="imageFields" :initial-data="formData" @update:modelValue="updateFormData" />
                </div>
            </div>

            <div class="tw-w-full">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                    <h2 class="tw-text-xl tw-font-semibold">Biến thể sản phẩm</h2>
                    <div class="tw-flex tw-gap-2">
                        <button v-if="!showVariants" @click="showVariantsSection"
                            class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
                            <i class="fas fa-plus"></i>
                            Thêm biến thể
                        </button>
                        <button v-if="showVariants" @click="addVariant"
                            class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
                            <i class="fas fa-plus"></i>
                            Thêm biến thể
                        </button>
                    </div>
                </div>

                <div v-if="showVariants" class="tw-w-full">
                    <div class="tw-flex tw-gap-4 tw-overflow-x-auto tw-pb-4 tw-w-full">
                        <div v-for="(variant, index) in formData.variants" :key="index"
                            class="tw-bg-gray-50 tw-p-4 tw-rounded-lg tw-flex-1 tw-min-w-[300px]">
                            <div class="tw-flex tw-justify-between tw-mb-2">
                                <h3 class="tw-font-medium">Biến thể {{ index + 1 }}</h3>
                                <button @click="removeVariant(index)" class="tw-text-red-500 hover:tw-text-red-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <Form :fields="variantFields" :initial-data="variant"
                                @update:modelValue="(val) => updateVariant(val, index)" />
                        </div>
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
import Form from '~/components/admin/Form.vue'
import { useProducts } from '~/composables/useProducts'
import { useCategory } from '~/composables/useCategory'
import { useBrand } from '~/composables/useBrand'

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
        label: 'Giá',
        type: 'number',
        placeholder: 'Nhập giá sản phẩm',
        required: true,
        min: 0,
        step: 1000
    },
    {
        name: 'original_price',
        label: 'Giá gốc',
        type: 'number',
        placeholder: 'Nhập giá gốc',
        required: false,
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
        name: 'quantity',
        label: 'Số lượng',
        type: 'number',
        placeholder: 'Nhập số lượng',
        required: true,
        min: 0
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
        name: 'quantity',
        label: 'Số lượng',
        type: 'number',
        placeholder: 'Nhập số lượng',
        required: true,
        min: 0
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
    original_price: 0,
    discount_price: 0,
    quantity: 0,
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
            formData.value = {
                name: product.name || '',
                price: product.price || 0,
                original_price: product.original_price || 0,
                discount_price: product.discount_price || 0,
                quantity: product.quantity || 0,
                category: product.categories_id ? String(product.categories_id) : '',
                brand: product.brand_id ? String(product.brand_id) : '',
                description: product.description || '',
                status: !!product.is_active,
                mainImage: null,
                mainImagePreview: mainImage ? `${apiBaseUrl}/storage/${mainImage.image_path}` : null,
                additionalImages: [],
                additionalImagePreviews: additionalImages.map(img => `${apiBaseUrl}/storage/${img.image_path}`),
                variants: product.variants ? product.variants.map(variant => ({
                    color: variant.color || '',
                    size: variant.size || '',
                    price: variant.price || 0,
                    quantity: variant.quantity || 0,
                    sku: variant.sku || ''
                })) : []
            }
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
        alert('Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại sau.')
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

const handleSubmit = async () => {
    try {
        if (!formData.value.mainImage && !formData.value.mainImagePreview) {
            alert('Vui lòng chọn ảnh chính cho sản phẩm')
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
            })
        }

        await updateProduct(route.params.id, productData)
        await navigateTo('/admin/products')
    } catch (error) {
        console.error('Error updating product:', error)
        alert('Có lỗi khi cập nhật sản phẩm')
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
        sku: ''
    })
}

const removeVariant = (index) => {
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