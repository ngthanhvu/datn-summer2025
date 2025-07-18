<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>

<script setup>
import { useHead } from '#app'
import { useSettings } from '~/composables/useSettingsApi'
import { onMounted, watch } from 'vue'
import { useSiteStore } from '~/stores/useSiteStore'

const siteStore = useSiteStore()
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

onMounted(async () => {
  try {
    const res = await fetchSettings(false)
    updateFavicon(res?.siteIcon || defaultFavicon)
    siteStore.setSiteLogo(res?.logo || defaultLogo)
  } catch (err) {
    console.error('Fetch settings failed:', err)
    updateFavicon(defaultFavicon)
    siteStore.setSiteLogo(defaultLogo)
  }
})

// Tự động cập nhật favicon nếu settings thay đổi
watch(() => settings.value.siteIcon, updateFavicon)
watch(() => settings.value.logo, siteStore.setSiteLogo)
</script>
