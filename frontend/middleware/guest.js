// middleware/guest.js
export default defineNuxtRouteMiddleware(async (to, from) => {
    const { isAuthenticated, checkAuth } = useAuth()

    // Nếu đã đăng nhập thì chuyển hướng khỏi trang login/register
    if (isAuthenticated.value || await checkAuth()) {
        return navigateTo('/') // Hoặc trang dashboard tùy bạn
    }
})