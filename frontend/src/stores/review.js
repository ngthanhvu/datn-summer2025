import { defineStore } from "pinia";
import { ref } from "vue";
import { useReviews } from "../composable/useReviews";

export const useReviewStore = defineStore("review", () => {
    const reviews = ref([]);
    const loading = ref(false);
    const error = ref(null);

    const { fetchLatestReview } = useReviews();

    const getLatestReview = async () => {
        loading.value = true;
        error.value = null;
        try {
            const res = await fetchLatestReview();
            reviews.value = res.data || [];
        } catch (err) {
            error.value = err;
            reviews.value = [];
        } finally {
            loading.value = false;
        }
    };


    return { reviews, loading, error, getLatestReview };
})