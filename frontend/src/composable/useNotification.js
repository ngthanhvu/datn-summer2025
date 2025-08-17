import { ref } from 'vue'
import axios from 'axios'
import Cookies from 'js-cookie'
import { push } from 'notivue'
import api from '../utils/api'

export function useNotification() {
    const notifications = ref([])
    const loading = ref(false)
    const error = ref(null)
    const previousNotifications = ref([])
    const lastNotificationTime = ref(0)

    // Sử dụng instance axios chung từ utility
    const API = api

    const fetchNotifications = async () => {
        loading.value = true
        error.value = null
        try {
            // Không cần thêm Authorization header nữa vì đã có trong interceptor
            const res = await API.get('/api/notifications')

            const newNotifications = res.data

            // Kiểm tra thông báo mới
            if (previousNotifications.value.length > 0) {
                const newItems = newNotifications.filter(newNotif =>
                    !previousNotifications.value.some(oldNotif => oldNotif.id === newNotif.id)
                )

                // Hiển thị push notification cho từng thông báo mới
                newItems.forEach(notification => {
                    // Tránh hiển thị thông báo quá nhanh (debounce)
                    const now = Date.now()
                    if (now - lastNotificationTime.value > 1000) {
                        push.success({
                            title: notification.data?.title || 'Thông báo mới',
                            message: notification.data?.message || 'Bạn có thông báo mới',
                            duration: 5000,
                            icon: 'fas fa-bell'
                        })
                        lastNotificationTime.value = now
                    }
                })
            }

            // Cập nhật danh sách thông báo
            notifications.value = newNotifications
            previousNotifications.value = [...newNotifications]

        } catch (err) {
            error.value = err
            // Hiển thị thông báo lỗi nếu không thể fetch notifications
            push.error({
                title: 'Lỗi',
                message: 'Không thể tải thông báo',
                duration: 3000
            })
        } finally {
            loading.value = false
        }
    }

    // Hàm để test thông báo push
    const testNotification = (type = 'success') => {
        const messages = {
            success: {
                title: 'Thành công!',
                message: 'Đây là thông báo test thành công'
            },
            error: {
                title: 'Lỗi!',
                message: 'Đây là thông báo test lỗi'
            },
            warning: {
                title: 'Cảnh báo!',
                message: 'Đây là thông báo test cảnh báo'
            },
            info: {
                title: 'Thông tin',
                message: 'Đây là thông báo test thông tin'
            }
        }

        const message = messages[type]
        push[type]({
            title: message.title,
            message: message.message,
            duration: 5000
        })
    }

    return {
        notifications,
        loading,
        error,
        fetchNotifications,
        testNotification
    }
}
