<template>
    <div class="flex flex-col min-h-screen">
        <HeaderHome />
        <main class="flex-grow bg-[#F5F5FA]">
            <router-view />
        </main>
        <FooterHome />
        <ChatWidget :is-authenticated="isAuthenticated" />
        <AIChatbot />
        <CookieConsent />
        <BannerPopup />
        <PurchaseToast />
        <BackToTop />
    </div>
</template>
<script setup>
import HeaderHome from './HeaderHome.vue'
import FooterHome from './FooterHome.vue'
import ChatWidget from './ChatWidget.vue'
import AIChatbot from '../components/AIChatbot.vue'
import CookieConsent from './CookieConsent.vue';
import BannerPopup from '../components/BannerPopup.vue';
import PurchaseToast from '../components/PurchaseToast.vue';
import BackToTop from '../components/BackToTop.vue';

import { useAuth } from '../composable/useAuth';

const { isAuthenticated } = useAuth()
</script>