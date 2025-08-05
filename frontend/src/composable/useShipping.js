// src/composables/useShipping.js
import axios from "axios";
import { ref } from "vue";
import Swal from "sweetalert2";
import Cookies from "js-cookie";

export const useShipping = () => {
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

    const shippingFee = ref(null);
    const provinces = ref([]);
    const districts = ref([]);
    const wards = ref([]);
    const loading = ref(false);
    const errors = ref({});

    // Lấy danh sách tỉnh/thành phố từ GHN
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

    // Lấy danh sách quận/huyện theo tỉnh/thành phố
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

    // Lấy danh sách phường/xã theo quận/huyện
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

    // Tính phí vận chuyển cơ bản
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

    // Tính phí vận chuyển từ giỏ hàng
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

    // Tính phí vận chuyển từ giỏ hàng với địa chỉ
    const calculateShippingFromCart = async (cartItems, address) => {
        try {
            const data = {
                to_district_id: address.district_id,
                to_ward_code: address.ward_code,
                service_type_id: 2, // Mặc định hàng nhẹ
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

    // Format phí vận chuyển
    const formatShippingFee = (fee) => {
        if (!fee) return "0 VNĐ";
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(fee.total || fee);
    };

    // Format thời gian giao hàng
    const formatDeliveryTime = (estimatedDelivery) => {
        if (!estimatedDelivery) return "";
        return estimatedDelivery.description || `Giao hàng trong ${estimatedDelivery.min_days}-${estimatedDelivery.max_days} ngày`;
    };

    // Reset dữ liệu
    const resetShippingData = () => {
        shippingFee.value = null;
        provinces.value = [];
        districts.value = [];
        wards.value = [];
        errors.value = {};
    };

    // Validate địa chỉ
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
        // Reactive data
        shippingFee,
        provinces,
        districts,
        wards,
        loading,
        errors,

        // Methods
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