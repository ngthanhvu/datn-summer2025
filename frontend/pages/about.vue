<template>
    <AboutContent />
</template>

<script setup>
import AboutContent from '~/components/about/AboutContent.vue';

useHead({
    title: 'Giới thiệu - DEVGANG',
    meta: [
        { name: 'description', content: 'EGA MEN' },
    ],
})
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');

body {
    font-family: 'Inter', sans-serif;
}
</style>