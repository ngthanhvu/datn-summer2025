import { defineStore } from "pinia";
import { ref } from "vue";
import { useBlog } from "../composable/useBlogs";

export const useBlogStore = defineStore("blog", () => {
    const blogs = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const pagination = ref(null);

    const { fetchBlogs } = useBlog();

    const getBlogs = async (page = 1, perPage = 6) => {
        loading.value = true;
        error.value = null;
        try {
            const res = await fetchBlogs(page, perPage);

            blogs.value = res.data.data || [];

            pagination.value = {
                current_page: res.data.current_page,
                last_page: res.data.last_page,
                per_page: res.data.per_page,
                total: res.data.total
            };
        } catch (err) {
            error.value = err;
            blogs.value = [];
            pagination.value = null;
        } finally {
            loading.value = false;
        }
    }

    return { blogs, loading, error, pagination, getBlogs };
});
