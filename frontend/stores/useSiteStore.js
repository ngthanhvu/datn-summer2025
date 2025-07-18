import { defineStore } from 'pinia'

export const useSiteStore = defineStore('site', {
    state: () => ({
        siteLogo: '/logo.png'
    }),
    actions: {
        setSiteLogo(url) {
            this.siteLogo = url || '/logo.png'
        }
    }
})
