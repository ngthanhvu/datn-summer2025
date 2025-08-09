<template>
  <div class="coupons-section">
    <div class="coupon-title">MÃ GIẢM GIÁ HOT</div>
    <div 
      v-for="(coupon, index) in coupons" 
      :key="coupon.id || index"
      class="coupon-item"
      :class="index === 0 ? 'coupon-item-premium' : 'coupon-item-standard'"
    >
      <div class="coupon-details">
        <div class="coupon-name">{{ coupon.name || 'Mã giảm giá' }}</div>
        <div class="coupon-code">{{ coupon.code }}</div>
        <div class="coupon-discount">
          <span v-if="coupon.type === 'percent'">
            Giảm {{ coupon.value }}% (Tối đa: {{ formatPrice(coupon.max_discount_value || 0) }})
          </span>
          <span v-else>
            Giảm {{ formatPrice(coupon.value) }}
          </span>
        </div>
        <div class="coupon-min-order">Đơn tối thiểu: {{ formatPrice(coupon.min_order_value || 0) }}</div>
        <div v-if="coupon.description" class="coupon-desc">{{ coupon.description }}</div>
      </div>
      <div class="coupon-badge">HOT</div>
    </div>
  </div>
</template>

<script>
import { useAIChat } from '../../composable/useAIChat'

export default {
  name: 'CouponCard',
  props: {
    coupons: {
      type: Array,
      required: true
    }
  },
  setup() {
    const { formatPrice } = useAIChat()
    
    return {
      formatPrice
    }
  }
}
</script>

<style scoped>
.coupons-section {
  margin: 12px 0;
  padding: 16px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(102, 126, 234, 0.4);
  border: 2px solid rgba(255, 255, 255, 0.3);
  position: relative;
  overflow: hidden;
}

.coupons-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  animation: shimmer 3s infinite;
}

@keyframes shimmer {
  0% { left: -100%; }
  100% { left: 100%; }
}

.coupon-title {
  text-align: center;
  color: white;
  font-weight: 800;
  font-size: 16px;
  margin-bottom: 16px;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
  animation: pulse 2s infinite;
  position: relative;
  z-index: 1;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.coupon-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  margin: 12px 0;
  border-radius: 12px;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  border: 2px solid rgba(255, 255, 255, 0.2);
  cursor: pointer;
}

.coupon-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
}

.coupon-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: left 0.6s ease;
}

.coupon-item:hover::before {
  left: 100%;
}

.coupon-item-premium {
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
  border: 3px solid #ffd700;
  box-shadow: 0 8px 30px rgba(255, 107, 107, 0.5);
}

.coupon-item-standard {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  border: 2px solid #4facfe;
  box-shadow: 0 6px 25px rgba(79, 172, 254, 0.4);
}

.coupon-details {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.coupon-name {
  color: white;
  font-weight: 700;
  font-size: 15px;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  letter-spacing: 0.5px;
  line-height: 1.3;
}

.coupon-code {
  color: #ffeaa7;
  font-weight: 800;
  font-size: 16px;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  letter-spacing: 1px;
  background: rgba(255, 255, 255, 0.1);
  padding: 4px 10px;
  border-radius: 16px;
  display: inline-block;
  border: 1px solid rgba(255, 255, 255, 0.2);
  align-self: flex-start;
}

.coupon-discount {
  color: #ffeaa7;
  font-weight: 600;
  font-size: 13px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.coupon-min-order {
  color: rgba(255, 255, 255, 0.9);
  font-size: 12px;
  font-weight: 500;
}

.coupon-desc {
  color: rgba(255, 255, 255, 0.8);
  font-size: 11px;
  font-style: italic;
  line-height: 1.4;
}

.coupon-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  background: linear-gradient(135deg, #ff4757 0%, #ff3742 100%);
  color: white;
  font-size: 10px;
  font-weight: 800;
  padding: 6px 10px;
  border-radius: 15px;
  text-transform: uppercase;
  letter-spacing: 1px;
  animation: flash 1.5s infinite;
  box-shadow: 0 4px 12px rgba(255, 71, 87, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

@keyframes flash {
  0%, 50%, 100% { opacity: 1; transform: scale(1); }
  25%, 75% { opacity: 0.7; transform: scale(1.1); }
}
</style>
