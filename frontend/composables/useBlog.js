import axios from "axios";
import { ref } from "vue";

export const useBlog = () => {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.public.apiBaseUrl || 'http://127.0.0.1:8000/api';

    const API = axios.create({
        baseURL: apiBaseUrl
    });

    API.interceptors.request.use((config) => {
        const token = localStorage.getItem('auth_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    }, (error) => {
        return Promise.reject(error);
    });

    const blogs = ref([]);
    const blog = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const pagination = ref(null);

    const fetchBlogs = async (page = 1) => {
        loading.value = true;
        error.value = null;
        console.log('Fetching blogs, page:', page, 'Base URL:', apiBaseUrl);
        try {
            const response = await API.get('/blogs', { params: { page } });
            console.log('Response:', response.data);
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
                return response.data;
            } else {
                throw new Error(response.data.message || 'Failed to fetch blogs');
            }
        } catch (err) {
            console.error('Error:', err);
            error.value = err.response?.data?.message || err.message || 'An error occurred';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Get single blog by ID
    const fetchBlog = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await API.get(`/blogs/${id}`);
            if (response.data.success) {
                blog.value = response.data.data;
                return response.data;
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

    // Create new blog
    const createBlog = async (blogData) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await API.post('/blogs', blogData);
            if (response.data.success) {
                if (blogs.value.length > 0) {
                    blogs.value.unshift(response.data.data);
                }
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

    // Update blog
    const updateBlog = async (id, blogData) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await API.put(`/blogs/${id}`, blogData);
            if (response.data.success) {
                const index = blogs.value.findIndex(b => b.id === id);
                if (index !== -1) {
                    blogs.value[index] = response.data.data;
                }
                if (blog.value && blog.value.id === id) {
                    blog.value = response.data.data;
                }
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

    // Delete blog
    const deleteBlog = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await API.delete(`/blogs/${id}`);
            if (response.data.success) {
                blogs.value = blogs.value.filter(b => b.id !== id);
                if (blog.value && blog.value.id === id) {
                    blog.value = null;
                }
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

    // Clear error
    const clearError = () => {
        error.value = null;
    };

    // Reset state
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