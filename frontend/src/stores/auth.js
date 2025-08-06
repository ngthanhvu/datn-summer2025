import { defineStore } from "pinia";
import { ref } from "vue";

export const useAuthStore = defineStore("auth", () => {
    // Thử lấy user từ localStorage trước, nếu không có thì lấy từ cookie
    const getUserFromStorage = () => {
        // Thử localStorage trước
        const userFromLS = localStorage.getItem("user");
        if (userFromLS) {
            return JSON.parse(userFromLS);
        }
        
        // Nếu không có trong localStorage, thử lấy từ cookie
        const cookies = document.cookie.split(';');
        const userCookie = cookies.find(cookie => cookie.trim().startsWith('user='));
        if (userCookie) {
            const userValue = userCookie.split('=')[1];
            return JSON.parse(decodeURIComponent(userValue));
        }
        
        return null;
    };

    const user = ref(getUserFromStorage());

    const setUser = (userData) => {
        user.value = userData;
        localStorage.setItem("user", JSON.stringify(userData));
    };

    const clearUser = () => {
        user.value = null;
        localStorage.removeItem("user");
    };

    return { user, setUser, clearUser };
});
