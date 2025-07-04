<template>
    <div class="tw-bg-white tw-rounded-lg tw-p-6 tw-w-[50%] mx-auto tw-border tw-border-gray-200">
        <form @submit.prevent="handleSubmit" class="form form-grid">
            <div class="form-group">
                <label for="name">Tên chương trình</label>
                <input id="name" v-model="formData.name" type="text" required
                    placeholder="Nhập tên chương trình khuyến mãi" />
                <span v-if="errors.name" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.name }}</span>
            </div>
            <div class="form-group">
                <label for="code">Mã giảm giá</label>
                <input id="code" v-model="formData.code" type="text" required placeholder="Nhập mã giảm giá" />
                <span v-if="errors.code" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.code }}</span>
            </div>
            <div class="form-group">
                <label for="type">Loại giảm giá</label>
                <select id="type" v-model="formData.type" required>
                    <option value="fixed">Giảm số tiền cố định</option>
                    <option value="percent">Giảm theo phần trăm</option>
                </select>
            </div>
            <div class="form-group">
                <label for="value">Giá trị giảm</label>
                <div class="input-with-suffix">
                    <input id="value" v-model.number="formData.value" type="number" required :min="0"
                        :max="formData.type === 'percent' ? 100 : undefined"
                        :step="formData.type === 'percent' ? 1 : 1000" />
                    <span class="suffix">{{ formData.type === 'percent' ? '%' : 'đ' }}</span>
                </div>
                <span v-if="errors.value" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.value }}</span>
            </div>
            <div class="form-group">
                <label for="min_order_value">Đơn hàng tối thiểu</label>
                <div class="input-with-suffix">
                    <input id="min_order_value" v-model.number="formData.min_order_value" type="number" required
                        :min="0" :step="1000" />
                    <span class="suffix">đ</span>
                </div>
                <span v-if="errors.min_order_value" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.min_order_value
                    }}</span>
            </div>
            <div class="form-group">
                <label for="max_discount_value">Giảm tối đa</label>
                <div class="input-with-suffix">
                    <input id="max_discount_value" v-model.number="formData.max_discount_value" type="number" required
                        :min="0" :step="1000" :disabled="formData.type === 'percent'" />
                    <span class="suffix">đ</span>
                </div>
                <span v-if="errors.max_discount_value" class="tw-text-red-500 tw-text-sm tw-mt-1">{{
                    errors.max_discount_value
                    }}</span>
            </div>
            <div class="form-group">
                <label for="usage_limit">Giới hạn sử dụng</label>
                <input id="usage_limit" v-model.number="formData.usage_limit" type="number" :min="0" :step="1"
                    placeholder="0 = không giới hạn" />
                <span v-if="errors.usage_limit" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.usage_limit
                    }}</span>
            </div>
            <div class="form-group">
                <label for="start_date">Ngày bắt đầu</label>
                <input id="start_date" v-model="formData.start_date" type="datetime-local" required />
                <span v-if="errors.start_date" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.start_date }}</span>
            </div>
            <div class="form-group">
                <label for="end_date">Ngày kết thúc</label>
                <input id="end_date" v-model="formData.end_date" type="datetime-local" required />
                <span v-if="errors.end_date" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.end_date }}</span>
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
            <button @click="handleSubmit"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-primary-dark">
                Tạo khuyến mãi
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useCoupon } from '@/composables/useCoupon'
import Swal from 'sweetalert2'

const { createCoupon } = useCoupon()

const formData = ref({
    name: '',
    code: '',
    type: 'fixed',
    value: 0,
    min_order_value: 0,
    max_discount_value: 0,
    usage_limit: 0,
    start_date: '',
    end_date: '',
    description: '',
    is_active: true
})

const errors = ref({})

function validateForm() {
    const err = {}
    if (!formData.value.name) err.name = 'Vui lòng nhập tên chương trình.'
    if (!formData.value.code) err.code = 'Vui lòng nhập mã giảm giá.'
    if (formData.value.value === '' || formData.value.value === null || formData.value.value < 0) err.value = 'Giá trị giảm phải lớn hơn hoặc bằng 0.'
    if (formData.value.min_order_value === '' || formData.value.min_order_value === null || formData.value.min_order_value < 0) err.min_order_value = 'Đơn hàng tối thiểu phải lớn hơn hoặc bằng 0.'
    if (formData.value.type !== 'percent' && (formData.value.max_discount_value === '' || formData.value.max_discount_value === null || formData.value.max_discount_value < 0)) err.max_discount_value = 'Giảm tối đa phải lớn hơn hoặc bằng 0.'
    if (formData.value.usage_limit === '' || formData.value.usage_limit === null || formData.value.usage_limit < 0) err.usage_limit = 'Giới hạn sử dụng phải lớn hơn hoặc bằng 0.'
    if (!formData.value.start_date) err.start_date = 'Vui lòng chọn ngày bắt đầu.'
    if (!formData.value.end_date) err.end_date = 'Vui lòng chọn ngày kết thúc.'
    if (formData.value.start_date && formData.value.end_date && new Date(formData.value.end_date) <= new Date(formData.value.start_date)) err.end_date = 'Ngày kết thúc phải sau ngày bắt đầu.'
    errors.value = err
    return Object.keys(err).length === 0
}

const handleSubmit = async () => {
    if (!validateForm()) return
    try {
        await createCoupon(formData.value)

        // Hiển thị thông báo thành công
        await Swal.fire({
            title: 'Thành công!',
            text: 'Mã giảm giá đã được tạo thành công',
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3bb77e'
        })

        // Reset form sau khi tạo thành công
        formData.value = {
            name: '',
            code: '',
            type: 'percent',
            value: 0,
            min_order_value: 0,
            max_discount_value: 0,
            usage_limit: 0,
            start_date: '',
            end_date: '',
            description: '',
            is_active: true
        }
        errors.value = {}
    } catch (error) {
        // Hiển thị thông báo lỗi
        Swal.fire({
            title: 'Lỗi!',
            text: 'Có lỗi xảy ra khi tạo mã giảm giá',
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3bb77e'
        })
        console.error('Lỗi khi tạo coupon:', error)
    }
}

watch(() => formData.value.type, (newType) => {
    if (newType === 'percent') {
        formData.value.max_discount_value = null
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

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem 2rem;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
