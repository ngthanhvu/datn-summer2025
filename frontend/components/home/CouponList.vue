<template>
    <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6">
        <!-- Card 1 -->
        <div class="tw-flex tw-max-w-xs tw-w-full tw-bg-white tw-shadow-md tw-rounded-md">
            <div class="tw-left-edge tw-flex tw-items-center tw-justify-center">
                <i class="fa-solid fa-ticket tw-text-white tw-text-2xl"></i>
            </div>
            <div class="tw-flex tw-flex-col tw-justify-center tw-px-4 tw-py-4 tw-flex-1">
                <p class="tw-text-sm tw-text-blue-600 tw-font-semibold tw-text-center">
                    NHẬP MÃ:
                    <span class="tw-font-normal">EGA10</span>
                </p>
                <p class="tw-text-xs tw-text-gray-500 tw-text-center tw-mt-1 tw-leading-tight">
                    Mã giảm 10% cho đơn hàng tối thiểu 1.5 triệu
                </p>
                <div class="tw-mt-3 tw-flex tw-items-center tw-justify-between">
                    <button @click="copyCouponCode('EGA10')"
                        class="tw-bg-blue-600 tw-text-white tw-text-xs tw-px-3 tw-py-1 tw-rounded-sm hover:tw-bg-blue-700 tw-transition">
                        Sao chép
                    </button>
                    <a class="tw-text-xs tw-text-gray-700 hover:tw-underline" href="#">
                        Điều kiện
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="tw-flex tw-max-w-xs tw-w-full tw-bg-white tw-shadow-md tw-rounded-md">
            <div class="tw-left-edge tw-flex tw-items-center tw-justify-center">
                <i class="fa-solid fa-ticket tw-text-white tw-text-2xl"></i>
            </div>
            <div class="tw-flex tw-flex-col tw-justify-center tw-px-4 tw-py-4 tw-flex-1">
                <p class="tw-text-sm tw-text-blue-600 tw-font-semibold tw-text-center">
                    NHẬP MÃ:
                    <span class="tw-font-normal">EGA15</span>
                </p>
                <p class="tw-text-xs tw-text-gray-500 tw-text-center tw-mt-1 tw-leading-tight">
                    Mã giảm 15% cho đơn hàng tối thiểu 2 triệu
                </p>
                <div class="tw-mt-3 tw-flex tw-items-center tw-justify-between">
                    <button @click="copyCouponCode('EGA15')"
                        class="tw-bg-blue-600 tw-text-white tw-text-xs tw-px-3 tw-py-1 tw-rounded-sm hover:tw-bg-blue-700 tw-transition">
                        Sao chép
                    </button>
                    <a class="tw-text-xs tw-text-gray-700 hover:tw-underline" href="#">
                        Điều kiện
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="tw-flex tw-max-w-xs tw-w-full tw-bg-white tw-shadow-md tw-rounded-md">
            <div class="tw-left-edge tw-flex tw-items-center tw-justify-center">
                <i class="fa-solid fa-ticket tw-text-white tw-text-2xl"></i>
            </div>
            <div class="tw-flex tw-flex-col tw-justify-center tw-px-4 tw-py-4 tw-flex-1">
                <p class="tw-text-sm tw-text-blue-600 tw-font-semibold tw-text-center">
                    NHẬP MÃ:
                    <span class="tw-font-normal">EGA99</span>
                </p>
                <p class="tw-text-xs tw-text-gray-500 tw-text-center tw-mt-1 tw-leading-tight">
                    Giảm giá 99k các sản phẩm Áo thun
                </p>
                <div class="tw-mt-3 tw-flex tw-items-center tw-justify-between">
                    <button @click="copyCouponCode('EGA99')"
                        class="tw-bg-blue-600 tw-text-white tw-text-xs tw-px-3 tw-py-1 tw-rounded-sm hover:tw-bg-blue-700 tw-transition">
                        Sao chép
                    </button>
                    <a class="tw-text-xs tw-text-gray-700 hover:tw-underline" href="#">
                        Điều kiện
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="tw-flex tw-max-w-xs tw-w-full tw-bg-white tw-shadow-md tw-rounded-md">
            <div class="tw-left-edge tw-flex tw-items-center tw-justify-center">
                <i class="fa-solid fa-ticket tw-text-white tw-text-2xl"></i>
            </div>
            <div class="tw-flex tw-flex-col tw-justify-center tw-px-4 tw-py-4 tw-flex-1">
                <p class="tw-text-sm tw-text-blue-600 tw-font-semibold tw-text-center">
                    NHẬP MÃ:
                    <span class="tw-font-normal">EGAFREESHIP</span>
                </p>
                <p class="tw-text-xs tw-text-gray-500 tw-text-center tw-mt-1 tw-leading-tight">
                    Miễn phí ship cho đơn hàng tối thiểu 500.000đ
                </p>
                <div class="tw-mt-3 tw-flex tw-items-center tw-justify-between">
                    <button @click="copyCouponCode('EGAFREESHIP')"
                        class="tw-bg-blue-600 tw-text-white tw-text-xs tw-px-3 tw-py-1 tw-rounded-sm hover:tw-bg-blue-700 tw-transition">
                        Sao chép
                    </button>
                    <a class="tw-text-xs tw-text-gray-700 hover:tw-underline" href="#">
                        Điều kiện
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const copyCouponCode = async (code) => {
    try {
        await navigator.clipboard.writeText(code)
        // Có thể thêm toast notification ở đây
        console.log(`Đã sao chép mã: ${code}`)
    } catch (err) {
        console.error('Không thể sao chép mã:', err)
    }
}
</script>

<style scoped>
/* Custom shape for left edge with small semicircle cutouts */
.tw-left-edge {
    position: relative;
    width: 64px;
    background-color: #1565d8;
    clip-path: polygon(0 0,
            100% 0,
            100% 100%,
            0 100%,
            0 85%,
            10% 85%,
            10% 70%,
            0 70%,
            0 55%,
            10% 55%,
            10% 40%,
            0 40%,
            0 25%,
            10% 25%,
            10% 10%,
            0 10%);
}

/* The above polygon creates a scalloped left edge with 4 semicircle cutouts */
</style>
