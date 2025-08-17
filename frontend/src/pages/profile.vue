<template>
    <div class="min-h-screen bg-gray-50">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col lg:flex-row gap-6">
                <ProfileSidebar :selected="selectedTab" @select="selectedTab = $event" />
                <div class="flex-1 space-y-6">
                    <template v-if="selectedTab === 'info'">
                        <ProfileInfo />
                        <ProfileChangePassword />
                    </template>
                    <ProfileAddress v-if="selectedTab === 'address'" />
                    <ProfileOrders v-if="selectedTab === 'orders'" />
                    <ProfileCoupon v-if="selectedTab === 'coupon'" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import ProfileSidebar from '../components/profile/ProfileSidebar.vue'
import ProfileInfo from '../components/profile/ProfileInfo.vue'
import ProfileChangePassword from '../components/profile/ProfileChangePassword.vue'
import ProfileAddress from '../components/profile/ProfileAddress.vue'
import ProfileOrders from '../components/profile/ProfileOrders.vue'
import ProfileCoupon from '../components/profile/ProfileCoupon.vue'

const route = useRoute()
const router = useRouter()

const selectedTab = ref(route.query.tab || 'info')

watch(selectedTab, (val) => {
    router.replace({ query: { ...route.query, tab: val } })
})

watch(
    () => route.query.tab,
    (val) => {
        if (val) selectedTab.value = val
    }
)
</script>
