// middleware/auth.js
export default defineNuxtRouteMiddleware(async (to, from) => {
    const { isAuthenticated, checkAuth } = useAuth()

    // Nếu route yêu cầu auth nhưng chưa đăng nhập
    if (!isAuthenticated.value) {
        const authValid = await checkAuth() // Thử kiểm tra lại

        if (!authValid) {
            return navigateTo('/login')
        }
    }
})