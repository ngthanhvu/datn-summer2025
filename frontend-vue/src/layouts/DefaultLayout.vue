<template>
    <div class="flex flex-col min-h-screen">
        <HeaderHome />
        <main class="flex-grow bg-[#F5F5FA]">
            <router-view />
        </main>
        <FooterHome />
        <ChatWidget v-if="isAuthenticated" />
    </div>
</template>
<script setup>
import HeaderHome from '../components/Layouts/HeaderHome.vue'
import FooterHome from '../components/Layouts/FooterHome.vue';
import ChatWidget from '../components/layouts/ChatWidget.vue';

import { useAuth } from '../composable/useAuth';

const { isAuthenticated } = useAuth()
</script>