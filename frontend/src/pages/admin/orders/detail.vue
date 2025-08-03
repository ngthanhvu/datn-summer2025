<template>
    <OrderDetailPage :orderId="orderId" />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import OrderDetailPage from '../../../components/admin/orders/OrderDetailPage.vue'
import { useHead } from '@vueuse/head'

const route = useRoute()
const router = useRouter()
const orderId = ref(route.params.id)

useHead({
    title: `Chi tiết đơn hàng #${orderId.value} | DEVGANG`,
    meta: [
        {
            name: "description",
            content: "Chi tiết đơn hàng"
        }
    ]
})

onMounted(() => {
    if (!orderId.value) {
        router.push('/admin/orders')
    }
})
</script>

<style lang="scss" scoped></style>