<template>
    <div class="flex flex-col gap-6">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 lg:gap-6">
            <!-- Basic Info Section -->
            <div class="lg:col-span-2 space-y-4 p-4 lg:p-6 rounded-[10px] border border-gray-300 bg-white">
                <div v-if="isDataLoaded">
                    <!-- Tên sản phẩm -->
                    <div class="mb-4">
                        <label class="block font-medium">Tên sản phẩm <span class="text-red-500">*</span></label>
                        <input v-model="formData.name" type="text"
                            class="input w-full border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            placeholder="Nhập tên sản phẩm" />
                        <div v-if="formErrors.name" class="text-red-500 text-sm mt-1">{{ formErrors.name }}</div>
                    </div>

                    <!-- Giá -->
                    <div class="mb-4">
                        <label class="block font-medium">Giá bán <span class="text-red-500">*</span></label>
                        <input v-model="formData.price" type="number" min="0" step="1000"
                            class="input w-full border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            placeholder="Nhập giá sản phẩm" />
                        <div v-if="formErrors.price" class="text-red-500 text-sm mt-1">{{ formErrors.price }}</div>
                    </div>

                    <!-- Giá khuyến mãi -->
                    <div class="mb-4">
                        <label class="block font-medium">Giá khuyến mãi</label>
                        <input v-model="formData.discount_price" type="number" min="0" step="1000"
                            class="input w-full border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                            placeholder="Nhập giá khuyến mãi" />
                        <div v-if="formErrors.discount_price" class="text-red-500 text-sm mt-1">{{
                            formErrors.discount_price }}</div>
                    </div>

                    <!-- Danh mục -->
                    <div class="mb-4">
                        <label class="block font-medium">Danh mục <span class="text-red-500">*</span></label>
                        <select v-model="formData.category"
                            class="input w-full border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100">
                            <option value="">Chọn danh mục</option>
                            <option v-for="opt in basicFields.find(f => f.name === 'category').options" :key="opt.value"
                                :value="opt.value">{{ opt.label }}</option>
                        </select>
                        <div v-if="formErrors.category" class="text-red-500 text-sm mt-1">{{ formErrors.category }}
                        </div>
                    </div>

                    <!-- Thương hiệu -->
                    <div class="mb-4">
                        <label class="block font-medium">Thương hiệu <span class="text-red-500">*</span></label>
                        <select v-model="formData.brand"
                            class="input w-full border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100">
                            <option value="">Chọn thương hiệu</option>
                            <option v-for="opt in basicFields.find(f => f.name === 'brand').options" :key="opt.value"
                                :value="opt.value">{{ opt.label }}</option>
                        </select>
                        <div v-if="formErrors.brand" class="text-red-500 text-sm mt-1">{{ formErrors.brand }}</div>
                    </div>

                    <!-- Mô tả -->
                    <div class="mb-4">
                        <label class="block font-medium">Mô tả <span class="text-red-500">*</span></label>
                        <CKEditor v-model="formData.description" />
                        <div v-if="formErrors.description" class="text-red-500 text-sm mt-1">{{ formErrors.description
                            }}</div>
                    </div>

                    <!-- Trạng thái -->
                    <div class="flex items-center gap-2">
                        <div class="toggle">
                            <input type="checkbox" id="status" v-model="formData.status" />
                            <label for="status"></label>
                        </div>
                        <span>{{ formData.status ? "Hiển thị" : "Ẩn" }}</span>
                    </div>
                </div>
                <div v-else class="text-center text-gray-500">Đang tải danh mục và thương hiệu...</div>
            </div>

            <!-- Images and Variants Section -->
            <div class="lg:col-span-3 space-y-4">
                <!-- Ảnh chính -->
                <div class="mb-4 border border-gray-300 p-4 lg:p-5 rounded-[10px] bg-white">
                    <label class="block font-medium">Ảnh chính <span class="text-red-500">*</span></label>
                    <label
                        class="flex flex-col items-center justify-center w-full h-32 lg:h-40 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer hover:bg-gray-50">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-cloud-upload-alt text-2xl lg:text-3xl text-gray-400 mb-2"></i>
                            <span class="text-gray-500 font-semibold text-sm lg:text-base">Click để tải ảnh lên</span>
                            <span class="text-xs text-gray-400">PNG, JPG, GIF (tối đa 2MB)</span>
                        </div>
                        <input type="file" accept="image/*" class="hidden" @change="onMainImageChange" />
                    </label>

                    <div v-if="formErrors.mainImage" class="text-red-500 text-sm mt-1">{{ formErrors.mainImage }}</div>

                    <div v-if="formData.mainImagePreview" class="relative w-32 h-32 lg:w-48 lg:h-48 mt-4">
                        <img :src="formData.mainImagePreview" class="w-full h-full object-cover rounded-lg shadow" />
                        <button @click="removeMainImage"
                            class="absolute top-2 right-2 p-2 rounded-full bg-white shadow hover:bg-gray-100">
                            <i class="fas fa-times text-red-500"></i>
                        </button>
                    </div>
                </div>

                <!-- Ảnh phụ -->
                <div class="mb-4 border border-gray-300 p-4 lg:p-5 rounded-[10px] bg-white">
                    <label class="block font-medium">Ảnh phụ</label>
                    <label
                        class="flex flex-col items-center justify-center w-full h-32 lg:h-40 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer hover:bg-gray-50">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-cloud-upload-alt text-2xl lg:text-3xl text-gray-400 mb-2"></i>
                            <span class="text-gray-500 font-semibold text-sm lg:text-base">Click để tải ảnh lên</span>
                        </div>
                        <input type="file" accept="image/*" multiple class="hidden"
                            @change="onAdditionalImagesChange" />
                    </label>

                    <div v-if="formErrors.additionalImages" class="text-red-500 text-sm mt-1">{{
                        formErrors.additionalImages }}</div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 lg:gap-4 mt-4"
                        v-if="formData.additionalImagePreviews.length">
                        <div v-for="(img, idx) in formData.additionalImagePreviews" :key="idx" class="relative group">
                            <img :src="img" class="w-full h-24 lg:h-32 object-cover rounded-lg shadow" />
                            <button @click="removeAdditionalImage(idx)"
                                class="absolute top-2 right-2 p-2 rounded-full bg-white shadow opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fas fa-times text-red-500"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Variants Section -->
                <div class="variants-section">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
                        <h2 class="text-lg lg:text-xl font-semibold">Biến thể sản phẩm</h2>
                        <div class="flex gap-2">
                            <button v-if="!showVariants" @click="showVariantsSection"
                                class="bg-gray-100 text-gray-600 rounded px-3 lg:px-4 py-2 flex items-center gap-2 hover:bg-gray-200 text-sm lg:text-base">
                                <i class="fas fa-plus"></i>
                                <span class="hidden sm:inline">Thêm biến thể</span>
                                <span class="sm:hidden">Thêm</span>
                            </button>
                            <button v-if="showVariants" @click="addVariant"
                                class="bg-white border border-gray-300 text-gray-600 rounded px-3 lg:px-4 py-2 flex items-center gap-2 hover:bg-gray-200 cursor-pointer text-sm lg:text-base">
                                <i class="fas fa-plus"></i>
                                <span class="hidden sm:inline">Thêm biến thể</span>
                                <span class="sm:hidden">Thêm</span>
                            </button>
                        </div>
                    </div>

                    <div v-if="showVariants" class="w-full">
                        <div class="space-y-4">
                            <div v-for="(variant, vIdx) in formData.variants" :key="vIdx"
                                class="bg-white border border-gray-300 p-3 lg:p-4 rounded-lg">
                                <div class="flex justify-between mb-4">
                                    <h3 class="font-medium">Biến thể {{ vIdx + 1 }}</h3>
                                    <button @click="removeVariantColor(vIdx)" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <!-- Mobile Variant Layout -->
                                <div class="lg:hidden">
                                    <div v-for="(sizeObj, sIdx) in variant.sizes" :key="sIdx"
                                        class="border border-gray-200 rounded-lg p-3 mb-3">
                                        <div class="space-y-3">
                                            <!-- Màu sắc -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Màu
                                                    sắc</label>
                                                <input v-model="variant.colorName" type="text"
                                                    class="input w-full border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                                    placeholder="Nhập tên màu" />
                                                <div v-if="formErrors.variants[vIdx]?.color"
                                                    class="text-red-500 text-sm mt-1">
                                                    {{ formErrors.variants[vIdx].color }}
                                                </div>
                                            </div>

                                            <!-- Kích thước -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Kích
                                                    thước</label>
                                                <input v-model="sizeObj.size" type="text"
                                                    class="input w-full border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                                    placeholder="Nhập kích thước" />
                                                <div v-if="formErrors.variants[vIdx]?.sizes[sIdx]?.size"
                                                    class="text-red-500 text-sm mt-1">{{
                                                        formErrors.variants[vIdx].sizes[sIdx].size }}</div>
                                            </div>

                                            <!-- Giá -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Giá</label>
                                                <input v-model="sizeObj.price" type="number" min="0" step="1000"
                                                    class="input w-full border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                                    placeholder="Nhập giá" />
                                                <div v-if="formErrors.variants[vIdx]?.sizes[sIdx]?.price"
                                                    class="text-red-500 text-sm mt-1">{{
                                                        formErrors.variants[vIdx].sizes[sIdx].price }}</div>
                                            </div>

                                            <!-- SKU -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                                                <div class="flex gap-2">
                                                    <input v-model="sizeObj.sku" type="text"
                                                        class="input flex-1 border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                                        placeholder="Nhập mã SKU" />
                                                    <button v-if="variant.sizes.length > 1"
                                                        @click="removeSizeFromVariant(vIdx, sIdx)"
                                                        class="text-red-500 hover:text-red-700 p-2 border border-gray-300 rounded">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <div v-if="formErrors.variants[vIdx]?.sizes[sIdx]?.sku"
                                                    class="text-red-500 text-sm mt-1">{{
                                                        formErrors.variants[vIdx].sizes[sIdx].sku }}</div>
                                            </div>

                                            <!-- Ảnh phụ -->
                                            <div v-if="sIdx === 0">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh
                                                    phụ</label>
                                                <input type="file" multiple :id="`variant-upload-${vIdx}`"
                                                    @change="onVariantImagesChange($event, vIdx)" class="hidden" />
                                                <label :for="`variant-upload-${vIdx}`"
                                                    class="inline-flex items-center bg-gray-100 hover:bg-blue-500 text-blue-600 hover:text-white font-medium px-3 py-2 rounded cursor-pointer gap-1 text-sm transition w-full justify-center">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5-5m0 0l5 5m-5-5v12" />
                                                    </svg>
                                                    <span>Tải ảnh</span>
                                                </label>
                                                <div class="flex gap-1 mt-2"
                                                    v-if="variant.imagesPreview && variant.imagesPreview.length">
                                                    <img v-for="(img, i) in variant.imagesPreview" :key="i" :src="img"
                                                        class="w-10 h-10 object-cover rounded border border-gray-200" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Desktop Variant Layout -->
                                <div class="hidden lg:block">
                                    <!-- Header row for labels -->
                                    <div class="grid grid-cols-5 gap-4 mb-2">
                                        <div class="font-medium">Màu sắc</div>
                                        <div class="font-medium">Kích thước</div>
                                        <div class="font-medium">Giá</div>
                                        <div class="font-medium">SKU</div>
                                        <div class="font-medium">Ảnh phụ</div>
                                    </div>

                                    <!-- Variant rows -->
                                    <div v-for="(sizeObj, sIdx) in variant.sizes" :key="sIdx"
                                        class="grid grid-cols-5 gap-2 items-center mb-2 p-2 bg-white rounded border-b border-gray-100 last:border-b-0">
                                        <!-- Màu sắc -->
                                        <div>
                                            <input v-model="variant.colorName" type="text"
                                                class="input w-40 border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                                placeholder="Nhập tên màu" />
                                            <div v-if="formErrors.variants[vIdx]?.color"
                                                class="text-red-500 text-sm mt-1">
                                                {{ formErrors.variants[vIdx].color }}
                                            </div>
                                        </div>
                                        <!-- Kích thước -->
                                        <div>
                                            <input v-model="sizeObj.size" type="text"
                                                class="input w-40 border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                                placeholder="Nhập kích thước" />
                                            <div v-if="formErrors.variants[vIdx]?.sizes[sIdx]?.size"
                                                class="text-red-500 text-sm mt-1">{{
                                                    formErrors.variants[vIdx].sizes[sIdx].size }}</div>
                                        </div>
                                        <!-- Giá -->
                                        <div>
                                            <input v-model="sizeObj.price" type="number" min="0" step="1000"
                                                class="input w-40 border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                                placeholder="Nhập giá" />
                                            <div v-if="formErrors.variants[vIdx]?.sizes[sIdx]?.price"
                                                class="text-red-500 text-sm mt-1">{{
                                                    formErrors.variants[vIdx].sizes[sIdx].price }}</div>
                                        </div>
                                        <!-- SKU -->
                                        <div class="flex gap-2 items-center w-40">
                                            <input v-model="sizeObj.sku" type="text"
                                                class="input w-full border border-gray-300 rounded p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                                placeholder="Nhập mã SKU" />
                                            <button v-if="variant.sizes.length > 1"
                                                @click="removeSizeFromVariant(vIdx, sIdx)"
                                                class="text-red-500 hover:text-red-700 p-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <div v-if="formErrors.variants[vIdx]?.sizes[sIdx]?.sku"
                                                class="text-red-500 text-sm mt-1">{{
                                                    formErrors.variants[vIdx].sizes[sIdx].sku }}</div>
                                        </div>
                                        <!-- Ảnh phụ -->
                                        <div v-if="sIdx === 0" class="flex items-center gap-3" style="min-width:110px;">
                                            <input type="file" multiple :id="`variant-upload-${vIdx}`"
                                                @change="onVariantImagesChange($event, vIdx)" class="hidden" />
                                            <label :for="`variant-upload-${vIdx}`"
                                                class="inline-flex items-center bg-gray-100 hover:bg-blue-500 text-blue-600 hover:text-white font-medium px-3 py-1 rounded cursor-pointer gap-1 text-sm transition"
                                                style="user-select: none; min-width: 90px; justify-content: center;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5-5m0 0l5 5m-5-5v12" />
                                                </svg>
                                                <span> Tải ảnh </span>
                                            </label>
                                            <div class="flex gap-1"
                                                v-if="variant.imagesPreview && variant.imagesPreview.length">
                                                <img v-for="(img, i) in variant.imagesPreview" :key="i" :src="img"
                                                    class="w-10 border-gray-100 border h-10 object-cover rounded" />
                                            </div>
                                        </div>
                                        <div v-else style="min-width:110px;"></div>
                                    </div>
                                </div>

                                <!-- Add size button -->
                                <div class="mt-2">
                                    <button @click="addSizeToVariant(vIdx)"
                                        class="bg-blue-100 text-blue-600 rounded px-3 py-1 text-sm hover:bg-blue-200">
                                        <i class="fas fa-plus mr-1"></i>
                                        Thêm kích thước
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-4 mt-6">
            <button @click="handleSubmit" :disabled="isSubmitting"
                class="bg-primary text-white rounded px-4 py-2 hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer w-full sm:w-auto">
                {{ isSubmitting ? "Đang cập nhật..." : "Cập nhật sản phẩm" }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import CKEditor from "../../CKEditor.vue";
import { useProducts } from "../../../composable/useProducts";
import { useCategoryStore } from "../../../stores/categories";
import { useBrandStore } from "../../../stores/brands";
import { push } from "notivue";

const router = useRouter();
const route = useRoute();
const { createProduct, updateProduct, getProductById } = useProducts();

const categoryStore = useCategoryStore();
const brandStore = useBrandStore();

const isEditMode = computed(() => !!route.params.id);

const isDataLoaded = ref(false);
const isSubmitting = ref(false);
const showVariants = ref(false);

const basicFields = ref([
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
    }
]);

