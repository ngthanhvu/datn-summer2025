<template>
  <div class="shipping-calculator">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-shipping-fast me-2"></i>
          Tính phí vận chuyển
        </h5>
      </div>
      <div class="card-body">
        <form @submit.prevent="calculateFee">
          <!-- Loại dịch vụ -->
          <div class="mb-3">
            <label class="form-label">Loại dịch vụ</label>
            <select v-model="form.service_type_id" class="form-select">
              <option value="2">Hàng nhẹ</option>
              <option value="5">Hàng nặng</option>
            </select>
          </div>

          <!-- Địa chỉ giao hàng -->
          <div class="row">
            <div class="col-md-4">
              <label class="form-label">Tỉnh/Thành phố</label>
              <select 
                v-model="form.province_id" 
                @change="onProvinceChange"
                class="form-select"
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
              <label class="form-label">Quận/Huyện</label>
              <select 
                v-model="form.district_id" 
                @change="onDistrictChange"
                class="form-select"
                :disabled="!form.province_id || loading"
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
              <label class="form-label">Phường/Xã</label>
              <select 
                v-model="form.ward_code" 
                class="form-select"
                :disabled="!form.district_id || loading"
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

          <!-- Thông tin hàng hóa -->
          <div class="row mt-3">
            <div class="col-md-3">
              <label class="form-label">Cân nặng (gram)</label>
              <input 
                v-model.number="form.weight" 
                type="number" 
                class="form-control"
                placeholder="Nhập cân nặng"
                min="1"
                max="1600000"
              />
            </div>
            <div class="col-md-3">
              <label class="form-label">Chiều dài (cm)</label>
              <input 
                v-model.number="form.length" 
                type="number" 
                class="form-control"
                placeholder="Chiều dài"
                min="1"
                max="200"
              />
            </div>
            <div class="col-md-3">
              <label class="form-label">Chiều rộng (cm)</label>
              <input 
                v-model.number="form.width" 
                type="number" 
                class="form-control"
                placeholder="Chiều rộng"
                min="1"
                max="200"
              />
            </div>
            <div class="col-md-3">
              <label class="form-label">Chiều cao (cm)</label>
              <input 
                v-model.number="form.height" 
                type="number" 
                class="form-control"
                placeholder="Chiều cao"
                min="1"
                max="200"
              />
            </div>
          </div>

          <!-- Giá trị khai giá -->
          <div class="row mt-3">
            <div class="col-md-6">
              <label class="form-label">Giá trị khai giá (VNĐ)</label>
              <input 
                v-model.number="form.insurance_value" 
                type="number" 
                class="form-control"
                placeholder="Giá trị hàng hóa"
                min="0"
                max="5000000"
              />
            </div>
            <div class="col-md-6">
              <label class="form-label">Tiền thu hộ (VNĐ)</label>
              <input 
                v-model.number="form.cod_value" 
                type="number" 
                class="form-control"
                placeholder="Tiền thu hộ"
                min="0"
                max="10000000"
              />
            </div>
          </div>

          <!-- Nút tính phí -->
          <div class="mt-4">
            <button 
              type="submit" 
              class="btn btn-primary"
              :disabled="loading || !isFormValid"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
              {{ loading ? 'Đang tính...' : 'Tính phí vận chuyển' }}
            </button>
          </div>
        </form>

        <!-- Kết quả tính phí -->
        <div v-if="shippingFee" class="mt-4">
          <div class="alert alert-info">
            <h6 class="alert-heading">Kết quả tính phí</h6>
            <div class="row">
              <div class="col-md-6">
                <p class="mb-1"><strong>Phí dịch vụ:</strong> {{ formatShippingFee(shippingFee.service_fee) }}</p>
                <p class="mb-1"><strong>Phí khai giá:</strong> {{ formatShippingFee(shippingFee.insurance_fee) }}</p>
                <p class="mb-1"><strong>Tổng phí:</strong> {{ formatShippingFee(shippingFee.total) }}</p>
              </div>
              <div class="col-md-6">
                <p class="mb-1"><strong>Phí COD:</strong> {{ formatShippingFee(shippingFee.cod_fee) }}</p>
                <p class="mb-1"><strong>Phí vùng xa:</strong> {{ formatShippingFee(shippingFee.deliver_remote_areas_fee) }}</p>
                <p class="mb-1"><strong>Thời gian giao:</strong> {{ formatDeliveryTime(estimatedDelivery) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useShipping } from '../../composable/useShipping';

const {
  shippingFee,
  provinces,
  districts,
  wards,
  loading,
  getProvinces,
  getDistricts,
  getWards,
  calculateShippingFee,
  formatShippingFee,
  formatDeliveryTime,
} = useShipping();

const form = ref({
  service_type_id: 2,
  province_id: '',
  district_id: '',
  ward_code: '',
  weight: 1000,
  length: 30,
  width: 40,
  height: 20,
  insurance_value: 0,
  cod_value: 0,
});

const estimatedDelivery = ref(null);

// Computed
const isFormValid = computed(() => {
  return form.value.province_id && 
         form.value.district_id && 
         form.value.ward_code && 
         form.value.weight > 0;
});

// Methods
const onProvinceChange = async () => {
  form.value.district_id = '';
  form.value.ward_code = '';
  if (form.value.province_id) {
    await getDistricts(form.value.province_id);
  }
};

const onDistrictChange = async () => {
  form.value.ward_code = '';
  if (form.value.district_id) {
    await getWards(form.value.district_id);
  }
};

const calculateFee = async () => {
  try {
    const data = {
      service_type_id: parseInt(form.value.service_type_id),
      to_district_id: parseInt(form.value.district_id),
      to_ward_code: form.value.ward_code,
      weight: form.value.weight,
      length: form.value.length,
      width: form.value.width,
      height: form.value.height,
      insurance_value: form.value.insurance_value,
      cod_value: form.value.cod_value,
    };

    const result = await calculateShippingFee(data);
    
    // Ước tính thời gian giao hàng
    const total = result.total || 0;
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
  } catch (error) {
    console.error('Error calculating shipping fee:', error);
  }
};

// Lifecycle
onMounted(async () => {
  await getProvinces();
});
</script>

<style scoped>
.shipping-calculator {
  max-width: 800px;
  margin: 0 auto;
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
  font-weight: 600;
  color: #5a5c69;
}

.alert-info {
  background-color: #d1ecf1;
  border-color: #bee5eb;
  color: #0c5460;
}

.alert-heading {
  color: #0c5460;
  font-weight: 600;
}
</style> 