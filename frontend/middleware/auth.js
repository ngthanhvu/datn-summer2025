export default defineNuxtRouteMiddleware(async (to, from) => {
    const { isAuthenticated, checkAuth } = useAuth()

    if (!isAuthenticated.value) {
        const authValid = await checkAuth()

        if (!authValid) {
            return navigateTo('/login')
        }
    }
})