const formData = ref({
    name: "",
    price: 0,
    discount_price: 0,
    category: "",
    brand: "",
    description: "",
    status: true,
    mainImage: null,
    mainImagePreview: null,
    additionalImages: [],
    additionalImagePreviews: [],
    variants: []
});

const formErrors = ref({
    name: "",
    price: "",
    discount_price: "",
    category: "",
    brand: "",
    description: "",
    mainImage: "",
    additionalImages: "",
    variants: []
});

onMounted(async () => {
    try {
        console.log('Starting onMounted...');

        // Load categories and brands first
        if (!categoryStore.categories.length) {
            console.log('Loading categories...');
            await categoryStore.fetchCategories();
        }
        if (!brandStore.brands.length) {
            console.log('Loading brands...');
            await brandStore.fetchBrands();
        }

        console.log('Categories loaded:', categoryStore.categories);
        console.log('Brands loaded:', brandStore.brands);

        // Set up options for dropdowns
        const catOptions = categoryStore.categories.map(cat => ({ value: String(cat.id), label: cat.name }));
        const categoryField = basicFields.value.find(f => f.name === 'category');
        if (categoryField) {
            categoryField.options = catOptions;
        }

        const brandOptions = brandStore.brands.map(brand => ({ value: String(brand.id), label: brand.name }));
        const brandField = basicFields.value.find(f => f.name === 'brand');
        if (brandField) {
            brandField.options = brandOptions;
        }

        console.log('Category options:', catOptions);
        console.log('Brand options:', brandOptions);

        // Now load product data if in edit mode
        if (isEditMode.value) {
            try {
                console.log('Loading product with ID:', route.params.id);
                const product = await getProductById(route.params.id);
                console.log('Product data:', product);
                console.log('Product category_id:', product.category_id);
                console.log('Product categories_id:', product.categories_id);
                console.log('Product brand_id:', product.brand_id);
                console.log('Product variants:', product.variants);

                formData.value = {
                    name: product.name || '',
                    price: product.price || 0,
                    discount_price: product.discount_price || 0,
                    category: product.category_id ? String(product.category_id) : (product.categories_id ? String(product.categories_id) : ''),
                    brand: product.brand_id ? String(product.brand_id) : '',
                    description: product.description || '',
                    status: !!product.is_active,
                    mainImage: null,
                    mainImagePreview: product.main_image_url || product.images?.[0]?.image_path || null,
                    additionalImages: [],
                    additionalImagePreviews: product.additional_images || product.images?.slice(1)?.map(img => img.image_path) || [],
                    variants: product.variants || []
                };

                // Add base URL to images if they don't have full URL
                const apiBaseUrl = import.meta.env.VITE_API_BASE_URL;
                if (formData.value.mainImagePreview && !formData.value.mainImagePreview.startsWith('http')) {
                    formData.value.mainImagePreview = `${apiBaseUrl}${formData.value.mainImagePreview}`;
                }
                if (formData.value.additionalImagePreviews.length > 0) {
                    formData.value.additionalImagePreviews = formData.value.additionalImagePreviews.map(img => {
                        if (img && !img.startsWith('http')) {
                            return `${apiBaseUrl}${img}`;
                        }
                        return img;
                    });
                }

                // Show variants section if product has variants
                if (product.variants && product.variants.length > 0) {
                    showVariants.value = true;

                    // Map variants data to the correct structure
                    formData.value.variants = product.variants.map(variant => ({
                        colorName: variant.color || '',
                        sizes: [{
                            size: variant.size || '',
                            price: variant.price || 0,
                            sku: variant.sku || ''
                        }],
                        images: [],
                        imagesPreview: []
                    }));

                    // Initialize form errors for variants
                    formErrors.value.variants = formData.value.variants.map(() => ({
                        color: '',
                        sizes: [{
                            size: '',
                            price: '',
                            sku: ''
                        }]
                    }));
                }

                console.log('Form data after mapping:', formData.value);
                console.log('Category selected:', formData.value.category);
                console.log('Brand selected:', formData.value.brand);
                console.log('Main image preview:', formData.value.mainImagePreview);
                console.log('Additional images:', formData.value.additionalImagePreviews);
                console.log('Variants:', formData.value.variants);
            } catch (err) {
                console.error('Error loading product:', err);
                push.error("Không tìm thấy sản phẩm");
                router.push("/admin/products");
            }
        }

        isDataLoaded.value = true;
        console.log('onMounted completed');
    } catch (err) {
        console.error('Không thể tải danh mục/thương hiệu', err);
        push.error("Có lỗi khi tải dữ liệu");
    }
});

