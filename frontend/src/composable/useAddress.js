// src/composables/useAddress.js
import axios from "axios";
import { ref } from "vue";
import Swal from "sweetalert2";
import Cookies from "js-cookie";

export const useAddress = () => {
    const API = axios.create({
        baseURL: import.meta.env.VITE_API_BASE_URL,
    });

    // ✅ Interceptor: tự động thêm token từ cookie vào mọi request
    API.interceptors.request.use((req) => {
        const token = Cookies.get("token");
        if (token) {
            req.headers.Authorization = `Bearer ${token}`;
        }
        return req;
    });

    // API provinces độc lập
    const PROVINCE_API = axios.create({
        baseURL: "http://provinces.open-api.vn/api/",
    });

    const form = ref({
        full_name: "",
        phone: "",
        province: "",
        district: "",
        ward: "",
        street: "",
    });

    const errors = ref({});

    const getProvinces = async () => {
        try {
            const res = await PROVINCE_API.get("p/");
            return res.data;
        } catch (err) {
            console.error("Error getting provinces:", err);
            throw err;
        }
    };

    const getDistricts = async (provinceCode) => {
        try {
            const res = await PROVINCE_API.get(`p/${provinceCode}?depth=2`);
            return res.data.districts;
        } catch (err) {
            console.error("Error getting districts:", err);
            throw err;
        }
    };

    const getWards = async (districtCode) => {
        try {
            const res = await PROVINCE_API.get(`d/${districtCode}?depth=2`);
            return res.data.wards;
        } catch (err) {
            console.error("Error getting wards:", err);
            throw err;
        }
    };

    const getAddresses = async () => {
        try {
            const res = await API.get("/api/addresses");
            return res.data;
        } catch (err) {
            console.error("Error getting addresses:", err);
            throw err;
        }
    };

    const getMyAddress = async () => {
        try {
            const res = await API.get("/api/me/address");
            return res.data;
        } catch (err) {
            console.error("Error getting my address:", err);
            throw err;
        }
    };

    const createAddress = async (data) => {
        try {
            const res = await API.post("/api/addresses", data);
            return res.data;
        } catch (err) {
            console.error("Error creating address:", err);
            throw err;
        }
    };

    const updateAddress = async (id, data) => {
        try {
            const res = await API.put(`/api/addresses/${id}`, data);
            return res.data;
        } catch (err) {
            console.error("Error updating address:", err);
            throw err;
        }
    };

    const deleteAddress = async (id) => {
        try {
            const confirm = await Swal.fire({
                title: "Bạn có chắc chắn?",
                text: "Bạn không thể hoàn tác sau khi xóa!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Có, xóa nó!",
                cancelButtonText: "Hủy",
            });

            if (confirm.isConfirmed) {
                const res = await API.delete(`/api/addresses/${id}`);
                Swal.fire("Đã xóa!", "Địa chỉ đã được xóa thành công", "success");
                return res.data;
            }
            return null;
        } catch (err) {
            console.error("Error deleting address:", err);
            Swal.fire("Lỗi", "Có lỗi xảy ra khi xóa địa chỉ.", "error");
            throw err;
        }
    };

    const validateForm = () => {
        const err = {};

        if (!form.value.full_name) {
            err.full_name = "Họ và tên không được để trống";
        } else if (form.value.full_name.length > 100) {
            err.full_name = "Họ và tên tối đa 100 ký tự";
        }

        if (!form.value.phone) {
            err.phone = "Số điện thoại không được để trống";
        } else if (!/^(0|\+84)[1-9][0-9]{8,9}$/.test(form.value.phone)) {
            err.phone = "Số điện thoại không hợp lệ";
        }

        if (!form.value.province) err.province = "Vui lòng chọn tỉnh/thành phố";
        if (!form.value.district) err.district = "Vui lòng chọn quận/huyện";
        if (!form.value.ward) err.ward = "Vui lòng chọn xã/phường";

        if (!form.value.street) {
            err.street = "Thôn/xóm không được để trống";
        } else if (form.value.street.length > 100) {
            err.street = "Thôn/xóm tối đa 100 ký tự";
        }

        errors.value = err;
        return Object.keys(err).length === 0;
    };

    const getFullAddress = (address) => {
        return `${address.street}, ${address.ward}, ${address.district}, ${address.province}`;
    };

    const resetForm = () => {
        form.value = {
            full_name: "",
            phone: "",
            province: "",
            district: "",
            ward: "",
            street: "",
        };
        errors.value = {};
    };

    const setFormData = (data) => {
        form.value = { ...data };
    };

    return {
        form,
        errors,
        getProvinces,
        getDistricts,
        getWards,
        getAddresses,
        getMyAddress,
        createAddress,
        updateAddress,
        deleteAddress,
        validateForm,
        getFullAddress,
        resetForm,
        setFormData,
    };
};
