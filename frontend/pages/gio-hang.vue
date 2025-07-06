<template>
    <CartContent />
</template>

<script setup>
import CartContent from '~/components/cart/CartContent.vue';

useHead({
    title: 'Giỏ hàng - DEVGANG',
    meta: [
        { name: 'description', content: 'Giỏ hàng - DEVGANG' },
    ],
})
</script>

<style>
/* Custom scrollbar for quantity input */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>