const validateImage = (file) => {
    const validTypes = ['image/png', 'image/jpeg', 'image/gif'];
    const maxSize = 2 * 1024 * 1024; // 2MB
    if (!validTypes.includes(file.type)) {
        return 'Định dạng ảnh không hợp lệ. Chỉ chấp nhận PNG, JPG, GIF.';
    }
    if (file.size > maxSize) {
        return 'Kích thước ảnh vượt quá 2MB.';
    }
    return '';
};

const validateForm = () => {
    let hasError = false;
    const errors = {
        name: "",
        price: "",
        discount_price: "",
        category: "",
        brand: "",
        description: "",
        mainImage: "",
        additionalImages: "",
        variants: formData.value.variants.map(() => ({
            color: "",
            sizes: []
        }))
    };

    if (!formData.value.name) {
        errors.name = "Vui lòng nhập tên sản phẩm";
        hasError = true;
    }
    if (!formData.value.price || formData.value.price <= 0) {
        errors.price = "Vui lòng nhập giá hợp lệ";
        hasError = true;
    }
    if (!formData.value.category) {
        errors.category = "Vui lòng chọn danh mục";
        hasError = true;
    }
    if (!formData.value.brand) {
        errors.brand = "Vui lòng chọn thương hiệu";
        hasError = true;
    }

    const descriptionText = formData.value.description
        ?.replace(/<(.|\n)*?>/g, "")
        .trim();

    if (!descriptionText) {
        errors.description = "Vui lòng nhập mô tả sản phẩm";
        hasError = true;
    }

    // Chỉ validate ảnh chính khi tạo mới hoặc khi có ảnh mới được chọn
    if (!isEditMode.value && !formData.value.mainImage && !formData.value.mainImagePreview) {
        errors.mainImage = "Vui lòng chọn ảnh chính";
        hasError = true;
    } else if (formData.value.mainImage) {
        const mainImageError = validateImage(formData.value.mainImage);
        if (mainImageError) {
            errors.mainImage = mainImageError;
            hasError = true;
        }
    }

    if (formData.value.additionalImages.length > 0) {
        formData.value.additionalImages.forEach((file, idx) => {
            const error = validateImage(file);
            if (error) {
                errors.additionalImages = error;
                hasError = true;
            }
        });
    }

    if (showVariants.value && formData.value.variants.length > 0) {
        formData.value.variants.forEach((variant, vIdx) => {
            if (!variant.colorName) {
                errors.variants[vIdx].color = "Vui lòng nhập tên màu sắc";
                hasError = true;
            }
            if (!variant.sizes || variant.sizes.length === 0) {
                errors.variants[vIdx].sizes = [{ size: "Thêm ít nhất 1 size" }];
                hasError = true;
            } else {
                errors.variants[vIdx].sizes = [];
                variant.sizes.forEach((sizeObj, sIdx) => {
                    const sizeErr = { size: "", price: "", sku: "" };
                    if (!sizeObj.size) {
                        sizeErr.size = "Nhập kích thước";
                        hasError = true;
                    }
                    if (!sizeObj.price || sizeObj.price <= 0) {
                        sizeErr.price = "Nhập giá hợp lệ";
                        hasError = true;
                    }
                    if (!sizeObj.sku) {
                        sizeErr.sku = "Nhập mã SKU";
                        hasError = true;
                    }
                    errors.variants[vIdx].sizes.push(sizeErr);
                });
            }
        });
    }

    formErrors.value = errors;
    return !hasError;
};

