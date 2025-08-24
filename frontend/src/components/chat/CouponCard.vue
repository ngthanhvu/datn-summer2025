<template>
  <div class="coupons-section">
    <div class="coupon-title">MÃ GIẢM GIÁ HOT</div>
    <div v-for="(coupon, index) in coupons" :key="coupon.id || index" class="coupon-item"
      :class="index === 0 ? 'coupon-item-premium' : 'coupon-item-standard'">
      <div class="coupon-details">
        <div class="coupon-name">{{ coupon.name || 'Mã giảm giá' }}</div>
        <div class="coupon-code">{{ coupon.code }}</div>
        <div class="coupon-discount">
          <span v-if="coupon.type === 'percent'">
            Giảm {{ coupon.value }}%
            <span v-if="coupon.max_discount_value && coupon.max_discount_value > 0">
              (Tối đa: {{ formatPrice(coupon.max_discount_value) }})
            </span>
          </span>
          <span v-else>
            Giảm {{ formatPrice(coupon.value) }}
          </span>
        </div>
        <div class="coupon-min-order">
          Đơn tối thiểu: {{ formatPrice(coupon.min_order_value || 0) }}
        </div>
        <div v-if="coupon.end_date" class="coupon-expiry">
          Hạn sử dụng: {{ formatDate(coupon.end_date) }}
        </div>
        <div v-if="coupon.description" class="coupon-desc">{{ coupon.description }}</div>

        <!-- Nút Lấy ngay -->
        <button @click="saveCoupon(coupon)" class="save-coupon-btn" :disabled="coupon.saved">
          <span v-if="coupon.saved">✅ Đã lưu</span>
          <span v-else>🎯 Lấy ngay</span>
        </button>

        <!-- Nút Sử dụng (chỉ hiện khi đã lưu) -->
        <button v-if="coupon.saved" @click="useCoupon(coupon.id)" class="use-coupon-btn" :disabled="coupon.used">
          <span v-if="coupon.used">🔴 Đã sử dụng</span>
          <span v-else>💳 Sử dụng ngay</span>
        </button>
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
  emits: ['save-coupon', 'use-coupon'],
  setup(props, { emit }) {
    const { formatPrice } = useAIChat()

    const formatDate = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString('vi-VN')
    }

    const saveCoupon = (coupon) => {
      coupon.saved = true
      emit('save-coupon', coupon)
    }

    const useCoupon = (couponId) => {
      emit('use-coupon', couponId)
    }

    return {
      formatPrice,
      formatDate,
      saveCoupon,
      useCoupon
    }
  }
}
</script>

<style scoped>
@import url('../../assets/css/chat-animations.css');

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

.coupon-expiry {
  color: #ff7675;
  font-size: 11px;
  font-weight: 600;
  background: rgba(255, 118, 117, 0.1);
  padding: 4px 8px;
  border-radius: 8px;
  border: 1px solid rgba(255, 118, 117, 0.3);
  align-self: flex-start;
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

/* Nút Lấy ngay */
.save-coupon-btn {
  margin-top: 12px;
  padding: 8px 16px;
  background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
  color: white;
  border: none;
  border-radius: 20px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0, 184, 148, 0.4);
  border: 2px solid rgba(255, 255, 255, 0.3);
  align-self: flex-start;
  position: relative;
  overflow: hidden;
}

.save-coupon-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: left 0.5s ease;
}

.save-coupon-btn:hover::before {
  left: 100%;
}

.save-coupon-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 184, 148, 0.6);
  background: linear-gradient(135deg, #00a085 0%, #00916e 100%);
}

.save-coupon-btn:active {
  transform: translateY(0);
}

.save-coupon-btn:disabled {
  background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
  cursor: not-allowed;
  transform: none;
  box-shadow: 0 2px 8px rgba(149, 165, 166, 0.4);
}

.save-coupon-btn:disabled:hover {
  transform: none;
  box-shadow: 0 2px 8px rgba(149, 165, 166, 0.4);
}

/* Nút Sử dụng */
.use-coupon-btn {
  margin-top: 8px;
  margin-left: 8px;
  padding: 8px 16px;
  background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
  color: white;
  border: none;
  border-radius: 20px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(243, 156, 18, 0.4);
  border: 2px solid rgba(255, 255, 255, 0.3);
  align-self: flex-start;
  position: relative;
  overflow: hidden;
}

.use-coupon-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: left 0.5s ease;
}

.use-coupon-btn:hover::before {
  left: 100%;
}

.use-coupon-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(243, 156, 18, 0.6);
  background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
}

.use-coupon-btn:active {
  transform: translateY(0);
}

.use-coupon-btn:disabled {
  background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
  cursor: not-allowed;
  transform: none;
  box-shadow: 0 2px 8px rgba(149, 165, 166, 0.4);
}

.use-coupon-btn:disabled:hover {
  transform: none;
  box-shadow: 0 2px 8px rgba(149, 165, 166, 0.4);
}
</style>
