<template>
    <DashboardContent />
</template>

<script setup>
import DashboardContent from '../../components/admin/dashboard/DashboardContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Bảng điều khiển | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Bảng điều khiển"
        }
    ]
})
</script>