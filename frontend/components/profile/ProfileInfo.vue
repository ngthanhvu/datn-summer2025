<template>
    <div class="tw-bg-white tw-p-6 tw-rounded tw-shadow">
        <h2 class="tw-font-bold tw-text-lg tw-mb-4">Thông tin cá nhân</h2>
        <div class="tw-space-y-4">
            <div class="tw-flex tw-items-center tw-gap-4 tw-mb-6">
                <div class="tw-relative">
                    <img src="https://placehold.co/50" alt="Avatar"
                        class="tw-w-24 tw-h-24 tw-rounded-full tw-object-cover" />
                    <button
                        class="tw-absolute tw-bottom-0 tw-right-0 tw-bg-[#81AACC] tw-text-white tw-p-2 tw-rounded-full hover:tw-bg-[#5b98ca]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-4 tw-h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                </div>
                <div>
                    <h3 class="tw-font-medium">Ảnh đại diện</h3>
                    <p class="tw-text-sm tw-text-gray-500">JPG, GIF hoặc PNG. Tối đa 2MB.</p>
                </div>
            </div>
            <div>
                <label class="tw-block tw-font-medium tw-mb-1">Tên người dùng</label>
                <input class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded" placeholder="Nhập tên người dùng" />
            </div>
            <div>
                <label class="tw-block tw-font-medium tw-mb-1">Email</label>
                <input class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded" placeholder="Nhập email" />
            </div>
            <div>
                <label class="tw-block tw-font-medium tw-mb-1">Số điện thoại</label>
                <input type="tel" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded"
                    placeholder="Nhập số điện thoại" />
            </div>
            <button class="tw-mt-2 tw-bg-[#81AACC] tw-text-white tw-px-4 tw-py-2 tw-rounded hover:tw-bg-[#5b98ca]">
                Lưu thay đổi
            </button>
        </div>
    </div>
</template>

<script setup>
// Component logic here
</script>