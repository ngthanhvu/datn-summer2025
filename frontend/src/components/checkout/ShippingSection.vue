<template>
  <div class="shipping-section">
    <div class="card">
      <div class="card-header">
        <h6 class="card-title mb-0">
          <i class="fas fa-truck me-2"></i>
          Thông tin vận chuyển
        </h6>
      </div>
      <div class="card-body">
        <!-- Hiển thị địa chỉ đã chọn -->
        <div class="mb-4" v-if="selectedAddress">
          <label class="form-label fw-bold">Địa chỉ giao hàng</label>
          <div class="alert alert-info">
          <div class="row">
              <div class="col-12">
                <p class="mb-1"><strong>Người nhận:</strong> {{ selectedAddress.fullName }}</p>
                <p class="mb-1"><strong>Số điện thoại:</strong> {{ selectedAddress.phone }}</p>
                <p class="mb-1"><strong>Địa chỉ:</strong> {{ selectedAddress.fullAddress }}</p>
                <p class="mb-1"><strong>Dịch vụ:</strong> GHN Express</p>
            </div>
            </div>
          </div>
        </div>

        <!-- Thông báo khi chưa chọn địa chỉ -->
        <div v-else class="alert alert-warning">
          <i class="fas fa-exclamation-triangle me-2"></i>
          Vui lòng chọn địa chỉ giao hàng ở trên
        </div>

        <!-- Thông tin phí vận chuyển -->
        <div v-if="shippingFee && selectedAddress" class="shipping-info">
          <div class="alert alert-success">
            <div class="row">
              <div class="col-md-6">
                <p class="mb-1"><strong>Phí vận chuyển:</strong> {{ formatShippingFee(shippingFee.total) }}</p>
                <p class="mb-1"><strong>Thời gian giao:</strong> {{ formatDeliveryTime(estimatedDelivery) }}</p>
                <p class="mb-1"><strong>Dịch vụ:</strong> GHN Express</p>
              </div>
              <div class="col-md-6">
                <p class="mb-1"><strong>Phí khai giá:</strong> {{ formatShippingFee(shippingFee.insurance_fee || 0) }}</p>
                <p class="mb-1"><strong>Phí COD:</strong> {{ formatShippingFee(shippingFee.cod_fee || 0) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Loading state -->
        <div v-if="loading && selectedAddress" class="text-center py-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Đang tính phí...</span>
          </div>
          <p class="mt-2 text-muted">Đang tính phí vận chuyển...</p>
        </div>

        <!-- Error state -->
        <div v-if="shippingError" class="alert alert-danger">
          <i class="fas fa-exclamation-triangle me-2"></i>
          {{ shippingError }}
        </div>
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
    
    // Nếu không lấy được thông tin shop, trả về lỗi
    throw new Error('Không thể lấy thông tin shop từ GHN API');
  } catch (error) {
    throw new Error('Không thể lấy thông tin shop từ GHN API');
  }
};

