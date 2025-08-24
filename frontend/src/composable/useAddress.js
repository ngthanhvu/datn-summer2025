// src/composables/useAddress.js
import { ref } from "vue";
import Swal from "sweetalert2";
import Cookies from "js-cookie";
import api from '../utils/api'

export const useAddress = () => {
    // Sử dụng instance axios chung từ utility
    const API = api

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
            const res = await API.get("/api/shipping/provinces");
            if (res.data.success) {
                return res.data.data;
            } else {
                throw new Error(res.data.message || 'Không thể lấy danh sách tỉnh/thành');
            }
        } catch (err) {
            // Fallback data nếu GHN API không hoạt động
            return [
                { ProvinceID: 1, ProvinceName: "Hà Nội" },
                { ProvinceID: 2, ProvinceName: "TP. Hồ Chí Minh" },
                { ProvinceID: 3, ProvinceName: "Đà Nẵng" },
                { ProvinceID: 4, ProvinceName: "Huế" },
                { ProvinceID: 5, ProvinceName: "Nha Trang" },
                { ProvinceID: 6, ProvinceName: "Cần Thơ" },
                { ProvinceID: 7, ProvinceName: "An Giang" },
                { ProvinceID: 8, ProvinceName: "Kiên Giang" },
                { ProvinceID: 9, ProvinceName: "Bình Dương" },
                { ProvinceID: 10, ProvinceName: "Đồng Nai" },
                { ProvinceID: 11, ProvinceName: "Bà Rịa - Vũng Tàu" },
                { ProvinceID: 12, ProvinceName: "Tây Ninh" },
                { ProvinceID: 13, ProvinceName: "Bình Phước" },
                { ProvinceID: 14, ProvinceName: "Bình Thuận" },
                { ProvinceID: 15, ProvinceName: "Ninh Thuận" },
                { ProvinceID: 16, ProvinceName: "Lâm Đồng" },
                { ProvinceID: 17, ProvinceName: "Bình Định" },
                { ProvinceID: 18, ProvinceName: "Phú Yên" },
                { ProvinceID: 19, ProvinceName: "Khánh Hòa" },
                { ProvinceID: 20, ProvinceName: "Quảng Nam" },
                { ProvinceID: 21, ProvinceName: "Quảng Ngãi" },
                { ProvinceID: 22, ProvinceName: "Long An" },
                { ProvinceID: 23, ProvinceName: "Tiền Giang" },
                { ProvinceID: 24, ProvinceName: "Bến Tre" },
                { ProvinceID: 25, ProvinceName: "Trà Vinh" },
                { ProvinceID: 26, ProvinceName: "Vĩnh Long" },
                { ProvinceID: 27, ProvinceName: "Đồng Tháp" },
                { ProvinceID: 28, ProvinceName: "An Giang" },
                { ProvinceID: 29, ProvinceName: "Kiên Giang" },
                { ProvinceID: 30, ProvinceName: "Cần Thơ" },
                { ProvinceID: 31, ProvinceName: "Hậu Giang" },
                { ProvinceID: 32, ProvinceName: "Sóc Trăng" },
                { ProvinceID: 33, ProvinceName: "Bạc Liêu" },
                { ProvinceID: 34, ProvinceName: "Cà Mau" },
                { ProvinceID: 35, ProvinceName: "Đắk Lắk" }
            ];
        }
    };

    const getDistricts = async (provinceCode) => {
        try {
            const res = await API.get(`/api/shipping/districts?province_id=${provinceCode}`);
            if (res.data.success) {
                return res.data.data;
            } else {
                throw new Error(res.data.message || 'Không thể lấy danh sách quận/huyện');
            }
        } catch (err) {
            // Fallback data
            return [
                { DistrictID: 1, DistrictName: "Quận 1" },
                { DistrictID: 2, DistrictName: "Quận 2" },
                { DistrictID: 3, DistrictName: "Quận 3" },
                { DistrictID: 4, DistrictName: "Quận 4" },
                { DistrictID: 5, DistrictName: "Quận 5" }
            ];
        }
    };

    const getWards = async (districtCode) => {
        try {
            const res = await API.get(`/api/shipping/wards?district_id=${districtCode}`);
            if (res.data.success) {
                return res.data.data;
            } else {
                throw new Error(res.data.message || 'Không thể lấy danh sách phường/xã');
            }
        } catch (err) {
            // Fallback data
            return [
                { WardCode: "00001", WardName: "Phường 1" },
                { WardCode: "00002", WardName: "Phường 2" },
                { WardCode: "00003", WardName: "Phường 3" },
                { WardCode: "00004", WardName: "Phường 4" },
                { WardCode: "00005", WardName: "Phường 5" }
            ];
        }
    };

    const getAddresses = async () => {
        try {
            const res = await API.get("/api/addresses");
            return res.data;
        } catch (err) {
            throw err;
        }
    };

    const getMyAddress = async () => {
        try {
            const res = await API.get("/api/me/address");
            return res.data;
        } catch (err) {
            throw err;
        }
    };

    const createAddress = async (data) => {
        try {
            const res = await API.post("/api/addresses", data);
            return res.data;
        } catch (err) {
            throw err;
        }
    };

    const updateAddress = async (id, data) => {
        try {
            const res = await API.put(`/api/addresses/${id}`, data);
            return res.data;
        } catch (err) {
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
                return res.data;
            }
            return null;
        } catch (err) {
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
