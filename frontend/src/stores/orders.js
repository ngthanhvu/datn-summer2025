import { defineStore } from "pinia";
import { ref } from "vue";
import { useOrder } from "../composable/useOrder";

export const useOrderStore = defineStore("order", () => {
    const orders = ref([]);
    const loading = ref(false);
    const error = ref(null);

    const { getAllOrders } = useOrder();

    const fetchOrders = async () => {
        loading.value = true
        try {
            const data = await getAllOrders();
            orders.value = data;
        } catch (err) {
            error.value = err
        } finally {
            loading.value = false
        }
    }

    return { orders, loading, error, fetchOrders };
});