<template>
  <div class="shipping-section">
    <div class="card-body">
      <div v-if="!selectedAddress" class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Vui lòng chọn địa chỉ giao hàng ở trên
      </div>
      <div v-if="shippingFee && selectedAddress && !loading" class="shipping-info">
        <div class="shipping-card">
          <div class="shipping-header">
            <i class="fas fa-shipping-fast me-2"></i>
            <span>Phương thức vận chuyển</span>
          </div>
          <div class="shipping-content">
            <div class="shipping-row">
              <div class="shipping-item">
                <span class="shipping-label">Dịch vụ:</span>
                <span class="shipping-value">GHN Express</span>
              </div>
              <div class="shipping-item">
                <span class="shipping-label">Phí vận chuyển:</span>
                <span class="shipping-value">{{ formatShippingFee(shippingFee.total) }}</span>
              </div>
            </div>
            <!-- <div class="shipping-row">
              <div class="shipping-item">
                <span class="shipping-label">Phí khai giá:</span>
                <span class="shipping-value">{{ formatShippingFee(shippingFee.insurance_fee || 0) }}</span>
              </div>
            </div> -->
            <div class="shipping-row">
              <div class="shipping-item">
                <span class="shipping-label">Thời gian giao:</span>
                <span class="shipping-value">{{ formatDeliveryTime(estimatedDelivery) }}</span>
              </div>
              <div class="shipping-item">
                <span class="shipping-label">Phí COD:</span>
                <span class="shipping-value">{{ formatShippingFee(shippingFee.cod_fee || 0) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading state -->
      <div v-if="loading && selectedAddress" class="loading-container">
        <div class="loading-spinner">
          <div class="spinner"></div>
        </div>
        <div class="loading-text">
          <p class="loading-title">Đang tính phí vận chuyển...</p>
          <p class="loading-subtitle">Vui lòng chờ trong giây lát</p>
        </div>
      </div>

      <!-- Error state -->
      <div v-if="shippingError" class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ shippingError }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  cartItems: {
    type: Array,
    required: true
  },
  selectedAddress: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['shipping-calculated']);

const loading = ref(false);
const shippingFee = ref(null);
const shippingError = ref('');
const estimatedDelivery = ref(null);
const shippingZone = ref('');

const shopInfo = ref(null);

const fetchShopInfo = async () => {
  try {
    const response = await axios.get('/api/shipping/config');

    if (response.data.success) {
      shopInfo.value = response.data.data;
    } else {
      // Handle error silently
    }
  } catch (error) {
    // Handle error silently
  }
};

const getShopLocation = async () => {
  try {
    if (!shopInfo.value) {
      await fetchShopInfo();
    }

    if (shopInfo.value && shopInfo.value.shop_info) {
      return {
        districtId: shopInfo.value.shop_info.district_id,
        wardCode: shopInfo.value.shop_info.ward_code
      };
    }

    throw new Error('Không thể lấy thông tin shop từ GHN API');
  } catch (error) {
    throw new Error('Không thể lấy thông tin shop từ GHN API');
  }
};

const getDistrictAndWardFromAddress = async (address) => {
  try {
    console.log('Getting district and ward for address:', address);

    const provincesResponse = await axios.get('/api/shipping/provinces');
    if (!provincesResponse.data.success) {
      throw new Error('Không thể lấy danh sách tỉnh');
    }

    const provinces = provincesResponse.data.data;
    console.log('Available provinces:', provinces.map(p => p.ProvinceName));

    const provinceName = address.province?.replace('Tỉnh ', '').replace('Thành phố ', '');
    console.log('Looking for province:', provinceName);

    let province = provinces.find(p => p.ProvinceName === provinceName);

    if (!province) {
      const provinceIgnoreCase = provinces.find(p =>
        p.ProvinceName.toLowerCase() === provinceName.toLowerCase()
      );

      if (provinceIgnoreCase) {
        console.log('Found province (case insensitive):', provinceIgnoreCase.ProvinceName);
        province = provinceIgnoreCase;
      } else {
        throw new Error(`Không tìm thấy tỉnh: ${provinceName}`);
      }
    }

    console.log('Found province:', province);

    const districtsResponse = await axios.get(`/api/shipping/districts?province_id=${province.ProvinceID}`);
    if (!districtsResponse.data.success) {
      throw new Error('Không thể lấy danh sách huyện');
    }

    const districts = districtsResponse.data.data;
    console.log('Available districts:', districts.map(d => d.DistrictName));

    const districtName = address.district?.replace('Huyện ', '').replace('Quận ', '');
    console.log('Looking for district:', districtName);

    let district = districts.find(d => d.DistrictName === districtName);

    if (!district) {
      district = districts.find(d =>
        d.DistrictName.toLowerCase() === districtName.toLowerCase()
      );

      if (district) {
        console.log('Found district (case insensitive):', district.DistrictName);
      } else {
        district = districts.find(d =>
          d.DistrictName.toLowerCase().includes(districtName.toLowerCase()) ||
          districtName.toLowerCase().includes(d.DistrictName.toLowerCase())
        );

        if (district) {
          console.log('Found district (partial match):', district.DistrictName);
        } else {
          throw new Error(`Không tìm thấy huyện: ${districtName}`);
        }
      }
    }

    console.log('Found district:', district);

    const wardsResponse = await axios.get(`/api/shipping/wards?district_id=${district.DistrictID}`);
    if (!wardsResponse.data.success) {
      throw new Error('Không thể lấy danh sách xã');
    }

    const wards = wardsResponse.data.data;
    console.log('Available wards:', wards.map(w => w.WardName));

    const wardName = address.ward?.replace('Xã ', '').replace('Phường ', '');
    console.log('Looking for ward:', wardName);

    let ward = wards.find(w => w.WardName === wardName);

    if (!ward) {
      ward = wards.find(w =>
        w.WardName.toLowerCase() === wardName.toLowerCase()
      );

      if (ward) {
        console.log('Found ward (case insensitive):', ward.WardName);
      } else {
        ward = wards.find(w =>
          w.WardName.toLowerCase().includes(wardName.toLowerCase()) ||
          wardName.toLowerCase().includes(w.WardName.toLowerCase())
        );

        if (ward) {
          console.log('Found ward (partial match):', ward.WardName);
        } else {
          throw new Error(`Không tìm thấy xã: ${wardName}`);
        }
      }
    }

    console.log('Found ward:', ward);

    return {
      district_id: district.DistrictID,
      ward_code: ward.WardCode
    };
  } catch (error) {
    console.error('Error getting district and ward:', error);
    throw error;
  }
};

