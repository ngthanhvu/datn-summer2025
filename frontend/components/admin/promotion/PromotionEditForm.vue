<template>
    <div>
        <div v-if="loading" class="tw-flex tw-justify-center tw-items-center tw-py-8">
            <div class="tw-animate-spin tw-rounded-full tw-h-8 tw-w-8 tw-border-b-2 tw-border-primary"></div>
            <span class="tw-ml-2">Đang tải...</span>
        </div>
        <div v-else-if="error"
            class="tw-bg-red-100 tw-border tw-border-red-400 tw-text-red-700 tw-px-4 tw-py-3 tw-rounded tw-mb-4">
            {{ error }}
        </div>
        <form v-else @submit.prevent="handleSubmit" class="form">
            <div class="form-group">
                <label for="name">Tên chương trình</label>
                <input id="name"
                    class="focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                    v-model="formData.name" type="text" required placeholder="Nhập tên chương trình khuyến mãi" />
            </div>
            <div class="form-group">
                <label for="code">Mã giảm giá</label>
                <input id="code"
                    class="focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                    v-model="formData.code" type="text" required placeholder="Nhập mã giảm giá" />
            </div>
            <div class="form-group">
                <label for="type">Loại giảm giá</label>
                <select id="type"
                    class="focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                    v-model="formData.type" required>
                    <option value="percentage">Giảm theo phần trăm</option>
                    <option value="fixed">Giảm số tiền cố định</option>
                </select>
            </div>
            <div class="form-group">
                <label for="value">Giá trị giảm</label>
                <div class="input-with-suffix">
                    <input id="value"
                        class="focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                        v-model.number="formData.value" type="number" required :min="0"
                        :max="formData.type === 'percentage' ? 100 : undefined"
                        :step="formData.type === 'percentage' ? 1 : 1000" />
                    <span class="suffix">{{ formData.type === 'percentage' ? '%' : 'đ' }}</span>
                </div>
            </div>
            <div class="form-group">
                <label for="min_order_value">Đơn hàng tối thiểu</label>
                <div class="input-with-suffix">
                    <input id="min_order_value"
                        class="focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                        v-model.number="formData.min_order_value" type="number" required :min="0" :step="1000" />
                    <span class="suffix">đ</span>
                </div>
            </div>
            <div class="form-group">
                <label for="max_discount_value">Giảm tối đa</label>
                <div class="input-with-suffix">
                    <input id="max_discount_value"
                        class="focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                        v-model.number="formData.max_discount_value" type="number" required :min="0" :step="1000" />
                    <span class="suffix">đ</span>
                </div>
            </div>
            <div class="form-group">
                <label for="usage_limit">Giới hạn sử dụng</label>
                <input id="usage_limit"
                    class="focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                    v-model.number="formData.usage_limit" type="number" :min="0" :step="1"
                    placeholder="0 = không giới hạn" />
            </div>
            <div class="form-group">
                <label for="start_date">Ngày bắt đầu</label>
                <input id="start_date"
                    class="focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                    v-model="formData.start_date" type="datetime-local" required />
            </div>
            <div class="form-group">
                <label for="end_date">Ngày kết thúc</label>
                <input id="end_date"
                    class="focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                    v-model="formData.end_date" type="datetime-local" required />
            </div>
            <div class="form-group">
                <label for="is_active">Trạng thái</label>
                <div class="toggle">
                    <input type="checkbox" id="is_active" v-model="formData.is_active" />
                    <label for="is_active"></label>
                </div>
            </div>
        </form>
        <div class="tw-flex tw-justify-end tw-gap-4 tw-mt-6">
            <button @click="$emit('cancel')"
                class="tw-bg-gray-500 tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-gray-600">
                Hủy
            </button>
            <button @click="handleSubmit" :disabled="loading"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-primary-dark disabled:tw-opacity-50">
                {{ loading ? 'Đang cập nhật...' : 'Cập nhật khuyến mãi' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useCoupon } from '@/composables/useCoupon'
import Swal from 'sweetalert2'

const props = defineProps({
    couponId: {
        type: [String, Number],
        required: true
    }
})

const emit = defineEmits(['cancel', 'updated'])

const { getCouponById, updateCoupon } = useCoupon()

const loading = ref(false)
const error = ref('')

const formData = ref({
    name: '',
    code: '',
    type: 'percentage',
    value: 0,
    min_order_value: 0,
    max_discount_value: 0,
    usage_limit: 0,
    start_date: '',
    end_date: '',
    description: '',
    is_active: true
})

const loadCouponData = async () => {
    try {
        loading.value = true
        error.value = ''
        const coupon = await getCouponById(props.couponId)

        if (coupon) {
            // Format dates for datetime-local input
            const formatDateForInput = (dateString) => {
                if (!dateString) return ''
                const date = new Date(dateString)
                return date.toISOString().slice(0, 16)
            }

            formData.value = {
                name: coupon.name || '',
                code: coupon.code || '',
                type: coupon.type === 'percent' ? 'percentage' : coupon.type,
                value: coupon.value || 0,
                min_order_value: coupon.min_order_value || 0,
                max_discount_value: coupon.max_discount_value || 0,
                usage_limit: coupon.usage_limit || 0,
                start_date: formatDateForInput(coupon.start_date),
                end_date: formatDateForInput(coupon.end_date),
                description: coupon.description || '',
                is_active: coupon.is_active !== undefined ? coupon.is_active : true
            }
        }
    } catch (err) {
        error.value = 'Không thể tải dữ liệu mã giảm giá. Vui lòng thử lại.'
        console.error('Error loading coupon:', err)
    } finally {
        loading.value = false
    }
}

const handleSubmit = async () => {
    try {
        loading.value = true

        // Validate dates
        if (new Date(formData.value.start_date) >= new Date(formData.value.end_date)) {
            throw new Error('Ngày kết thúc phải sau ngày bắt đầu')
        }

        await updateCoupon(props.couponId, {
            ...formData.value,
            type: formData.value.type === 'percentage' ? 'percent' : formData.value.type
        })

        // Hiển thị thông báo thành công
        await Swal.fire({
            title: 'Thành công!',
            text: 'Mã giảm giá đã được cập nhật thành công',
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3bb77e'
        })

        // Emit event để parent component biết đã cập nhật
        emit('updated')
    } catch (error) {
        // Hiển thị thông báo lỗi
        const errorMessage = error.message || 'Có lỗi xảy ra khi cập nhật mã giảm giá'
        Swal.fire({
            title: 'Lỗi!',
            text: errorMessage,
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3bb77e'
        })
        console.error('Lỗi khi cập nhật coupon:', error)
    } finally {
        loading.value = false
    }
}

// Watch for couponId changes
watch(() => props.couponId, () => {
    if (props.couponId) {
        loadCouponData()
    }
}, { immediate: true })

// Load data on component mount
onMounted(() => {
    if (props.couponId) {
        loadCouponData()
    }
})
</script>

<style scoped>
.form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 500;
    color: #374151;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group input[type="datetime-local"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.875rem;
}

.form-group textarea {
    resize: vertical;
}

.input-with-suffix {
    position: relative;
    display: flex;
    align-items: center;
}

.input-with-suffix input {
    padding-right: 2rem;
}

.suffix {
    position: absolute;
    right: 0.75rem;
    color: #6b7280;
    font-size: 0.875rem;
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

.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}
</style>