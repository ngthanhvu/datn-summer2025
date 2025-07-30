import { defineStore } from "pinia";
import { ref } from "vue";
import { useBrand } from "../composable/useBrand";

export const useBrandStore = defineStore("brand", () => {
    const brands = ref([]);
    const loading = ref(false);
    const error = ref(null);

    const { getBrands } = useBrand();

    const fetchBrands = async () => {
        loading.value = true;
        try {
            brands.value = await getBrands();
        } catch (err) {
            error.value = err;
        } finally {
            loading.value = false;
        }
    };

    return { brands, loading, error, fetchBrands };
});