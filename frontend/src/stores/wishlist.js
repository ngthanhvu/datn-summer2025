import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useProducts } from '../composable/useProducts'

export const useWishlistStore = defineStore('wishlist', () => {
    const { getFavoriteProducts } = useProducts()
    const wishlistItems = ref([])
    const loading = ref(false)

    const wishlistCount = computed(() => wishlistItems.value.length)

    const fetchWishlist = async () => {
        loading.value = true
        try {
            const favorites = await getFavoriteProducts()
            wishlistItems.value = favorites
        } catch (error) {
            console.error('Error fetching wishlist:', error)
            wishlistItems.value = []
        } finally {
            loading.value = false
        }
    }

    const refreshWishlist = async () => {
        await fetchWishlist()
    }

    return {
        wishlistItems,
        wishlistCount,
        loading,
        fetchWishlist,
        refreshWishlist
    }
}) 