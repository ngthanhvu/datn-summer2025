<template>
    <div class="tw-mb-6">
        <h2 class="tw-text-lg tw-font-semibold tw-mb-4">Địa chỉ giao hàng</h2>
        <div class="tw-space-y-4">
            <!-- Existing Addresses -->
            <div v-for="(address, index) in addresses" :key="index"
                class="tw-p-4 tw-border tw-rounded-md tw-cursor-pointer hover:tw-bg-gray-50"
                :class="{ 'tw-border-black': selectedAddress === index }" @click="$emit('select', index)">
                <div class="tw-flex tw-justify-between tw-items-start">
                    <div>
                        <p class="tw-font-medium">{{ address.fullName }}</p>
                        <p class="tw-text-sm tw-text-gray-600">{{ address.phone }}</p>
                        <p class="tw-text-sm tw-text-gray-600">{{ address.fullAddress }}</p>
                    </div>
                    <div class="tw-flex tw-gap-2">
                        <button @click.stop="$emit('edit', index)" class="tw-text-blue-600 hover:tw-text-blue-800">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button @click.stop="$emit('delete', index)" class="tw-text-red-600 hover:tw-text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Add New Address Button -->
            <button @click="$emit('add')"
                class="tw-w-full tw-p-3 tw-border-2 tw-border-dashed tw-border-gray-300 tw-rounded-md tw-text-gray-600 hover:tw-border-gray-400 hover:tw-text-gray-800">
                <i class="fas fa-plus tw-mr-2"></i>
                Thêm địa chỉ mới
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    addresses: {
        type: Array,
        required: true
    },
    selectedAddress: {
        type: Number,
        required: true
    }
})

defineEmits(['select', 'edit', 'delete', 'add'])
</script>