<template>
    <div>
        <div class="tw-flex tw-justify-between tw-items-start tw-mb-2">
            <div>
                <div class="tw-font-semibold tw-text-lg tw-text-gray-800">{{ review.user?.name || 'Khách hàng' }}</div>
                <div class="tw-flex tw-items-center tw-mt-1">
                    <span v-for="star in 5" :key="star" class="tw-text-xl"
                        :class="star <= review.rating ? 'tw-text-yellow-500' : 'tw-text-gray-300'">★</span>
                </div>
            </div>
            <img :src="getUserAvatar(review.user)" :alt="review.user?.name || 'User'"
                class="tw-w-16 tw-h-16 tw-rounded tw-object-cover" @error="handleImageError" />
        </div>
        <div class="tw-text-gray-700 tw-mt-2">
            {{ review.content }}
        </div>
    </div>
</template>

<script setup>
defineProps({ review: Object })

const getUserAvatar = (user) => {
    if (user?.avatar) {
        return user.avatar.startsWith('http') ? user.avatar : `https://placehold.co/100x100?text=${user.name?.charAt(0) || 'U'}`
    }
    return `https://placehold.co/100x100?text=${user?.name?.charAt(0) || 'U'}`
}

const handleImageError = (event) => {
    const alt = event.target.alt || 'Image'
    event.target.src = `https://placehold.co/100x100?text=${alt.charAt(0)}`
}
</script>