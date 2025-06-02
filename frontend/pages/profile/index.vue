<template>
    <div class="tw-flex tw-gap-6 tw-p-6 container">
        <ProfileSidebar :selected="selectedTab" @select="selectedTab = $event" />
        <div class="tw-flex-1 tw-space-y-6">
            <template v-if="selectedTab === 'info'">
                <ProfileInfo />
                <ChangePassword />
            </template>
            <ProfileAddress v-if="selectedTab === 'address'" />
            <ProfileOrders v-if="selectedTab === 'orders'" />
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import ProfileSidebar from '~/components/profile/ProfileSidebar.vue'
import ProfileInfo from '~/components/profile/ProfileInfo.vue'
import ChangePassword from '~/components/profile/ChangePassword.vue'
import ProfileAddress from '~/components/profile/ProfileAddress.vue'
import ProfileOrders from '~/components/profile/ProfileOrders.vue'

const selectedTab = ref('info')
</script>