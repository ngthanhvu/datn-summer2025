<template>
    <div>
        <div class="flex justify-between items-start mb-2">
            <div>
                <div class="font-semibold text-lg text-gray-800">
                    {{ getUserName(review.user) }}
                </div>
                <div class="flex items-center mt-1">
                    <span v-for="star in 5" :key="star" class="text-xl"
                        :class="star <= review.rating ? 'text-yellow-500' : 'text-gray-300'">★</span>
                </div>
            </div>
            <img :src="getUserAvatar(review.user)" :alt="getUserName(review.user)"
                class="w-16 h-16 rounded object-cover" @error="handleImageError" />
        </div>
        <div class="text-gray-700 mt-2">
            {{ review.content }}
        </div>
    </div>
</template>

<script setup>
defineProps({ review: Object })

const getUserName = (user) => {
    if (!user) return 'Khách hàng'
    return user.username || user.name || user.email?.split('@')[0] || 'Khách hàng'
}

const getUserAvatar = (user) => {
    if (!user) {
        return `https://placehold.co/100x100?text=K`
    }

    if (user.avatar) {
        if (user.avatar.startsWith('http')) {
            return user.avatar
        }
        if (user.avatar.startsWith('/storage/')) {
            return `${import.meta.env.VITE_API_URL}${user.avatar}`
        }
        return `${import.meta.env.VITE_API_URL}/storage/${user.avatar}`
    }

    const name = getUserName(user)
    return `https://placehold.co/100x100?text=${name.charAt(0).toUpperCase()}`
}

const handleImageError = (event) => {
    const alt = event.target.alt || 'User'
    const randomSeed = Math.random().toString(36).substring(7)
    event.target.src = `https://avatar.iran.liara.run/public?name=${encodeURIComponent(alt)}&seed=${randomSeed}`
}
</script>
