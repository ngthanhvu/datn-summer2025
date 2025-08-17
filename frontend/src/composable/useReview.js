import { ref } from 'vue';
import axios from 'axios';
import api from '../utils/api'

// Sử dụng instance axios chung từ utility
const API = api

export const useReview = () => {
    const reviews = ref([]);
    const loading = ref(false);
    const error = ref(null);

    const fetchLatestReview = async () => {
        loading.value = true;
        error.value = null;
        try {
            const response = await API.get('/api/reviews/latest');
            reviews.value = response.data;
            return reviews.value;
        } catch (err) {
            error.value = err;
        } finally {
            loading.value = false;
        }
    };

    return { reviews, loading, error, fetchLatestReview };
}