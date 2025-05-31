<template>
    <div class="product-images">
        <div class="main-image">
            <img :src="mainImage?.image_path" :alt="mainImage?.image_path" class="w-16 h-16 object-cover rounded" />
        </div>
        <div v-if="subImages.length > 0" class="sub-images mt-2">
            <div class="flex gap-1">
                <img v-for="image in subImages" :key="image.id" :src="image.image_path" :alt="image.image_path"
                    class="w-8 h-8 object-cover rounded cursor-pointer hover:opacity-75"
                    @click="handleImageClick(image)" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    images: {
        type: Array,
        required: true
    }
})

const mainImage = computed(() => {
    return props.images.find(img => img.is_main === 1)
})

const subImages = computed(() => {
    return props.images.filter(img => img.is_main === 0)
})

const emit = defineEmits(['imageClick'])

const handleImageClick = (image) => {
    emit('imageClick', image)
}
</script>

<style scoped>
.product-images {
    min-width: 100px;
}
</style>