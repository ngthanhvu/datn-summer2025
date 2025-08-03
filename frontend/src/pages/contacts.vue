<template>
    <ContactContent />
</template>

<script setup>
import ContactContent from '../components/site/ContactContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Liên hệ | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Lien hệ"
        }
    ]
})
</script>