const onMainImageChange = e => {
    const file = e.target.files[0];
    if (file) {
        const error = validateImage(file);
        if (error) {
            formErrors.value.mainImage = error;
            return;
        }
        formData.value.mainImage = file;
        const reader = new FileReader();
        reader.onload = (ev) => {
            formData.value.mainImagePreview = ev.target.result;
        };
        reader.readAsDataURL(file);
        formErrors.value.mainImage = "";
    } else {
        formData.value.mainImage = null;
        formData.value.mainImagePreview = null;
    }
};

const removeMainImage = () => {
    formData.value.mainImage = null;
    formData.value.mainImagePreview = null;
    if (!isEditMode.value) {
        formErrors.value.mainImage = "Vui lòng chọn ảnh chính";
    }
};

const onAdditionalImagesChange = e => {
    const files = Array.from(e.target.files);
    let hasError = false;
    files.forEach(file => {
        const error = validateImage(file);
        if (error) {
            formErrors.value.additionalImages = error;
            hasError = true;
        }
    });
    if (!hasError) {
        formData.value.additionalImages = files;
        formData.value.additionalImagePreviews = [];
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = (ev) => {
                formData.value.additionalImagePreviews.push(ev.target.result);
            };
            reader.readAsDataURL(file);
        });
        formErrors.value.additionalImages = "";
    }
};

