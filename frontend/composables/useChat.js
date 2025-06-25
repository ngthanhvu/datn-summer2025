import axios from 'axios'
import { useAuth } from './useAuth'

export const useChat = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl
    const API = axios.create({ baseURL: apiBaseUrl })
    const { getToken } = useAuth()

    const getConversations = async () => {
        try {
            const token = await getToken()
            const response = await API.get('/api/chat/conversations', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
            return response.data 
        } catch (error) {
            console.error('Lỗi khi lấy cuộc trò chuyện:', error)
            throw error
        }
    }

    const getMessages = async (userId) => {
        try {
            const token = await getToken()
            const response = await API.get(`/api/chat/messages/${userId}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
            return response.data 
        } catch (error) {
            console.error('Lỗi khi lấy tin nhắn:', error)
            throw error
        }
    }

    const sendMessage = async (messageData) => {
        try {
            const token = await getToken()
            const formData = new FormData()
            Object.keys(messageData).forEach(key => {
                if (messageData[key] !== null && messageData[key] !== undefined) {
                    formData.append(key, messageData[key])
                }
            })
            const response = await API.post('/api/chat/send', formData, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'multipart/form-data'
                }
            })
            return response.data 
        } catch (error) {
            console.error('Lỗi khi gửi tin nhắn:', error)
            throw error
        }
    }

    const markAsRead = async (messageId) => {
        try {
            const token = await getToken()
            const response = await API.put(`/api/chat/read/${messageId}`, {}, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Lỗi khi đánh dấu đã đọc:', error)
            throw error
        }
    }

    const getUnreadCount = async () => {
        try {
            const token = await getToken()
            const response = await API.get('/api/chat/unread-count', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Lỗi khi lấy số tin nhắn chưa đọc:', error)
            throw error
        }
    }

    const searchUsers = async (query) => {
        try {
            const token = await getToken()
            const response = await API.get('/api/chat/search-users', {
                params: { q: query },
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Lỗi khi tìm kiếm người dùng:', error)
            throw error
        }
    }

    const deleteMessage = async (messageId) => {
        try {
            const token = await getToken()
            const response = await API.delete(`/api/chat/message/${messageId}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
            return response.data 
        } catch (error) {
            console.error('Lỗi khi xóa tin nhắn:', error)
            throw error
        }
    }

    const getAdmins = async () => {
        try {
            const token = await getToken()
            const response = await API.get('/api/chat/admins', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
            return response.data
        } catch (error) {
            console.error('Lỗi khi lấy danh sách admin:', error)
            throw error
        }
    }

    return {
        getConversations,
        getMessages,
        sendMessage,
        markAsRead,
        getUnreadCount,
        searchUsers,
        deleteMessage,
        getAdmins
    }
} 