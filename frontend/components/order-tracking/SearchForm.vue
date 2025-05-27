<template>
    <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm">
        <h2 class="tw-text-lg tw-font-semibold tw-mb-6">Tra cứu đơn hàng</h2>
        <div class="tw-flex tw-flex-col tw-gap-4">
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Mã đơn hàng</label>
                <input v-model="searchForm.orderId" type="text" placeholder="Nhập mã đơn hàng của bạn"
                    class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md">
            </div>
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Số điện thoại</label>
                <input v-model="searchForm.phone" type="tel" placeholder="Nhập số điện thoại đặt hàng"
                    class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md">
            </div>
            <button @click="onSearch"
                class="tw-w-full tw-px-6 tw-py-2 tw-bg-[#81AACC] tw-text-white tw-rounded-md hover:tw-bg-[#498ac0]">
                Tra cứu
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'

const searchForm = reactive({
    orderId: '',
    phone: ''
})

const emit = defineEmits(['search'])

const onSearch = () => {
    emit('search', { ...searchForm })
}
</script>