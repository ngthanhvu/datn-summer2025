<template>
    <div :class="[
        'skeleton-animation',
        `tw-bg-${color}-200`,
        `tw-rounded-${rounded}`,
        `tw-h-${height}`,
        `tw-w-${width}`,
        className
    ]" :style="customStyle"></div>
</template>

<script setup>
defineProps({
    height: {
        type: String,
        default: '4'
    },
    width: {
        type: String,
        default: 'full'
    },
    color: {
        type: String,
        default: 'gray'
    },
    rounded: {
        type: String,
        default: 'md'
    },
    className: {
        type: String,
        default: ''
    },
    customStyle: {
        type: Object,
        default: () => ({})
    }
})
</script>

<style scoped>
.skeleton-animation {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
    background-size: 400% 100%;
    animation: skeleton-loading 1.4s ease infinite;
}

@keyframes skeleton-loading {
    0% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0% 50%;
    }
}
</style>