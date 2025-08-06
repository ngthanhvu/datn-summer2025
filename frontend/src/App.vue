<template>
  <router-view />
  <Notivue v-slot="item">
    <Notification :item="item" :theme="pastelTheme" />
  </Notivue>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import { Notivue, Notification, pastelTheme } from 'notivue'
import { useSettings } from './composable/useSettingsApi'

const defaultFavicon = 'https://cdn-img.upanhlaylink.com/img/image_202505261a100993dadd1e94d860ec123578e3cf.jpg'
const defaultLogo = '/logo2.png'

const siteLogo = ref(defaultLogo)

const { settings, fetchSettings } = useSettings()

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
  const script = document.createElement('script')
  script.async = true
  script.defer = true
  document.head.appendChild(script)

  const res = await fetchSettings(false)
  updateFavicon(res.siteIcon || defaultFavicon)
  updateLogo(res.logo || defaultLogo)
})

watch(() => settings.value.siteIcon, updateFavicon)
watch(() => settings.value.logo, updateLogo)
</script>