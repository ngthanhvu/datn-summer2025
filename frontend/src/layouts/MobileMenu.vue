<template>
    <div class="mobile-menu" :class="{ 'block': isOpen, 'hidden': !isOpen }">
        <div class="flex justify-between items-center p-4 border-b border-gray-300">
            <h3 class="m-0 font-medium">Menu</h3>
            <button class="bg-transparent border-0" @click="close">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>

        <!-- Mobile Icons -->
        <div class="flex justify-around items-center p-4 border-b border-gray-300">
            <a href="#" class="text-gray-700" @click="close">
                <i class="bi bi-search text-xl"></i>
            </a>
            <router-link to="/san-pham-yeu-thich" class="text-gray-700 relative" @click="close">
                <i class="bi bi-heart text-xl"></i>
                <span
                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">2</span>
            </router-link>
            <router-link to="/gio-hang" class="text-gray-700 relative" @click="close">
                <i class="bi bi-cart text-xl"></i>
                <span
                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">2</span>
            </router-link>
            <router-link to="/trang-ca-nhan" class="text-gray-700" @click="close">
                <i class="bi bi-person text-xl"></i>
            </router-link>
        </div>

        <ul class="list-none p-0 m-0">
            <li class="py-3 border-b border-gray-300">
                <router-link to="/" class="text-gray-700 font-medium px-4 block" @click="close">Trang chủ
                </router-link>
            </li>
            <li class="py-3 border-b border-gray-300">
                <router-link to="/san-pham" class="text-gray-700 font-medium px-4 block" @click="close">
                    Sản phẩm</router-link>
            </li>
            <li class="py-3 border-b border-gray-300">
                <router-link to="/gioi-thieu" class="text-gray-700 font-medium px-4 block" @click="close">Giới
                    thiệu</router-link>
            </li>
            <li class="py-3 border-b border-gray-300">
                <router-link to="/tin-tuc" class="text-gray-700 font-medium px-4 block" @click="close">Tin
                    tức</router-link>
            </li>
            <li class="py-3 border-b border-gray-300">
                <router-link to="/lien-he" class="text-gray-700 font-medium px-4 block" @click="close">
                    Liên hệ</router-link>
            </li>
            <li class="py-3 border-b border-gray-300">
                <router-link to="/tra-cuu-don-hang" class="text-gray-700 font-medium px-4 block" @click="close">
                    Kiểm tra đơn hàng</router-link>
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

.mobile-menu.block {
    transform: translateX(0);
}

@media (min-width: 992px) {
    .mobile-menu {
        display: none !important;
    }
}
</style>