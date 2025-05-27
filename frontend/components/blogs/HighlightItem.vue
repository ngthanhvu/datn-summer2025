<template>
    <li class="tw-flex tw-gap-2 tw-items-center">
        <img class="tw-w-16 tw-h-12 tw-object-cover tw-flex-shrink-0" :src="item.img" :alt="item.text" width="96"
            height="48" />
        <span class="hover:tw-text-[#81AACC] tw-cursor-pointer">{{ item.text }}</span>
    </li>
</template>

<script setup>
defineProps({
    item: {
        type: Object,
        required: true
    }
});
</script>