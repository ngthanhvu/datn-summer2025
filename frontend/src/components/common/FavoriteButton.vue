<template>
  <button class="favorite-button cursor-pointer" @click="toggleFavoriteStatus"
    :class="{ 'is-favorite': isFavoriteState }">
    <span class="sr-only">Yêu thích</span>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
      :class="{ 'fill-current': isFavoriteState }">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
    </svg>
  </button>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useProducts } from "../../composable/useProducts";
import { useWishlistStore } from "../../stores/wishlist";
import { push } from "notivue";

const props = defineProps({
  productSlug: { type: String, required: true },
});
const emit = defineEmits(["favorite-changed"]);

const { toggleFavorite, isFavorite } = useProducts();
const wishlistStore = useWishlistStore();
const isFavoriteState = ref(false);

const checkFavorite = async () => {
  try {
    isFavoriteState.value = await isFavorite(props.productSlug);
  } catch (error) {
    console.error("Error checking favorite status:", error);
    isFavoriteState.value = false;
  }
};

onMounted(checkFavorite);

const toggleFavoriteStatus = async () => {
  try {
    const newState = await toggleFavorite(props.productSlug);
    isFavoriteState.value = newState;
    emit("favorite-changed", newState);

    // Refresh wishlist count in store
    await wishlistStore.refreshWishlist();

    push.success(
      newState
        ? "Đã thêm vào danh sách yêu thích!"
        : "Đã xóa khỏi danh sách yêu thích!"
    );
  } catch (error) {
    push.error("Có lỗi xảy ra, vui lòng thử lại!");
    console.error("Error toggling favorite:", error);
  }
};
</script>

<style scoped>
.favorite-button {
  padding: 0.5rem;
  border-radius: 9999px;
  transition: background-color 0.2s ease, color 0.2s ease;
}

.favorite-button:hover {
  background-color: #f3f4f6;
}

.favorite-button.is-favorite {
  color: #ef4444;
}
</style>
