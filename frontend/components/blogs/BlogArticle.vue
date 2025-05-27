<template>
    <article class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-6">
        <div class="tw-flex-shrink-0 tw-max-w-[320px] tw-w-full">
            <img class="tw-object-cover tw-w-full tw-h-40 sm:tw-h-40" :src="article.image" :alt="article.title"
                width="320" height="160" />
        </div>
        <div class="tw-flex tw-flex-col tw-text-base tw-leading-relaxed tw-max-w-xl">
            <h2 class="tw-text-[20px] tw-font-normal tw-mb-1 tw-uppercase hover:tw-text-[#81AACC] tw-cursor-pointer">
                {{ article.title }}
            </h2>
            <div class="tw-flex tw-items-center tw-text-gray-600 tw-text-sm tw-mb-2 tw-space-x-3">
                <div class="tw-flex tw-items-center tw-space-x-1">
                    <i class="far fa-calendar-alt"></i>
                    <span>{{ article.date }}</span>
                </div>
                <div class="tw-flex tw-items-center tw-space-x-1">
                    <i class="far fa-clock"></i>
                    <span>{{ article.readTime }}</span>
                </div>
            </div>
            <p class="tw-text-gray-500 tw-mb-2">
                {{ article.excerpt }}
            </p>
            <a class="tw-text-sm tw-underline tw-text-black tw-w-max" :href="article.link || '#'">
                Đọc tiếp
            </a>
        </div>
    </article>
</template>

<script setup>
defineProps({
    article: {
        type: Object,
        required: true
    }
});
</script>