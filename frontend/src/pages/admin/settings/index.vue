<template>
    <SettingsContent />
</template>

<script setup>
import SettingsContent from '../../../components/admin/settings/SettingsContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Cài đặt trang web | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Cài đặt trang web"
        }
    ]
})
</script>