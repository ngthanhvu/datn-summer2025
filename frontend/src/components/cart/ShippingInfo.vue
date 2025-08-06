<template>
  <div class="shipping-info">
    <div class="card">
      <div class="card-header">
        <h6 class="card-title mb-0">
          <i class="fas fa-truck me-2"></i>
          Thông tin vận chuyển
        </h6>
      </div>
      <div class="card-body">
        <!-- Chọn địa chỉ giao hàng -->
        <div class="mb-3">
          <label class="form-label fw-bold">Địa chỉ giao hàng</label>
          <div class="row">
            <div class="col-md-4">
              <select 
                v-model="selectedAddress.province_id" 
                @change="onProvinceChange"
                class="form-select form-select-sm"
                :disabled="loading"
              >
                <option value="">Chọn tỉnh/thành phố</option>
                <option 
                  v-for="province in provinces" 
                  :key="province.ProvinceID"
                  :value="province.ProvinceID"
                >
                  {{ province.ProvinceName }}
                </option>
              </select>
            </div>
            <div class="col-md-4">
              <select 
                v-model="selectedAddress.district_id" 
                @change="onDistrictChange"
                class="form-select form-select-sm"
                :disabled="!selectedAddress.province_id || loading"
              >
                <option value="">Chọn quận/huyện</option>
                <option 
                  v-for="district in districts" 
                  :key="district.DistrictID"
                  :value="district.DistrictID"
                >
                  {{ district.DistrictName }}
                </option>
              </select>
            </div>
            <div class="col-md-4">
              <select 
                v-model="selectedAddress.ward_code" 
                @change="calculateShipping"
                class="form-select form-select-sm"
                :disabled="!selectedAddress.district_id || loading"
              >
                <option value="">Chọn phường/xã</option>
                <option 
                  v-for="ward in wards" 
                  :key="ward.WardCode"
                  :value="ward.WardCode"
                >
                  {{ ward.WardName }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Thông tin phí vận chuyển -->
        <div v-if="shippingFee" class="shipping-details">
          <div class="alert alert-success alert-sm">
            <div class="row">
              <div class="col-md-6">
                <p class="mb-1"><strong>Phí vận chuyển:</strong> {{ formatShippingFee(shippingFee.total) }}</p>
                <p class="mb-1"><strong>Thời gian giao:</strong> {{ formatDeliveryTime(estimatedDelivery) }}</p>
              </div>
              <div class="col-md-6">
                <p class="mb-1"><strong>Phí khai giá:</strong> {{ formatShippingFee(shippingFee.insurance_fee) }}</p>
                <p class="mb-1"><strong>Phí COD:</strong> {{ formatShippingFee(shippingFee.cod_fee) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="text-center py-2">
          <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">Đang tính phí...</span>
          </div>
          <small class="text-muted">Đang tính phí vận chuyển...</small>
        </div>

        <!-- Error state -->
        <div v-if="shippingError" class="alert alert-danger alert-sm">
          <i class="fas fa-exclamation-triangle me-2"></i>
          {{ shippingError }}
        </div>

        <!-- Link đến trang tính phí chi tiết -->
        <div class="mt-3">
          <a href="/tinh-phi-van-chuyen" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-calculator me-2"></i>
            Tính phí chi tiết
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useShipping } from '../../composable/useShipping';

const props = defineProps({
  cartItems: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['shipping-calculated']);

const {
  shippingFee,
  provinces,
  districts,
  wards,
  loading,
  getProvinces,
  getDistricts,
  getWards,
  calculateCartShippingFee,
  formatShippingFee,
  formatDeliveryTime,
} = useShipping();

const selectedAddress = ref({
  province_id: '',
  district_id: '',
  ward_code: ''
});

const shippingError = ref('');
const estimatedDelivery = ref(null);

const isAddressComplete = computed(() => {
  return selectedAddress.value.province_id && 
         selectedAddress.value.district_id && 
         selectedAddress.value.ward_code;
});

// Methods
const onProvinceChange = async () => {
  selectedAddress.value.district_id = '';
  selectedAddress.value.ward_code = '';
  shippingFee.value = null;
  shippingError.value = '';
  
  if (selectedAddress.value.province_id) {
    await getDistricts(selectedAddress.value.province_id);
  }
};

const onDistrictChange = async () => {
  selectedAddress.value.ward_code = '';
  shippingFee.value = null;
  shippingError.value = '';
  
  if (selectedAddress.value.district_id) {
    await getWards(selectedAddress.value.district_id);
  }
};

const calculateShipping = async () => {
  if (!isAddressComplete.value || !props.cartItems.length) {
    return;
  }

  try {
    shippingError.value = '';
    
    const data = {
      to_district_id: parseInt(selectedAddress.value.district_id),
      to_ward_code: selectedAddress.value.ward_code,
      service_type_id: 2, 
      cart_items: props.cartItems.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity
      }))
    };

    const result = await calculateCartShippingFee(data);
    
    const total = result.shipping_fee?.total || 0;
    if (total <= 20000) {
      estimatedDelivery.value = {
        min_days: 1,
        max_days: 2,
        description: 'Giao hàng trong 1-2 ngày'
      };
    } else if (total <= 50000) {
      estimatedDelivery.value = {
        min_days: 2,
        max_days: 4,
        description: 'Giao hàng trong 2-4 ngày'
      };
    } else {
      estimatedDelivery.value = {
        min_days: 3,
        max_days: 7,
        description: 'Giao hàng trong 3-7 ngày'
      };
    }

    emit('shipping-calculated', {
      shippingFee: result.shipping_fee,
      estimatedDelivery: estimatedDelivery.value,
      address: selectedAddress.value
    });

  } catch (error) {
    console.error('Error calculating shipping:', error);
    shippingError.value = 'Không thể tính phí vận chuyển cho địa chỉ này';
  }
};

watch(() => props.cartItems, () => {
  if (isAddressComplete.value) {
    calculateShipping();
  }
}, { deep: true });

onMounted(async () => {
  await getProvinces();
});
</script>

<style scoped>
.shipping-info {
  margin-bottom: 1rem;
}

.card {
  border: 1px solid #e3e6f0;
  border-radius: 0.35rem;
  box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.card-header {
  background-color: #f8f9fc;
  border-bottom: 1px solid #e3e6f0;
  padding: 0.75rem 1rem;
}

.card-title {
  color: #5a5c69;
  font-weight: 700;
  font-size: 0.9rem;
}

.form-label {
  color: #5a5c69;
  font-size: 0.9rem;
}

.alert-sm {
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
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

.shipping-details {
  margin-top: 0.5rem;
}

.form-select-sm {
  font-size: 0.875rem;
  padding: 0.25rem 0.5rem;
}
</style> 