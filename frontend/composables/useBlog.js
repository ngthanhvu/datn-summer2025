import axios from "axios";
import { ref } from "vue";
import { useCookie } from '#app';

export const useBlog = () => {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.public.apiBaseUrl;
    const token = useCookie('token');

    const API = axios.create({
        baseURL: apiBaseUrl
    });

    API.interceptors.request.use((req) => {
        if (token.value) {
            req.headers.Authorization = `Bearer ${token.value}`;
        }
        return req;
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
                blog.value = null;
                throw new Error(response.data.message || 'Blog not found');
            }
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'Blog not found';
            blog.value = null;
        } finally {
            loading.value = false;
        }
    };

    const fetchBlogBySlug = async (slug) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await API.get(`/api/blogs/slug/${slug}`);
            if (response.data.success) {
                blog.value = response.data.data;
            } else {
                blog.value = null;
                throw new Error(response.data.message || 'Blog not found');
            }
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'Blog not found';
            blog.value = null;
        } finally {
            loading.value = false;
        }
    };

    const createBlog = async (blogData) => {
        loading.value = true;
        error.value = null;
        try {
            console.log('Creating blog - Token:', token.value);
            const response = await API.post('/api/blogs', blogData, {
                headers: {
                    // 'Content-Type': 'multipart/form-data', // XÓA DÒNG NÀY
                    'Authorization': `Bearer ${token.value}`
                }
            });
            if (response.data.success) {
                return response.data;
            } else {
                throw new Error(response.data.message || 'Failed to create blog');
            }
        } catch (err) {
            console.log('Error creating blog:', err.response?.data);
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

    const updateBlog = async (id, formData) => {
        loading.value = true;
        error.value = null;
        try {
            // Thêm _method: 'PUT' để Laravel hiểu đây là update
            formData.append('_method', 'PUT');
            const response = await API.post(`/api/blogs/${id}`, formData, {
                headers: {
                    'Authorization': `Bearer ${token.value}`
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

    const updateBlogJson = async (id, blogData) => {
        loading.value = true;
        error.value = null;
        try {

            const response = await API.put(`/api/blogs/${id}`, blogData, {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token.value}`
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
        fetchBlogBySlug,
        createBlog,
        updateBlog,
        updateBlogJson,
        deleteBlog,
        clearError,
        resetState
    };
};