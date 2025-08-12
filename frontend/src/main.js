import { createApp } from 'vue'
import { createHead } from '@vueuse/head'
import { createPinia } from 'pinia'
import { createNotivue } from 'notivue'
import axios from 'axios'

import './style.css'
import 'notivue/notification.css'
import 'notivue/animations.css'
import App from './App.vue'
import router from './router'

axios.defaults.baseURL = import.meta.env.VITE_API_BASE_URL
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['Accept'] = 'application/json'

axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

const pinia = createPinia()
const notivue = createNotivue({ position: 'top-right' })
const app = createApp(App)
const head = createHead()

app.use(pinia)
app.use(notivue)
app.use(head)
app.use(router)
app.mount('#app')