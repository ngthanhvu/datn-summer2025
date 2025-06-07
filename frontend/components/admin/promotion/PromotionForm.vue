<template>
    <div>
        <form @submit.prevent="handleSubmit" class="form">
            <!-- Tên chương trình -->
            <div class="form-group">
                <label for="name">Tên chương trình</label>
                <input id="name" v-model="formData.name" type="text" required
                    placeholder="Nhập tên chương trình khuyến mãi" />
            </div>

            <!-- Mã giảm giá -->
            <div class="form-group">
                <label for="code">Mã giảm giá</label>
                <input id="code" v-model="formData.code" type="text" required placeholder="Nhập mã giảm giá" />
            </div>

            <!-- Loại giảm giá -->
            <div class="form-group">
                <label for="type">Loại giảm giá</label>
                <select id="type" v-model="formData.type" required>
                    <option value="percentage">Giảm theo phần trăm</option>
                    <option value="fixed">Giảm số tiền cố định</option>
                </select>
            </div>

            <!-- Giá trị giảm -->
            <div class="form-group">
                <label for="value">Giá trị giảm</label>
                <div class="input-with-suffix">
                    <input id="value" v-model.number="formData.value" type="number" required :min="0"
                        :max="formData.type === 'percentage' ? 100 : undefined"
                        :step="formData.type === 'percentage' ? 1 : 1000" />
                    <span class="suffix">{{ formData.type === 'percentage' ? '%' : 'đ' }}</span>
                </div>
            </div>

            <!-- Đơn hàng tối thiểu -->
            <div class="form-group">
                <label for="minSpend">Đơn hàng tối thiểu</label>
                <div class="input-with-suffix">
                    <input id="minSpend" v-model.number="formData.minSpend" type="number" required :min="0"
                        :step="1000" />
                    <span class="suffix">đ</span>
                </div>
            </div>

            <!-- Giảm tối đa -->
            <div class="form-group">
                <label for="maxDiscount">Giảm tối đa</label>
                <div class="input-with-suffix">
                    <input id="maxDiscount" v-model.number="formData.maxDiscount" type="number" required :min="0"
                        :step="1000" />
                    <span class="suffix">đ</span>
                </div>
            </div>

            <!-- Giới hạn sử dụng -->
            <div class="form-group">
                <label for="usageLimit">Giới hạn sử dụng</label>
                <input id="usageLimit" v-model.number="formData.usageLimit" type="number" :min="0" :step="1"
                    placeholder="0 = không giới hạn" />
            </div>

            <!-- Ngày bắt đầu -->
            <div class="form-group">
                <label for="startDate">Ngày bắt đầu</label>
                <input id="startDate" v-model="formData.startDate" type="datetime-local" required />
            </div>

            <!-- Ngày kết thúc -->
            <div class="form-group">
                <label for="endDate">Ngày kết thúc</label>
                <input id="endDate" v-model="formData.endDate" type="datetime-local" required />
            </div>

            <!-- Mô tả -->
            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea id="description" v-model="formData.description" rows="3"
                    placeholder="Nhập mô tả chương trình khuyến mãi"></textarea>
            </div>

            <!-- Trạng thái -->
            <div class="form-group">
                <label for="status">Trạng thái</label>
                <div class="toggle">
                    <input type="checkbox" id="status" v-model="formData.status" />
                    <label for="status"></label>
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
import { ref } from 'vue'

const props = defineProps({
    initialData: {
        type: Object,
        default: () => ({})
    }
})

const formData = ref({
    name: '',
    code: '',
    type: 'percentage',
    value: 0,
    minSpend: 0,
    maxDiscount: 0,
    usageLimit: 0,
    startDate: '',
    endDate: '',
    description: '',
    status: true,
    ...props.initialData
})

const handleSubmit = async () => {
    try {
        console.log('Create promotion:', formData.value)
        await navigateTo('/admin/promotions')
    } catch (error) {
        console.error('Error creating promotion:', error)
    }
}
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
