import axios from "axios";
import { ref } from "vue";
import Swal from "sweetalert2";
import Cookies from "js-cookie";
import api from '../utils/api'

// Sử dụng instance axios chung từ utility
const API = api

const shippingFee = ref(null);
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);
const loading = ref(false);
const errors = ref({});
const shippingError = ref('');
const shopInfo = ref(null);

const getProvinces = async () => handleApi(async () => {
  const res = await API.get("/api/shipping/provinces");
  provinces.value = res.data.data;
  return res.data.data;
}, "Không thể lấy danh sách tỉnh/thành phố");

const getDistricts = async (provinceId) => handleApi(async () => {
  const res = await API.get(`/api/shipping/districts?province_id=${provinceId}`);
  districts.value = res.data.data;
  return res.data.data;
}, "Không thể lấy danh sách quận/huyện");

const getWards = async (districtId) => handleApi(async () => {
  const res = await API.get(`/api/shipping/wards?district_id=${districtId}`);
  wards.value = res.data.data;
  return res.data.data;
}, "Không thể lấy danh sách phường/xã");

const fetchShopInfo = async () => {
  try {
    const res = await API.get('/api/shipping/config');
    if (res.data.success) shopInfo.value = res.data.data;
  } catch { }
};

const getShopLocation = async () => {
  if (!shopInfo.value) await fetchShopInfo();
  const info = shopInfo.value?.shop_info;
  if (info) return { districtId: info.district_id, wardCode: info.ward_code };
  throw new Error('Không thể lấy thông tin shop từ GHN API');
};

const getDistrictAndWardFromAddress = async (address) => {
  const provincesRes = await API.get('/api/shipping/provinces');
  const provinces = provincesRes.data.data;
  const provinceName = address.province?.replace('Tỉnh ', '').replace('Thành phố ', '');
  let province = provinces.find(p => p.ProvinceName === provinceName) ||
    provinces.find(p => p.ProvinceName.toLowerCase() === provinceName?.toLowerCase());
  if (!province) throw new Error(`Không tìm thấy tỉnh: ${provinceName}`);

  const districtsRes = await API.get(`/api/shipping/districts?province_id=${province.ProvinceID}`);
  const districts = districtsRes.data.data;
  const districtName = address.district?.replace('Huyện ', '').replace('Quận ', '');
  let district = districts.find(d => d.DistrictName === districtName) ||
    districts.find(d => d.DistrictName.toLowerCase() === districtName?.toLowerCase()) ||
    districts.find(d => d.DistrictName.toLowerCase().includes(districtName?.toLowerCase()));
  if (!district) throw new Error(`Không tìm thấy huyện: ${districtName}`);

  const wardsRes = await API.get(`/api/shipping/wards?district_id=${district.DistrictID}`);
  const wards = wardsRes.data.data;
  const wardName = address.ward?.replace('Xã ', '').replace('Phường ', '');
  let ward = wards.find(w => w.WardName === wardName) ||
    wards.find(w => w.WardName.toLowerCase() === wardName?.toLowerCase()) ||
    wards.find(w => w.WardName.toLowerCase().includes(wardName?.toLowerCase()));
  if (!ward) throw new Error(`Không tìm thấy xã: ${wardName}`);

  return { district_id: district.DistrictID, ward_code: ward.WardCode };
};

