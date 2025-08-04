<template>
    <div class="flex flex-col min-h-screen">
        <HeaderHome />
        <main class="flex-grow bg-[#F5F5FA]">
            <router-view />
        </main>
        <FooterHome />
        <ChatWidget v-if="isAuthenticated" />
        <CookieConsent />
    </div>
</template>
<script setup>
import HeaderHome from './HeaderHome.vue'
import FooterHome from './FooterHome.vue'
import ChatWidget from './ChatWidget.vue'
import CookieConsent from './CookieConsent.vue';

import { useAuth } from '../composable/useAuth';

const { isAuthenticated } = useAuth()
</script>