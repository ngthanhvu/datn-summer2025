<template>
  <button
    class="favorite-button"
    @click="toggleFavoriteStatus"
    :class="{ 'is-favorite': isFavoriteState }"
  >
    <span class="sr-only">Yêu thích</span>
    <svg
      xmlns="http://www.w3.org/2000/svg"
      class="tw-h-6 tw-w-6"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      :class="{ 'tw-fill-current': isFavoriteState }"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
      />
    </svg>
  </button>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useProducts } from '@/composables/useProducts'

const props = defineProps({
  productSlug: {
    type: String,
    required: true
  }
})

const { toggleFavorite, isFavorite } = useProducts()
const isFavoriteState = ref(false)

onMounted(async () => {
  try {
    isFavoriteState.value = await isFavorite(props.productSlug)
  } catch (error) {
    console.error('Error checking favorite status:', error)
  }
})

const toggleFavoriteStatus = async () => {
  try {
    await toggleFavorite(props.productSlug)
    isFavoriteState.value = !isFavoriteState.value

    if (isFavoriteState.value) {
      alert(' Đã thêm vào danh sách yêu thích!')
    } else {
      alert(' Đã xóa khỏi danh sách yêu thích!')
    }
  } catch (error) {
    console.error('Error toggling favorite:', error)
    alert('Đã xảy ra lỗi, vui lòng thử lại!')
  }
}
</script>

<style scoped>
.favorite-button {
  @apply tw-p-2 tw-rounded-full hover:tw-bg-gray-100 tw-transition-colors tw-duration-200;
}
.favorite-button.is-favorite {
  @apply tw-text-red-500;
}
</style>
