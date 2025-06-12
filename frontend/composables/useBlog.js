import axios from "axios";
import { ref } from "vue";

export const useBlog = () => {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.public.apiBaseUrl || 'http://127.0.0.1:8000/api';

    const API = axios.create({
        baseURL: apiBaseUrl
    });

    const blogs = ref([]);
    const blog = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const pagination = ref(null);

    const fetchBlogs = async (page = 1, filters = {}) => {
        loading.value = true;
        error.value = null;
        try {
            const params = { page, ...filters };
            const response = await API.get('/api/blogs', { params });
            
            if (response.data.success) {
                blogs.value = response.data.data.data;
                pagination.value = {
                    current_page: response.data.data.current_page,
                    last_page: response.data.data.last_page,
                    per_page: response.data.data.per_page,
                    total: response.data.data.total,
                    from: response.data.data.from,
                    to: response.data.data.to
                };
            } else {
                throw new Error(response.data.message || 'Failed to fetch blogs');
            }
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'An error occurred';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const fetchBlog = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await API.get(`/api/blogs/${id}`);
            if (response.data.success) {
                blog.value = response.data.data;
            } else {
                throw new Error(response.data.message || 'Failed to fetch blog');
            }
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'An error occurred';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const createBlog = async (blogData) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await API.post('/api/blogs', blogData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
            if (response.data.success) {
                return response.data;
            } else {
                throw new Error(response.data.message || 'Failed to create blog');
            }
        } catch (err) {
            const errorMessage = err.response?.data?.message || err.message || 'An error occurred';
            const validationErrors = err.response?.data?.errors || null;
            error.value = errorMessage;
            const errorObj = new Error(errorMessage);
            if (validationErrors) {
                errorObj.errors = validationErrors;
            }
            throw errorObj;
        } finally {
            loading.value = false;
        }
    };

    const updateBlog = async (id, blogData) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await API.put(`/api/blogs/${id}`, blogData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
            if (response.data.success) {
                return response.data;
            } else {
                throw new Error(response.data.message || 'Failed to update blog');
            }
        } catch (err) {
            const errorMessage = err.response?.data?.message || err.message || 'An error occurred';
            const validationErrors = err.response?.data?.errors || null;
            error.value = errorMessage;
            const errorObj = new Error(errorMessage);
            if (validationErrors) {
                errorObj.errors = validationErrors;
            }
            throw errorObj;
        } finally {
            loading.value = false;
        }
    };

    const deleteBlog = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await API.delete(`/api/blogs/${id}`);
            if (response.data.success) {
                return response.data;
            } else {
                throw new Error(response.data.message || 'Failed to delete blog');
            }
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'An error occurred';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const clearError = () => {
        error.value = null;
    };

    const resetState = () => {
        blogs.value = [];
        blog.value = null;
        loading.value = false;
        error.value = null;
        pagination.value = null;
    };

    return {
        blogs,
        blog,
        loading,
        error,
        pagination,
        fetchBlogs,
        fetchBlog,
        createBlog,
        updateBlog,
        deleteBlog,
        clearError,
        resetState
    };
};