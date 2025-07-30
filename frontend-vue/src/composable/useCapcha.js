import { ref, onMounted } from 'vue';

export const useCaptcha = () => {
    const captchaToken = ref(null)
    const widgetId = ref(null)

    const renderCaptcha = () => {
        if (typeof window !== 'undefined' && window.turnstile) {
            widgetId.value = window.turnstile.render('#cf-turnstile', {
                sitekey: import.meta.env.VITE_TURNSTILE_SITE_KEY, // Sửa lại cho đúng Vite
                callback: (t) => (captchaToken.value = t),
                'error-callback': () => (captchaToken.value = null),
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