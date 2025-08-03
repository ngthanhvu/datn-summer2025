<template>
    <div>
        <div class="flex justify-between items-start mb-2">
            <div>
                <div class="font-semibold text-lg text-gray-800">
                    {{ review.user?.name || 'Khách hàng' }}
                </div>
                <div class="flex items-center mt-1">
                    <span v-for="star in 5" :key="star" class="text-xl"
                        :class="star <= review.rating ? 'text-yellow-500' : 'text-gray-300'">★</span>
                </div>
            </div>
            <img :src="getUserAvatar(review.user)" :alt="review.user?.name || 'User'"
                class="w-16 h-16 rounded object-cover" @error="handleImageError" />
        </div>
        <div class="text-gray-700 mt-2">
            {{ review.content }}
        </div>
    </div>
</template>

<script setup>
defineProps({ review: Object })

const getUserAvatar = (user) => {
    if (user?.avatar) {
        return user.avatar.startsWith('http')
            ? user.avatar
            : `https://placehold.co/100x100?text=${user.name?.charAt(0) || 'U'}`
    }
    return `https://placehold.co/100x100?text=${user?.name?.charAt(0) || 'U'}`
}

const handleImageError = (event) => {
    const alt = event.target.alt || 'Image'
    event.target.src = `https://placehold.co/100x100?text=${alt.charAt(0)}`
}
</script>
