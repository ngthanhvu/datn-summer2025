import { defineStore } from "pinia";
import { ref } from "vue";
import { useBlog } from "../composable/useBlogs";

export const useBlogStore = defineStore("blog", () => {
    const blogs = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const pagination = ref(null);

    const { fetchBlogs } = useBlog();

    const getBlogs = async () => {
        loading.value = true;
        error.value = null;
        try {
            const res = await fetchBlogs();
            blogs.value = res.data.data || [];
        } catch (err) {
            error.value = err;
            blogs.value = [];
        } finally {
            loading.value = false;
        }
    }

    return { blogs, loading, error, pagination, getBlogs };
});