<template>
    <WishlistContent />
</template>

<script setup>
import WishlistContent from '~/components/wishlist/WishlistContent.vue'

useHead({
    title: 'Danh sách yêu thích - DEVGANG',
    meta: [
        { name: 'description', content: 'Danh sách yêu thích - DEVGANG' },
    ],
})
</script>

<style scoped>
:deep(.card-image) {
    height: 400px;
    /* Make product images taller */
    object-fit: cover;
}
</style>