const removeAdditionalImage = idx => {
    formData.value.additionalImages.splice(idx, 1);
    formData.value.additionalImagePreviews.splice(idx, 1);
};

// Variants functions
const showVariantsSection = () => {
    showVariants.value = true;
    addVariant();
};

const generateSKU = (name) => {
    const randomNum = Math.floor(Math.random() * 1000000).toString().padStart(6, '0');
    const namePart = name
        .toUpperCase()
        .replace(/[^A-Z0-9]/g, '')
        .substring(0, 4);
    return `${namePart}-${randomNum}`;
};

const addVariant = () => {
    formData.value.variants.push({
        colorName: '',
        sizes: [{
            size: '',
            price: formData.value.price || 0,
            sku: generateSKU(formData.value.name)
        }]
    });
    formErrors.value.variants.push({
        color: '',
        sizes: [{
            size: '',
            price: '',
            sku: ''
        }]
    });
};

const removeVariantColor = (vIdx) => {
    formData.value.variants.splice(vIdx, 1);
    formErrors.value.variants.splice(vIdx, 1);
};

const addSizeToVariant = (vIdx) => {
    const productPrice = formData.value.price || 0;
    formData.value.variants[vIdx].sizes.push({
        size: '',
        price: productPrice,
        sku: generateSKU(formData.value.name)
    });
    formErrors.value.variants[vIdx].sizes.push({
        size: '',
        price: '',
        sku: ''
    });
};

