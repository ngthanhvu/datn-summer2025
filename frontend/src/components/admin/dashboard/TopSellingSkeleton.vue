<template>
    <div class="top-selling bg-white rounded-2xl p-6 animate-pulse">
        <h3 class="mb-4 font-bold text-lg">Top Sản Phẩm Bán Chạy</h3>
        <ul class="space-y-4">
            <li v-for="i in 5" :key="i" class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gray-200 rounded-xl"></div>
                <div class="flex-1 min-w-0">
                    <div class="h-4 w-32 bg-gray-200 rounded mb-2"></div>
                    <div class="flex items-center gap-2">
                        <div class="h-4 w-16 bg-gray-200 rounded"></div>
                        <div class="h-4 w-10 bg-gray-200 rounded"></div>
                    </div>
                </div>
                <div class="flex items-center gap-1 min-w-[60px]">
                    <div class="w-4 h-4 rounded-full bg-gray-200 border-2 border-gray-200"></div>
                    <div class="w-4 h-4 rounded-full bg-gray-200 border-2 border-gray-200"></div>
                    <div class="w-4 h-4 rounded-full bg-gray-200 border-2 border-gray-200"></div>
                    <div class="ml-1 h-4 w-6 bg-gray-200 rounded"></div>
                </div>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.top-selling {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
    padding: 1.5rem;
}
</style>