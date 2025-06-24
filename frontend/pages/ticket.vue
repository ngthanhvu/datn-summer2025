<template>
    <div class="tw-bg-gray-50 tw-flex tw-items-center tw-justify-center tw-min-h-screen tw-p-4">
        <div class="tw-max-w-sm tw-w-full tw-bg-white tw-shadow-md tw-rounded-sm tw-flex">
            <div
                class="tw-flex-shrink-0 tw-bg-blue-600 tw-flex tw-items-center tw-justify-center tw-p-4 tw-rounded-l-sm">
                <img src="https://www.svgrepo.com/show/525120/ticket-sale.svg"
                    alt="Blue coupon ticket with white delivery truck icon and text 'Freeship' vertically"
                    class="tw-w-16 tw-h-16 tw-object-contain" width="64" height="64" />
            </div>
            <div class="tw-flex tw-flex-col tw-justify-center tw-px-4 tw-py-3 tw-flex-grow">
                <p class="tw-text-sm tw-font-semibold tw-text-blue-600">
                    NHẬP MÃ:
                    <span class="tw-font-normal">EGAFREESHIP</span>
                </p>
                <p class="tw-text-xs tw-text-gray-500 tw-mt-1 tw-leading-tight tw-max-w-[180px]">
                    Miễn phí ship cho đơn hàng tối thiểu 500.000đ
                </p>
                <div class="tw-flex tw-items-center tw-mt-3 tw-space-x-6">
                    <button type="button"
                        class="tw-bg-blue-600 tw-text-white tw-text-xs tw-font-medium tw-px-3 tw-py-1 tw-rounded-sm hover:tw-bg-blue-700 tw-transition">
                        Sao chép
                    </button>
                    <a href="#" class="tw-text-xs tw-text-gray-600 hover:tw-underline">
                        Điều kiện
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// Nếu cần copy mã hoặc xử lý tương tác, có thể thêm vào đây
</script>

<style scoped>
/* Không cần CSS nếu chỉ dùng Tailwind, có thể để trống hoặc dùng thêm nếu cần */
</style>