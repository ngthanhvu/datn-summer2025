<template>
    <ContactsContent />
</template>

<script setup>
import ContactsContent from '../../../components/admin/contacts/ContactsContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Quản lý liên hệ | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Quản lý liên hệ"
        }
    ]
})
</script>