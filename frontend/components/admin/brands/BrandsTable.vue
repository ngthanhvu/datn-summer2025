<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-overflow-x-auto">
            <table class="tw-w-full">
                <thead>
                    <tr class="tw-border-b tw-bg-gray-50">
                        <th class="tw-px-4 tw-py-3 tw-text-left">ID</th>
                        <th class="tw-px-4 tw-py-3 tw-text-left">Logo</th>
                        <th class="tw-px-4 tw-py-3 tw-text-left">Tên thương hiệu</th>
                        <th class="tw-px-4 tw-py-3 tw-text-left">Mô tả</th>
                        <th class="tw-px-4 tw-py-3 tw-text-left">Danh mục cha</th>
                        <th class="tw-px-4 tw-py-3 tw-text-left">Trạng thái</th>
                        <th class="tw-px-4 tw-py-3 tw-text-left">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="brands.length > 0">
                        <BrandTableRow v-for="brand in brands" :key="brand.id" :brand="brand" :brands="brands"
                            @delete="$emit('delete', $event)" />
                    </template>
                    <BrandTableEmpty v-else />
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import BrandTableRow from './BrandTableRow.vue'
import BrandTableEmpty from './BrandTableEmpty.vue'

const props = defineProps({
    brands: {
        type: Array,
        required: true
    }
})

defineEmits(['delete'])
</script>