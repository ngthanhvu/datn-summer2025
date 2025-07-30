import { createApp } from 'vue'
import { createHead } from '@vueuse/head'
import { createPinia } from 'pinia'
import { createNotivue } from 'notivue'

import './style.css'
import 'notivue/notification.css'
import 'notivue/animations.css'
import App from './App.vue'
import router from './router'

const pinia = createPinia()
const notivue = createNotivue({ position: 'top-right' })
const app = createApp(App)
const head = createHead()

app.use(pinia)
app.use(notivue)
app.use(head)
app.use(router)
app.mount('#app')