const isAddressComplete = computed(() => {
  return props.selectedAddress && props.cartItems.length > 0;
});

// Methods
const determineShippingZone = (address) => {
  return 'GHN API';
};

const calculateShippingByZone = (zone, totalValue) => {
  return { deliveryTime: { min_days: 1, max_days: 3, description: 'Giao hàng trong 1-3 ngày' } };
};

const callGHNShippingAPI = async (address) => {
  try {
    console.log('Address data:', address);

    const totalWeight = props.cartItems.reduce((total, item) => {
      return total + (500 * item.quantity);
    }, 0);

    const totalValue = props.cartItems.reduce((total, item) => {
      return total + (item.price * item.quantity);
    }, 0);

    const shopLocation = await getShopLocation();
    console.log('Shop location:', shopLocation);

    if (!shopInfo.value) {
      await fetchShopInfo();
    }

    const ghnConfig = shopInfo.value || {
      base_url: 'https://online-gateway.ghn.vn/shiip/public-api/v2',
      token: '',
      shop_id: ''
    };

    let toDistrictId = address.district_id;
    let toWardCode = address.ward_code;

    if (!toDistrictId || !toWardCode) {
      console.log('Getting district and ward from GHN API...');
      const locationInfo = await getDistrictAndWardFromAddress(address);
      toDistrictId = locationInfo.district_id;
      toWardCode = locationInfo.ward_code;
    }

    console.log('Destination district_id:', toDistrictId);
    console.log('Destination ward_code:', toWardCode);

    if (!toDistrictId || !toWardCode) {
      return {
        success: false,
        message: 'Thiếu thông tin district_id hoặc ward_code của địa chỉ giao hàng'
      };
    }

    if (!shopLocation.districtId || !shopLocation.wardCode) {
      return {
        success: false,
        message: 'Không thể lấy thông tin shop từ GHN API'
      };
    }

    const shippingData = {
      service_type_id: 2, 
      from_district_id: shopLocation.districtId,
      from_ward_code: shopLocation.wardCode,
      to_district_id: toDistrictId,
      to_ward_code: toWardCode,
      weight: totalWeight,
      length: 30,
      width: 40,
      height: 20,
      insurance_value: totalValue,
      cod_value: 0
    };

    console.log('GHN API Request Data:', shippingData);

    const response = await axios.post(`${ghnConfig.base_url}/shipping-order/fee`, shippingData, {
      headers: {
        'Content-Type': 'application/json',
        'Token': ghnConfig.token,
        'ShopId': ghnConfig.shop_id,
      }
    });

    if (response.data.code === 200) {
      return {
        success: true,
        data: response.data.data
      };
    } else {
      console.log('GHN API Error Response:', response.data);
      return {
        success: false,
        message: response.data.message || 'Có lỗi xảy ra khi tính phí'
      };
    }
  } catch (error) {
    console.log('GHN API Error:', error.response?.data || error.message);
    return {
      success: false,
      message: error.response?.data?.message || error.message || 'Không thể kết nối đến GHN API'
    };
  }
};

const calculateShipping = async () => {
  if (!isAddressComplete.value) {
    return;
  }

  try {
    loading.value = true;
    shippingError.value = '';

    const result = await callGHNShippingAPI(props.selectedAddress);

    if (result.success) {
      shippingFee.value = result.data;

      estimatedDelivery.value = {
        min_days: 1,
        max_days: 3,
        description: 'Giao hàng trong 1-3 ngày'
      };

      emit('shipping-calculated', {
        shippingFee: result.data,
        estimatedDelivery: estimatedDelivery.value,
        address: props.selectedAddress,
        zone: 'GHN API'
      });
    } else {
      shippingError.value = result.message || 'Không thể tính phí vận chuyển cho địa chỉ này';
    }

  } catch (error) {
    shippingError.value = 'Không thể tính phí vận chuyển cho địa chỉ này';
  } finally {
    loading.value = false;
  }
};

const formatShippingFee = (fee) => {
  if (!fee) return "0 VNĐ";
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(fee);
};

const formatDeliveryTime = (estimatedDelivery) => {
  if (!estimatedDelivery) return "";
  return estimatedDelivery.description || `Giao hàng trong ${estimatedDelivery.min_days}-${estimatedDelivery.max_days} ngày`;
};

watch(() => props.cartItems, () => {
  if (isAddressComplete.value) {
    calculateShipping();
  }
}, { deep: true });

watch(() => props.selectedAddress, (newAddress, oldAddress) => {
  if (oldAddress && newAddress && oldAddress.id !== newAddress.id) {
    shippingFee.value = null;
    estimatedDelivery.value = null;
    shippingError.value = '';
  }

  if (isAddressComplete.value) {
    calculateShipping();
  }
}, { deep: true });

onMounted(() => {
  fetchShopInfo();
});
</script>

