<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-start">
            <div>
                <p class="tw-text-gray-500 tw-text-sm">{{ title }}</p>
                <h3 class="tw-text-2xl tw-font-bold tw-mt-1">{{ value }}</h3>
                <p v-if="growth !== undefined" class="tw-text-sm tw-mt-1"
                    :class="growth >= 0 ? 'tw-text-green-600' : 'tw-text-red-600'">
                    <i :class="['fas', growth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down']"></i>
                    {{ Math.abs(growth) }}% so với tháng trước
                </p>
                <p v-else class="tw-text-sm tw-mt-1 tw-text-gray-500">
                    {{ subText }}
                </p>
            </div>
            <div :class="`tw-bg-${iconColor}-500/10 tw-p-3 tw-rounded-full`">
                <i :class="[icon, `tw-text-${iconColor}-500`]"></i>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    },
    growth: {
        type: Number,
        default: undefined
    },
    subText: {
        type: String,
        default: ''
    },
    icon: {
        type: String,
        required: true
    },
    iconColor: {
        type: String,
        required: true
    }
})
</script>