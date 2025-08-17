import axios from "axios";
import Cookies from "js-cookie";
import api from '../utils/api'

// Sử dụng instance axios chung từ utility
const API = api

export const useAdminReviews = () => {
    const getAllReviews = async (page = 1, perPage = 5, extraParams = {}) => {
        try {
            const res = await API.get("/api/product-reviews", {
                params: { page, per_page: perPage, ...extraParams },
            });
            return res.data;
        } catch (err) {
            console.error("Lỗi khi lấy danh sách đánh giá:", err);
            throw err;
        }
    };

    const updateReviewStatus = async (reviewId, status) => {
        try {
            const res = await API.put(`/api/product-reviews/${reviewId}`, {
                is_approved: status === "approved",
                is_hidden: status === "rejected",
            });
            return res.data;
        } catch (err) {
            console.error("Lỗi khi cập nhật trạng thái đánh giá:", err);
            throw err;
        }
    };

    const deleteReview = async (reviewId) => {
        try {
            const res = await API.delete(`/api/product-reviews/${reviewId}`);
            return res.data;
        } catch (err) {
            console.error("Lỗi khi xóa đánh giá:", err);
            throw err;
        }
    };

    const addAdminReply = async (reviewId, replyData) => {
        try {
            const res = await API.post(
                `/api/product-reviews/${reviewId}/admin-reply`,
                replyData,
                { headers: { "Content-Type": "application/json" } }
            );
            return res.data;
        } catch (err) {
            console.error("Lỗi khi thêm phản hồi admin:", err);
            throw err;
        }
    };

    const getReviewsByCategory = async (categoryId, page = 1, perPage = 5) => {
        try {
            const res = await API.get(`/api/product-reviews/category/${categoryId}`, {
                params: { page, per_page: perPage },
            });
            return res.data;
        } catch (err) {
            console.error("Lỗi khi lấy đánh giá theo danh mục:", err);
            throw err;
        }
    };

    const getReviewsByBrand = async (brandId, page = 1, perPage = 5) => {
        try {
            const res = await API.get(`/api/product-reviews/brand/${brandId}`, {
                params: { page, per_page: perPage },
            });
            return res.data;
        } catch (err) {
            console.error("Lỗi khi lấy đánh giá theo thương hiệu:", err);
            throw err;
        }
    };

    const updateAdminReply = async (replyId, content) => {
        try {
            const res = await API.put(
                `/api/product-reviews/${replyId}/admin-reply`,
                { content },
                { headers: { "Content-Type": "application/json" } }
            );
            return res.data;
        } catch (err) {
            console.error("Lỗi khi cập nhật phản hồi admin:", err);
            throw err;
        }
    };

    return {
        getAllReviews,
        updateReviewStatus,
        addAdminReply,
        updateAdminReply,
        getReviewsByCategory,
        getReviewsByBrand,
        deleteReview,
    };
};