const callGHNShippingAPI = async (address, cartItems) => {
  const totalWeight = cartItems.reduce((t, i) => t + (500 * i.quantity), 0);
  const totalValue = cartItems.reduce((t, i) => t + (i.price * i.quantity), 0);

  const shopLocation = await getShopLocation();

  if (!shopInfo.value) await fetchShopInfo();
  const ghnConfig = shopInfo.value || {
    base_url: 'https://online-gateway.ghn.vn/shiip/public-api/v2',
    token: '', shop_id: ''
  };

  const loc = await getDistrictAndWardFromAddress(address);
  const toDistrictId = loc.district_id;
  const toWardCode = loc.ward_code;

  if (!toDistrictId || !toWardCode) return { success: false, message: 'Thiếu district_id hoặc ward_code' };
  if (!shopLocation.districtId || !shopLocation.wardCode) return { success: false, message: 'Không thể lấy thông tin shop từ GHN API' };
  const shippingData = {
    service_type_id: 2,
    from_district_id: shopLocation.districtId,
    from_ward_code: shopLocation.wardCode,
    to_district_id: toDistrictId,
    to_ward_code: toWardCode,
    weight: totalWeight,
    length: 30, width: 40, height: 20,
    insurance_value: totalValue, cod_value: 0
  };
  try {
    const res = await axios.post(`${ghnConfig.base_url}/shipping-order/fee`, shippingData, {
      headers: {
        'Content-Type': 'application/json',
        'Token': ghnConfig.token,
        'ShopId': ghnConfig.shop_id,
      }
    });
    if (res.data.code === 200) return { success: true, data: res.data.data };
    return { success: false, message: res.data.message || 'Có lỗi khi tính phí' };
  } catch (error) {
    console.error('GHN API error:', error.response?.data || error.message);
    return { success: false, message: error.response?.data?.message || error.message || 'Không thể kết nối GHN API' };
  }
};

const calculateGHNShipping = async (selectedAddress, cartItems) => {
  if (!selectedAddress || !cartItems.length) return null;
  try {
    loading.value = true;
    shippingError.value = '';
    const result = await callGHNShippingAPI(selectedAddress, cartItems);
    if (result.success) {
      const estimatedDelivery = { min_days: 1, max_days: 3, description: 'Giao hàng trong 1-3 ngày' };
      return { shippingFee: result.data, estimatedDelivery, zone: 'GHN API' };
    } else {
      shippingError.value = result.message || 'Không thể tính phí vận chuyển';
      return null;
    }
  } catch {
    shippingError.value = 'Không thể tính phí vận chuyển';
    return null;
  } finally {
    loading.value = false;
  }
};

const calculateShippingFee = async (data) => handleApi(async () => {
  const res = await API.post("/api/shipping/calculate-fee", data);
  shippingFee.value = res.data.data;
  return res.data.data;
}, "Không thể tính phí vận chuyển");

const calculateCartShippingFee = async (data) => handleApi(async () => {
  const res = await API.post("/api/shipping/calculate-cart-fee", data);
  shippingFee.value = res.data.data;
  return res.data.data;
}, "Không thể tính phí vận chuyển");

const calculateShippingFromCart = async (cartItems, address) => {
  const data = {
    to_district_id: address.district_id,
    to_ward_code: address.ward_code,
    service_type_id: 2,
    cart_items: cartItems.map(item => ({ product_id: item.product_id, quantity: item.quantity }))
  };
  return await calculateCartShippingFee(data);
};

function formatShippingFee(fee) {
  if (!fee) return "0 VNĐ";
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(fee.total || fee);
}
function formatDeliveryTime(estimatedDelivery) {
  if (!estimatedDelivery) return "";
  return estimatedDelivery.description || `Giao hàng trong ${estimatedDelivery.min_days}-${estimatedDelivery.max_days} ngày`;
}
function resetShippingData() {
  shippingFee.value = null;
  provinces.value = [];
  districts.value = [];
  wards.value = [];
  errors.value = {};
}
function validateAddress(address) {
  const errors = {};
  if (!address.to_district_id) errors.district = "Vui lòng chọn quận/huyện";
  if (!address.to_ward_code) errors.ward = "Vui lòng chọn phường/xã";
  return { isValid: Object.keys(errors).length === 0, errors };
}

async function handleApi(fn, errorMsg) {
  try {
    loading.value = true;
    return await fn();
  } catch (err) {
    Swal.fire({ icon: "error", title: "Lỗi", text: err?.response?.data?.message || errorMsg });
    throw err;
  } finally {
    loading.value = false;
  }
}

export const useShipping = () => ({
  shippingFee, provinces, districts, wards, loading, errors, shippingError, shopInfo,
  getProvinces, getDistricts, getWards,
  fetchShopInfo, getShopLocation, getDistrictAndWardFromAddress, callGHNShippingAPI, calculateGHNShipping,
  calculateShippingFee, calculateCartShippingFee, calculateShippingFromCart,
  formatShippingFee, formatDeliveryTime, resetShippingData, validateAddress,
}); 