const removeSizeFromVariant = (vIdx, sIdx) => {
    if (formData.value.variants[vIdx].sizes.length <= 1) return;
    formData.value.variants[vIdx].sizes.splice(sIdx, 1);
    formErrors.value.variants[vIdx].sizes.splice(sIdx, 1);
};

const onVariantImagesChange = (e, vIdx) => {
    const files = Array.from(e.target.files);
    if (!formData.value.variants[vIdx].images) formData.value.variants[vIdx].images = [];
    if (!formData.value.variants[vIdx].imagesPreview) formData.value.variants[vIdx].imagesPreview = [];
    formData.value.variants[vIdx].images = files;
    formData.value.variants[vIdx].imagesPreview = [];
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (ev) => {
            formData.value.variants[vIdx].imagesPreview.push(ev.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const handleSubmit = async () => {
    if (!validateForm()) {
        push.error("Vui lòng kiểm tra và điền đầy đủ thông tin");
        return;
    }

    isSubmitting.value = true;

    try {
        const data = new FormData();
        data.append("name", formData.value.name);
        data.append("price", formData.value.price);
        data.append("discount_price", formData.value.discount_price);
        data.append("description", formData.value.description);
        data.append("is_active", formData.value.status ? "1" : "0");
        data.append("category_id", formData.value.category);
        data.append("brand_id", formData.value.brand);

        if (formData.value.mainImage) {
            data.append("is_main", formData.value.mainImage);
        }

        formData.value.additionalImages.forEach(img => {
            data.append("image_path[]", img);
        });

        // Gửi variants nếu có
        formData.value.variants.forEach((variant, vIdx) => {
            // Gửi color cho variant
            data.append(`variants[${vIdx}][color]`, variant.colorName);
            // Gửi ảnh cho variant
            if (variant.images && variant.images.length > 0) {
                variant.images.forEach(imgFile => {
                    data.append(`variants[${vIdx}][images][]`, imgFile);
                });
            }
            // Gửi từng size cho variant
            variant.sizes.forEach((sizeObj, sIdx) => {
                data.append(`variants[${vIdx}][sizes][${sIdx}][size]`, sizeObj.size);
                data.append(`variants[${vIdx}][sizes][${sIdx}][price]`, sizeObj.price);
                data.append(`variants[${vIdx}][sizes][${sIdx}][sku]`, sizeObj.sku);
            });
        });

        if (isEditMode.value) {
            await updateProduct(route.params.id, data);
            push.success("Cập nhật sản phẩm thành công");
        } else {
            await createProduct(data);
            push.success("Tạo sản phẩm thành công");
        }
        router.push("/admin/products");
    } catch (err) {
        console.error(err);
        push.error("Có lỗi xảy ra");
    } finally {
        isSubmitting.value = false;
    }
};
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