import { ref, onMounted } from 'vue';

export const useCaptcha = () => {
    const captchaToken = ref(null)
    const widgetId = ref(null)

    const renderCaptcha = () => {
        if (typeof window !== 'undefined' && window.turnstile) {
            widgetId.value = window.turnstile.render('#cf-turnstile', {
                sitekey: useRuntimeConfig().public.turnstileSiteKey,
                callback: (t) => (token.value = t),
                'error-callback': () => (token.value = null),
            })
        }
    }

    onMounted(() => {
        renderCaptcha()
    })

    return {
        captchaToken,
        renderCaptcha
    }
}