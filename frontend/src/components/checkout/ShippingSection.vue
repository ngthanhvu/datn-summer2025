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
                <span class="shipping-value">{{ formatShippingFee(props.freeShipping ? 0 : shippingFee.total) }}</span>
              </div>
            </div>
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
import { useShipping } from '../../composable/useShipping';

const props = defineProps({
  cartItems: {
    type: Array,
    required: true
  },
  selectedAddress: {
    type: Object,
    default: null
  },
  freeShipping: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['shipping-calculated']);

const { 
  loading, 
  shippingError, 
  fetchShopInfo, 
  calculateGHNShipping,
  formatShippingFee,
  formatDeliveryTime
} = useShipping();

const shippingFee = ref(null);
const estimatedDelivery = ref(null);

const isAddressComplete = computed(() => {
  return !!props.selectedAddress && Array.isArray(props.cartItems) && props.cartItems.length > 0;
});

const handleShippingCalculation = async () => {
  if (!isAddressComplete.value) {
    return;
  }

  emit('shipping-calculated', { loading: true });

  const result = await calculateGHNShipping(props.selectedAddress, props.cartItems);
  
  if (result) {
    shippingFee.value = result.shippingFee;
    estimatedDelivery.value = result.estimatedDelivery;
    
    emit('shipping-calculated', {
      shippingFee: result.shippingFee,
      estimatedDelivery: result.estimatedDelivery,
      address: props.selectedAddress,
      zone: result.zone,
      loading: false
    });
  } else {
    shippingFee.value = null;
    estimatedDelivery.value = null;
    emit('shipping-calculated', { loading: false });
  }
};

watch(() => props.cartItems, () => {
  if (isAddressComplete.value) {
    handleShippingCalculation();
  }
}, { deep: true, immediate: true });

watch(() => props.selectedAddress, (newAddress, oldAddress) => {
  if (oldAddress && newAddress && oldAddress.id !== newAddress.id) {
    shippingFee.value = null;
    estimatedDelivery.value = null;
    emit('shipping-calculated', { loading: false });
  }

  if (isAddressComplete.value) {
    handleShippingCalculation();
  }
}, { deep: true, immediate: true });

onMounted(async () => {
  await fetchShopInfo();
  if (isAddressComplete.value) {
    handleShippingCalculation();
  }
});

defineExpose({
  handleShippingCalculation
});
</script>

