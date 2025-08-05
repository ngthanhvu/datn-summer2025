import axios from "axios";
import { ref } from "vue";
import Swal from "sweetalert2";
import Cookies from "js-cookie";

export const useShipping = () => {
    const API = axios.create({
        baseURL: import.meta.env.VITE_API_BASE_URL,
    });

    API.interceptors.request.use((req) => {
        const token = Cookies.get("token");
        if (token) {
            req.headers.Authorization = `Bearer ${token}`;
        }
        return req;
    });

    const shippingFee = ref(null);
    const provinces = ref([]);
    const districts = ref([]);
    const wards = ref([]);
    const loading = ref(false);
    const errors = ref({});

    const getProvinces = async () => {
        try {
            loading.value = true;
            const res = await API.get("/api/shipping/provinces");
            if (res.data.success) {
                provinces.value = res.data.data;
                return res.data.data;
            } else {
                throw new Error(res.data.message);
            }
        } catch (err) {
            console.error("Error getting provinces:", err);
            Swal.fire({
                icon: "error",
                title: "Lỗi",
                text: "Không thể lấy danh sách tỉnh/thành phố",
            });
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getDistricts = async (provinceId) => {
        try {
            loading.value = true;
            const res = await API.get(`/api/shipping/districts?province_id=${provinceId}`);
            if (res.data.success) {
                districts.value = res.data.data;
                return res.data.data;
            } else {
                throw new Error(res.data.message);
            }
        } catch (err) {
            console.error("Error getting districts:", err);
            Swal.fire({
                icon: "error",
                title: "Lỗi",
                text: "Không thể lấy danh sách quận/huyện",
            });
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getWards = async (districtId) => {
        try {
            loading.value = true;
            const res = await API.get(`/api/shipping/wards?district_id=${districtId}`);
            if (res.data.success) {
                wards.value = res.data.data;
                return res.data.data;
            } else {
                throw new Error(res.data.message);
            }
        } catch (err) {
            console.error("Error getting wards:", err);
            Swal.fire({
                icon: "error",
                title: "Lỗi",
                text: "Không thể lấy danh sách phường/xã",
            });
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const calculateShippingFee = async (data) => {
        try {
            loading.value = true;
            const res = await API.post("/api/shipping/calculate-fee", data);
            if (res.data.success) {
                shippingFee.value = res.data.data;
                return res.data.data;
            } else {
                throw new Error(res.data.message);
            }
        } catch (err) {
            console.error("Error calculating shipping fee:", err);
            Swal.fire({
                icon: "error",
                title: "Lỗi",
                text: err.response?.data?.message || "Không thể tính phí vận chuyển",
            });
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const calculateCartShippingFee = async (data) => {
        try {
            loading.value = true;
            const res = await API.post("/api/shipping/calculate-cart-fee", data);
            if (res.data.success) {
                shippingFee.value = res.data.data;
                return res.data.data;
            } else {
                throw new Error(res.data.message);
            }
        } catch (err) {
            console.error("Error calculating cart shipping fee:", err);
            Swal.fire({
                icon: "error",
                title: "Lỗi",
                text: err.response?.data?.message || "Không thể tính phí vận chuyển",
            });
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const calculateShippingFromCart = async (cartItems, address) => {
        try {
            const data = {
                to_district_id: address.district_id,
                to_ward_code: address.ward_code,
                service_type_id: 2,
                cart_items: cartItems.map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity
                }))
            };

            return await calculateCartShippingFee(data);
        } catch (err) {
            console.error("Error calculating shipping from cart:", err);
            throw err;
        }
    };

    const formatShippingFee = (fee) => {
        if (!fee) return "0 VNĐ";
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(fee.total || fee);
    };

    const formatDeliveryTime = (estimatedDelivery) => {
        if (!estimatedDelivery) return "";
        return estimatedDelivery.description || `Giao hàng trong ${estimatedDelivery.min_days}-${estimatedDelivery.max_days} ngày`;
    };

    const resetShippingData = () => {
        shippingFee.value = null;
        provinces.value = [];
        districts.value = [];
        wards.value = [];
        errors.value = {};
    };

    const validateAddress = (address) => {
        const errors = {};
        
        if (!address.to_district_id) {
            errors.district = "Vui lòng chọn quận/huyện";
        }
        
        if (!address.to_ward_code) {
            errors.ward = "Vui lòng chọn phường/xã";
        }

        return {
            isValid: Object.keys(errors).length === 0,
            errors
        };
    };

    return {
        shippingFee,
        provinces,
        districts,
        wards,
        loading,
        errors,

        getProvinces,
        getDistricts,
        getWards,
        calculateShippingFee,
        calculateCartShippingFee,
        calculateShippingFromCart,
        formatShippingFee,
        formatDeliveryTime,
        resetShippingData,
        validateAddress,
    };
}; 