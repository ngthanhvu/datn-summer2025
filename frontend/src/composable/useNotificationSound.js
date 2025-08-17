import { ref, onMounted } from 'vue'

export function useNotificationSound() {
    const audio = ref(null)
    const isEnabled = ref(true)
    const volume = ref(0.5)

    const initAudio = () => {
        if (typeof window !== 'undefined') {
            audio.value = new Audio('/noti.mp3')
            audio.value.preload = 'auto'
            audio.value.volume = volume.value
        }
    }

    const playSound = async () => {
        if (!isEnabled.value || !audio.value) return

        try {
            audio.value.currentTime = 0
            await audio.value.play()
        } catch (error) {
            console.log('Không thể phát âm thanh thông báo:', error)
        }
    }

    const setVolume = (newVolume) => {
        volume.value = Math.max(0, Math.min(1, newVolume))
        if (audio.value) {
            audio.value.volume = volume.value
        }
    }

    const toggleSound = () => {
        isEnabled.value = !isEnabled.value
    }

    const enableSound = () => {
        isEnabled.value = true
    }

    const disableSound = () => {
        isEnabled.value = false
    }

    onMounted(() => {
        initAudio()
    })

    return {
        audio,
        isEnabled,
        volume,
        playSound,
        setVolume,
        toggleSound,
        enableSound,
        disableSound
    }
} 