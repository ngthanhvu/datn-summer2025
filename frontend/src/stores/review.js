import { defineStore } from "pinia";
import { ref } from "vue";
import { useReview } from "../composable/useReview";

export const useReviewStore = defineStore("review", () => {
    const reviews = ref([]);
    const loading = ref(false);
    const error = ref(null);

    const { fetchLatestReview } = useReview();

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