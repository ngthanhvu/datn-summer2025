<template>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-500 text-sm">{{ title }}</p>
                <h3 class="text-2xl font-bold mt-1">{{ value }}</h3>
                <p v-if="growth !== undefined" class="text-sm mt-1"
                    :class="growth >= 0 ? 'text-green-600' : 'text-red-600'">
                </p>
                <p v-else class="text-sm mt-1 text-gray-500">
                    {{ subText }}
                </p>
            </div>
            <div :class="`bg-${iconColor}-500/10 p-3 rounded-full`">
                <i :class="[icon, `text-${iconColor}-500`]"></i>
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