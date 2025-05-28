// middleware/admin.js
export default defineNuxtRouteMiddleware(async (to, from) => {
    const { isAdmin, checkAdmin } = useAuth()

    if (!isAdmin.value) {
        const isAdminValid = await checkAdmin()

        if (!isAdminValid) {
            return navigateTo('/')
        }
    }
})