<template>
  <NuxtLayout :site-logo="siteLogo">
    <NuxtPage />
  </NuxtLayout>
</template>

<script setup>
import { useHead } from '#app'
import { useSettings } from '~/composables/useSettingsApi'
import { ref, onMounted, watch } from 'vue'

const { settings, fetchSettings } = useSettings()

useHead({
  script: [
    {
      src: "https://challenges.cloudflare.com/turnstile/v0/api.js",
      async: true,
      defer: true,
    },
  ],
})

const defaultFavicon = 'https://cdn-img.upanhlaylink.com/img/image_202505261a100993dadd1e94d860ec123578e3cf.jpg'
const defaultLogo = '/logo.png'

const siteLogo = ref(defaultLogo)

const updateFavicon = (url) => {
  if (!url) return
  let link = document.querySelector("link[rel~='icon']")
  if (!link) {
    link = document.createElement('link')
    link.rel = 'icon'
    document.head.appendChild(link)
  }
  link.href = url
}

const updateLogo = (url) => {
  if (url) siteLogo.value = url
}

onMounted(async () => {
  const res = await fetchSettings(false)
  console.log('SETTINGS FROM API:', res)
  updateFavicon(res.siteIcon || defaultFavicon)
  updateLogo(res.logo || defaultLogo)
})



watch(() => settings.value.siteIcon, updateFavicon)
watch(() => settings.value.logo, updateLogo)
</script>
