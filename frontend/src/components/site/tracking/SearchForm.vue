<template>
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h2 class="text-lg font-semibold mb-6">Tra cứu đơn hàng</h2>
        <div class="flex flex-col gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Mã vận đơn</label>
                <input v-model="searchForm.trackingCode" type="text" placeholder="Nhập mã vận đơn của bạn"
                    class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-1 focus:ring-[#81AACC] rounded-md" />
            </div>
            <button @click="onSearch" class="w-full px-6 py-2 bg-[#81AACC] text-white rounded-md hover:bg-[#498ac0]">
                Tra cứu
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'

const searchForm = reactive({
    trackingCode: ''
})

const emit = defineEmits(['search'])

const onSearch = () => {
    emit('search', { ...searchForm })
}
</script>
