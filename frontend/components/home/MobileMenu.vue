<template>
    <div class="mobile-menu" :class="{ 'tw-block': isOpen, 'tw-hidden': !isOpen }">
        <div class="tw-flex tw-justify-between tw-items-center tw-p-4 tw-border-b">
            <h3 class="tw-m-0 tw-font-medium">Menu</h3>
            <button class="tw-bg-transparent tw-border-0" @click="close">
                <i class="bi bi-x-lg tw-text-xl"></i>
            </button>
        </div>

        <!-- Mobile Icons -->
        <div class="tw-flex tw-justify-around tw-items-center tw-p-4 tw-border-b">
            <a href="/search" class="tw-text-gray-700" @click="close">
                <i class="bi bi-search tw-text-xl"></i>
            </a>
            <NuxtLink to="/wishlist" class="tw-text-gray-700 tw-relative" @click="close">
                <i class="bi bi-heart tw-text-xl"></i>
                <span
                    class="tw-absolute -tw-top-2 -tw-right-2 tw-bg-red-500 tw-text-white tw-rounded-full tw-w-5 tw-h-5 tw-flex tw-items-center tw-justify-center tw-text-xs">2</span>
            </NuxtLink>
            <NuxtLink to="/cart" class="tw-text-gray-700 tw-relative" @click="close">
                <i class="bi bi-cart tw-text-xl"></i>
                <span
                    class="tw-absolute -tw-top-2 -tw-right-2 tw-bg-red-500 tw-text-white tw-rounded-full tw-w-5 tw-h-5 tw-flex tw-items-center tw-justify-center tw-text-xs">2</span>
            </NuxtLink>
            <NuxtLink to="/profile" class="tw-text-gray-700" @click="close">
                <i class="bi bi-person tw-text-xl"></i>
            </NuxtLink>
        </div>

        <ul class="tw-list-none tw-p-0 tw-m-0">
            <li class="tw-py-3 tw-border-b">
                <NuxtLink to="/" class="tw-text-gray-700 tw-font-medium tw-px-4 tw-block" @click="close">Trang chủ
                </NuxtLink>
            </li>
            <li class="tw-py-3 tw-border-b">
                <NuxtLink to="/product" class="tw-text-gray-700 tw-font-medium tw-px-4 tw-block" @click="close">
                    Sản phẩm</NuxtLink>
            </li>
            <li class="tw-py-3 tw-border-b">
                <NuxtLink to="/about" class="tw-text-gray-700 tw-font-medium tw-px-4 tw-block" @click="close">Giới
                    thiệu</NuxtLink>
            </li>
            <li class="tw-py-3 tw-border-b">
                <NuxtLink to="/blogs" class="tw-text-gray-700 tw-font-medium tw-px-4 tw-block" @click="close">Tin
                    tức</NuxtLink>
            </li>
            <li class="tw-py-3 tw-border-b">
                <NuxtLink to="/contact" class="tw-text-gray-700 tw-font-medium tw-px-4 tw-block" @click="close">
                    Liên hệ</NuxtLink>
            </li>
            <li class="tw-py-3 tw-border-b">
                <NuxtLink to="/order-tracking" class="tw-text-gray-700 tw-font-medium tw-px-4 tw-block" @click="close">
                    Kiểm tra đơn hàng</NuxtLink>
            </li>
        </ul>
    </div>
</template>

<script setup>
defineProps({
    isOpen: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close'])

const close = () => {
    emit('close')
}
</script>

<style scoped>
.mobile-menu {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    background: white;
    z-index: 1000;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
    transform: translateX(100%);
    transition: transform 0.3s ease-in-out;
}

.mobile-menu.tw-block {
    transform: translateX(0);
}

@media (min-width: 992px) {
    .mobile-menu {
        display: none !important;
    }
}
</style>