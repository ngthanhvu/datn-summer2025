import axios from "axios";
import Cookies from "js-cookie";
import api from '../utils/api'

// Sử dụng instance axios chung từ utility
const API = api

export const useChat = () => {
    const getConversations = async () => {
        try {
            const res = await API.get("/api/chat/conversations");
            return res.data;
        } catch (err) {
            console.error("Lỗi khi lấy cuộc trò chuyện:", err);
            throw err;
        }
    };

    const getMessages = async (userId) => {
        try {
            const res = await API.get(`/api/chat/messages/${userId}`);
            return res.data;
        } catch (err) {
            console.error("Lỗi khi lấy tin nhắn:", err);
            throw err;
        }
    };

    const sendMessage = async (messageData) => {
        try {
            const formData = new FormData();
            Object.entries(messageData).forEach(([key, value]) => {
                if (value !== null && value !== undefined) {
                    formData.append(key, value);
                }
            });

            const res = await API.post("/api/chat/send", formData, {
                headers: { "Content-Type": "multipart/form-data" },
            });
            return res.data;
        } catch (err) {
            console.error("Lỗi khi gửi tin nhắn:", err);
            throw err;
        }
    };

    const markAsRead = async (messageId) => {
        try {
            const res = await API.put(`/api/chat/read/${messageId}`);
            return res.data;
        } catch (err) {
            console.error("Lỗi khi đánh dấu đã đọc:", err);
            throw err;
        }
    };

    const getUnreadCount = async () => {
        try {
            const res = await API.get("/api/chat/unread-count");
            return res.data;
        } catch (err) {
            console.error("Lỗi khi lấy số tin nhắn chưa đọc:", err);
            throw err;
        }
    };

    const searchUsers = async (query) => {
        try {
            const res = await API.get("/api/chat/search-users", {
                params: { q: query },
            });
            return res.data;
        } catch (err) {
            console.error("Lỗi khi tìm kiếm người dùng:", err);
            throw err;
        }
    };

    const deleteMessage = async (messageId) => {
        try {
            const res = await API.delete(`/api/chat/message/${messageId}`);
            return res.data;
        } catch (err) {
            console.error("Lỗi khi xóa tin nhắn:", err);
            throw err;
        }
    };

    const getAdmins = async () => {
        try {
            const res = await API.get("/api/chat/admins");
            return res.data;
        } catch (err) {
            console.error("Lỗi khi lấy danh sách admin:", err);
            throw err;
        }
    };

    return {
        getConversations,
        getMessages,
        sendMessage,
        markAsRead,
        getUnreadCount,
        searchUsers,
        deleteMessage,
        getAdmins,
    };
};