const getDistrictAndWardFromAddress = async (address) => {
  try {
    console.log('Getting district and ward for address:', address);
    
    // Lấy danh sách tỉnh từ GHN API
    const provincesResponse = await axios.get('/api/shipping/provinces');
    if (!provincesResponse.data.success) {
      throw new Error('Không thể lấy danh sách tỉnh');
    }
    
    const provinces = provincesResponse.data.data;
    console.log('Available provinces:', provinces.map(p => p.ProvinceName));
    
    // Tìm province_id từ tên tỉnh
    const provinceName = address.province?.replace('Tỉnh ', '').replace('Thành phố ', '');
    console.log('Looking for province:', provinceName);
    
    let province = provinces.find(p => p.ProvinceName === provinceName);
    
    if (!province) {
      // Thử tìm kiếm không phân biệt hoa thường
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
    
    // Lấy danh sách huyện từ GHN API
    const districtsResponse = await axios.get(`/api/shipping/districts?province_id=${province.ProvinceID}`);
    if (!districtsResponse.data.success) {
      throw new Error('Không thể lấy danh sách huyện');
    }
    
    const districts = districtsResponse.data.data;
    console.log('Available districts:', districts.map(d => d.DistrictName));
    
    // Tìm district_id từ tên huyện
    const districtName = address.district?.replace('Huyện ', '').replace('Quận ', '');
    console.log('Looking for district:', districtName);
    
    let district = districts.find(d => d.DistrictName === districtName);
    
    if (!district) {
      // Thử tìm kiếm không phân biệt hoa thường
      district = districts.find(d => 
        d.DistrictName.toLowerCase() === districtName.toLowerCase()
      );
      
      if (district) {
        console.log('Found district (case insensitive):', district.DistrictName);
      } else {
        // Thử tìm kiếm partial match
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
    
    // Lấy danh sách xã từ GHN API
    const wardsResponse = await axios.get(`/api/shipping/wards?district_id=${district.DistrictID}`);
    if (!wardsResponse.data.success) {
      throw new Error('Không thể lấy danh sách xã');
    }
    
    const wards = wardsResponse.data.data;
    console.log('Available wards:', wards.map(w => w.WardName));
    
    // Tìm ward_code từ tên xã
    const wardName = address.ward?.replace('Xã ', '').replace('Phường ', '');
    console.log('Looking for ward:', wardName);
    
    let ward = wards.find(w => w.WardName === wardName);
    
    if (!ward) {
      // Thử tìm kiếm không phân biệt hoa thường
      ward = wards.find(w => 
        w.WardName.toLowerCase() === wardName.toLowerCase()
      );
      
      if (ward) {
        console.log('Found ward (case insensitive):', ward.WardName);
      } else {
        // Thử tìm kiếm partial match
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
  // Chỉ sử dụng GHN API, không cần xác định zone
  return 'GHN API';
};

const calculateShippingByZone = (zone, totalValue) => {
  // Chỉ sử dụng GHN API, không cần tính theo zone
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

    // Lấy thông tin địa chỉ đích từ address thực tế
    let toDistrictId = address.district_id;
    let toWardCode = address.ward_code;

    // Nếu không có district_id và ward_code, lấy từ GHN API
    if (!toDistrictId || !toWardCode) {
      console.log('Getting district and ward from GHN API...');
      const locationInfo = await getDistrictAndWardFromAddress(address);
      toDistrictId = locationInfo.district_id;
      toWardCode = locationInfo.ward_code;
    }

    console.log('Destination district_id:', toDistrictId);
    console.log('Destination ward_code:', toWardCode);

    // Validate dữ liệu trước khi gọi API
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

    // Chuẩn bị dữ liệu cho GHN API
    const shippingData = {
      service_type_id: 2, // Hàng nhẹ
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
      
      // Sử dụng thông tin từ GHN API
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

watch(() => props.selectedAddress, () => {
  if (isAddressComplete.value) {
    calculateShipping();
  }
}, { deep: true });

onMounted(() => {
  fetchShopInfo();
});
</script>

<style scoped>
.shipping-section {
  margin-bottom: 1.5rem;
}

.card {
  border: 1px solid #e3e6f0;
  border-radius: 0.35rem;
  box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.card-header {
  background-color: #f8f9fc;
  border-bottom: 1px solid #e3e6f0;
  padding: 1rem 1.25rem;
}

.card-title {
  color: #5a5c69;
  font-weight: 700;
}

.form-label {
  color: #5a5c69;
}

.alert-success {
  background-color: #d1e7dd;
  border-color: #badbcc;
  color: #0f5132;
}

.alert-danger {
  background-color: #f8d7da;
  border-color: #f5c2c7;
  color: #842029;
}

.alert-info {
  background-color: #d1ecf1;
  border-color: #bee5eb;
  color: #0c5460;
}

.alert-warning {
  background-color: #fff3cd;
  border-color: #ffeaa7;
  color: #856404;
}

.shipping-info {
  margin-top: 1rem;
}
</style> 