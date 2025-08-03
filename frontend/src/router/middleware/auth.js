import { useAuthStore } from '../../stores/auth'

export function authGuard(to, from, next) {
    const authStore = useAuthStore()
    const user = authStore.user

    // Nếu route yêu cầu đăng nhập
    if (to.meta.requiresAuth) {
        if (!user) {
            return next({ path: '/login' })
        }
        if (to.meta.requiresAdmin && user.role !== 'admin') {
            return next({ path: '/' })
        }
        if (to.meta.requiresUser && user.role !== 'user') {
            return next({ path: '/' })
        }
    }

    // Nếu route chỉ dành cho khách
    if (to.meta.guestOnly && user) {
        return next({ path: '/' })
    }

    next()
} 