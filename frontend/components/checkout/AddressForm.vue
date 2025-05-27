<template>
    <div v-if="show"
        class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50">
        <div class="tw-bg-white tw-rounded-lg tw-w-full tw-max-w-2xl tw-mx-4">
            <div class="tw-p-6">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
                    <h3 class="tw-text-lg tw-font-semibold">
                        {{ editingIndex === null ? 'Thêm địa chỉ mới' : 'Chỉnh sửa địa chỉ' }}
                    </h3>
                    <button @click="$emit('close')" class="tw-text-gray-500 hover:tw-text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="tw-space-y-4">
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Họ và tên</label>
                        <input v-model="form.fullName" type="text"
                            class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md">
                    </div>
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Số điện thoại</label>
                        <input v-model="form.phone" type="tel"
                            class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md">
                    </div>
                    <div class="tw-grid tw-grid-cols-2 tw-gap-4">
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Tỉnh/Thành</label>
                            <select v-model="form.province" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md">
                                <option value="">Chọn tỉnh/thành</option>
                                <option value="hanoi">Hà Nội</option>
                                <option value="hcm">TP. HCM</option>
                            </select>
                        </div>
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Quận/Huyện</label>
                            <select v-model="form.district" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md">
                                <option value="">Chọn quận/huyện</option>
                                <option value="quan1">Quận 1</option>
                                <option value="quan2">Quận 2</option>
                            </select>
                        </div>
                    </div>
                    <div class="tw-grid tw-grid-cols-2 tw-gap-4">
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Phường/Xã</label>
                            <select v-model="form.ward" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md">
                                <option value="">Chọn phường/xã</option>
                                <option value="phuong1">Phường 1</option>
                                <option value="phuong2">Phường 2</option>
                            </select>
                        </div>
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Thôn/Xóm</label>
                            <input v-model="form.hamlet" type="text"
                                class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md">
                        </div>
                    </div>
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Địa chỉ chi tiết</label>
                        <input v-model="form.detail" type="text"
                            class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md" placeholder="Số nhà, tên đường">
                    </div>
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Ghi chú</label>
                        <textarea v-model="form.note" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md" rows="3"
                            placeholder="Ghi chú về địa chỉ giao hàng"></textarea>
                    </div>
                    <div class="tw-flex tw-gap-3">
                        <button @click="handleSave"
                            class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-black tw-text-white tw-rounded-md hover:tw-bg-gray-800">
                            {{ editingIndex === null ? 'Thêm địa chỉ' : 'Cập nhật' }}
                        </button>
                        <button @click="$emit('close')"
                            class="tw-px-4 tw-py-2 tw-border tw-rounded-md hover:tw-bg-gray-50">
                            Hủy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    editingIndex: {
        type: Number,
        default: null
    },
    address: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['close', 'save'])

const form = ref({
    fullName: '',
    phone: '',
    province: '',
    district: '',
    ward: '',
    hamlet: '',
    detail: '',
    note: ''
})

watch(() => props.address, (newAddress) => {
    if (newAddress) {
        form.value = { ...newAddress }
    }
}, { immediate: true })

const handleSave = () => {
    emit('save', {
        ...form.value,
        fullAddress: `${form.value.detail}, ${form.value.hamlet}, ${form.value.ward}, ${form.value.district}, ${form.value.province}`
    })
}
</script>