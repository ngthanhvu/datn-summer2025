<template>
    <div class="flex flex-col gap-6">
        <!-- Header Section -->
        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Chỉnh sửa sản phẩm</h1>
            <p class="text-gray-600">Cập nhật thông tin chi tiết về sản phẩm và các biến thể</p>
        </div>

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
                                class="bg-green-500 text-white rounded-lg px-4 py-2 flex items-center gap-2 hover:bg-green-600 transition-colors">
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
                                    class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition-colors"
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

                                    <div class="p-4">
                                        <div v-for="(sizeObj, sIdx) in variant.sizes" :key="sIdx"
                                            class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end mb-4 p-4 bg-gray-50 rounded-lg">
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
                                                        class="px-3 py-2 text-red-500 hover:text-red-700 border border-gray-300 rounded-md hover:bg-red-50 transition-colors">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <div v-if="formErrors.variants[vIdx]?.sizes[sIdx]?.sku"
                                                    class="text-red-500 text-sm mt-1">
                                                    {{ formErrors.variants[vIdx].sizes[sIdx].sku }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Add Size Button -->
                                        <button @click="addSizeToVariant(vIdx)"
                                            class="bg-blue-100 text-blue-600 rounded-lg px-4 py-2 text-sm hover:bg-blue-200 transition-colors">
                                            <i class="fas fa-plus mr-2"></i>
                                            Thêm kích thước
                                        </button>
                                    </div>
                                </div>

                                <!-- Variant Images Section - Separate from size grid -->
                                <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                    <h5 class="text-sm font-medium text-blue-800 mb-3 flex items-center gap-2">
                                        <i class="fas fa-images"></i>
                                        Ảnh biến thể cho màu "{{ variant.colorName || 'Chưa đặt tên' }}"
                                    </h5>

                                    <div class="flex items-center gap-4">
                                        <input type="file" multiple :id="`variant-upload-${vIdx}`"
                                            @change="onVariantImagesChange($event, vIdx)" class="hidden" />
                                        <label :for="`variant-upload-${vIdx}`"
                                            class="inline-flex items-center bg-blue-500 hover:bg-blue-600 text-white font-medium px-4 py-2 rounded-md cursor-pointer gap-2 text-sm transition-colors">
                                            <i class="fas fa-upload"></i>
                                            <span>Tải ảnh</span>
                                        </label>

                                        <div v-if="variant.imagesPreview && variant.imagesPreview.length"
                                            class="flex gap-2">
                                            <div v-for="(img, i) in variant.imagesPreview" :key="i"
                                                class="relative group">
                                                <img :src="img" :alt="`Ảnh ${i + 1}`"
                                                    class="w-16 h-16 object-cover rounded-lg border border-blue-200 shadow-sm" />
                                                <button @click="removeVariantImage(vIdx, i)"
                                                    class="absolute -top-2 -right-2 p-1 rounded-full bg-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity text-xs hover:bg-red-600">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-xs text-blue-600 mt-2">
                                        Ảnh này sẽ được áp dụng cho tất cả kích thước của màu sắc này
                                    </p>
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
                class="bg-blue-500 text-white rounded-lg px-8 py-3 hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium">
                <i v-if="isSubmitting" class="fas fa-spinner fa-spin mr-2"></i>
                {{ isSubmitting ? 'Đang cập nhật...' : 'Cập nhật sản phẩm' }}
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

        if (!categoryStore.categories.length) {
            await categoryStore.fetchCategories();
        }
        if (!brandStore.brands.length) {
            await brandStore.fetchBrands();
        }

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

        if (isEditMode.value) {
            try {
                const product = await getProductById(route.params.id);

                formData.value = {
                    name: product.name || '',
                    price: product.price || 0,
                    discount_price: product.discount_price || 0,
                    category: product.categories_id ? String(product.categories_id) : '',
                    brand: product.brand_id ? String(product.brand_id) : '',
                    description: product.description || '',
                    status: !!product.is_active,
                    mainImage: null,
                    mainImagePreview: product.main_image_url || product.images?.[0]?.image_path || null,
                    additionalImages: [],
                    additionalImagePreviews: product.additional_images || product.images?.slice(1)?.map(img => img.image_path) || [],
                    variants: product.variants || []
                };

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

                if (product.variants && product.variants.length > 0) {
                    showVariants.value = true;

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

                    formErrors.value.variants = formData.value.variants.map(() => ({
                        color: '',
                        sizes: [{
                            size: '',
                            price: '',
                            sku: ''
                        }]
                    }));
                }

            } catch (err) {
                console.error('Error loading product:', err);
                push.error("Không tìm thấy sản phẩm");
                router.push("/admin/products");
            }
        }

        isDataLoaded.value = true;
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
        }],
        images: [],
        imagesPreview: []
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
        if (formData.value.category && formData.value.category.trim() !== '') {
            data.append("categories_id", formData.value.category);
        }
        if (formData.value.brand && formData.value.brand.trim() !== '') {
            data.append("brand_id", formData.value.brand);
        }

        if (formData.value.mainImage) {
            data.append("is_main", formData.value.mainImage);
        }

        formData.value.additionalImages.forEach(img => {
            data.append("image_path[]", img);
        });

        formData.value.variants.forEach((variant, vIdx) => {
            data.append(`variants[${vIdx}][color]`, variant.colorName);
            if (variant.images && variant.images.length > 0) {
                variant.images.forEach(imgFile => {
                    data.append(`variants[${vIdx}][images][]`, imgFile);
                });
            }
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

// Thêm function removeVariantImage
const removeVariantImage = (vIdx, i) => {
    if (formData.value.variants[vIdx].imagesPreview) {
        formData.value.variants[vIdx].imagesPreview.splice(i, 1);
    }
    if (formData.value.variants[vIdx].images) {
        formData.value.variants[vIdx].images.splice(i, 1);
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