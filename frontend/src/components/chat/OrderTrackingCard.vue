<template>
  <div class="order-tracking-card">
    <div class="tracking-form">
      <h4>Tra cứu đơn hàng</h4>
      <p class="form-description">Nhập mã tra cứu để xem tình trạng</p>

      <div class="input-group">
        <div class="input-wrapper">
          <input v-model="trackingCode" type="text" placeholder="Nhập mã tra cứu..." class="tracking-input"
            @keyup.enter="searchOrder" />
          <button @click="searchOrder" :disabled="!trackingCode.trim() || isSearching" class="search-icon-btn"
            title="Tìm kiếm">
            <i v-if="isSearching" class="fas fa-spinner fa-spin"></i>
            <i v-else class="fas fa-search"></i>
          </button>
        </div>
        <button @click="searchOrder" :disabled="!trackingCode.trim() || isSearching" class="search-btn">
          <i v-if="isSearching" class="fas fa-spinner fa-spin"></i>
          <i v-else class="fas fa-search"></i>
          <span v-if="isSearching">Đang tìm...</span>
          <span v-else>Tìm kiếm</span>
        </button>
      </div>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>
    </div>

    <!-- Order details -->
    <div v-if="order" class="order-details">
      <div class="order-header">
        <h5>Thông tin đơn hàng</h5>
        <span class="order-status" :class="getStatusClass(order.status)">
          {{ getStatusText(order.status) }}
        </span>
      </div>

      <div class="order-info">
        <div class="info-row">
          <span class="label">Mã đơn hàng:</span>
          <span class="value">{{ order.tracking_code }}</span>
        </div>
        <div class="info-row">
          <span class="label">Ngày đặt:</span>
          <span class="value">{{ formatDate(order.created_at) }}</span>
        </div>
        <div class="info-row">
          <span class="label">Tổng tiền:</span>
          <span class="value">{{ formatPrice(order.final_price) }}</span>
        </div>
        <div class="info-row">
          <span class="label">Phương thức thanh toán:</span>
          <span class="value">{{ getPaymentMethodText(order.payment_method) }}</span>
        </div>
        <div class="info-row">
          <span class="label">Trạng thái thanh toán:</span>
          <span class="value" :class="getPaymentStatusClass(order.payment_status)">
            {{ getPaymentStatusText(order.payment_status) }}
          </span>
        </div>
      </div>

      <!-- Order items -->
      <div v-if="order.order_details && order.order_details.length > 0" class="order-items">
        <h6>📦 Sản phẩm đã đặt</h6>
        <div class="item-list">
          <div v-for="item in order.order_details" :key="item.id" class="order-item">
            <div class="item-image">
              <img :src="getItemImageUrl(item)" :alt="item.variant?.product?.name" @error="handleImageError" />
            </div>
            <div class="item-info">
              <div class="item-name">{{ item.variant?.product?.name }}</div>
              <div class="item-variant">
                Size: {{ item.variant?.size }} | Màu: {{ item.variant?.color }}
              </div>
              <div class="item-price">
                {{ formatPrice(item.price) }} x {{ item.quantity }}
              </div>
            </div>
            <!-- <div class="item-total">
              {{ formatPrice(item.total_price) }}
            </div> -->
          </div>
        </div>
      </div>

      <!-- No items message -->
      <div v-else-if="order.order_details && order.order_details.length === 0" class="order-items">
        <h6>📦 Sản phẩm đã đặt</h6>
        <div class="no-items-message">
          <p>Không có sản phẩm nào trong đơn hàng này</p>
        </div>
      </div>

      <!-- Shipping address -->
      <div v-if="order.address" class="shipping-address">
        <h6>📍 Địa chỉ giao hàng</h6>
        <div class="address-content">
          <p>{{ order.address.full_name }}</p>
          <p>{{ order.address.phone }}</p>
          <p>{{ order.address.address }}</p>
          <p>{{ order.address.city }}, {{ order.address.district }}, {{ order.address.ward }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useAIChat } from '../../composable/useAIChat'

export default {
  name: 'OrderTrackingCard',
  emits: ['order-found'],
  setup(props, { emit }) {
    const { searchOrder, formatPrice, getImageUrl, handleImageError } = useAIChat()

    const trackingCode = ref('')
    const order = ref(null)
    const error = ref('')
    const isSearching = ref(false)

    const searchOrderHandler = async () => {
      if (!trackingCode.value.trim()) return

      isSearching.value = true
      error.value = ''
      order.value = null

      try {
        const result = await searchOrder(trackingCode.value.trim())

        if (result.success && result.order) {
          order.value = result.order
          emit('order-found', result.order)
        } else {
          error.value = result.message || 'Không tìm thấy đơn hàng với mã tra cứu này'
          console.log('Order not found:', result.message)
        }
      } catch (err) {
        console.error('Order tracking error:', err)
        error.value = 'Có lỗi xảy ra khi tra cứu đơn hàng. Vui lòng thử lại sau.'
      } finally {
        isSearching.value = false
      }
    }

    const getStatusClass = (status) => {
      const statusClasses = {
        'pending': 'status-pending',
        'confirmed': 'status-confirmed',
        'processing': 'status-processing',
        'shipped': 'status-shipped',
        'delivered': 'status-delivered',
        'cancelled': 'status-cancelled',
        'returned': 'status-returned'
      }
      return statusClasses[status] || 'status-pending'
    }

    const getStatusText = (status) => {
      const statusTexts = {
        'pending': 'Chờ xác nhận',
        'confirmed': 'Đã xác nhận',
        'processing': 'Đang xử lý',
        'shipped': 'Đang giao hàng',
        'delivered': 'Đã giao hàng',
        'cancelled': 'Đã hủy',
        'returned': 'Đã trả hàng'
      }
      return statusTexts[status] || 'Chờ xác nhận'
    }

    const getPaymentMethodText = (method) => {
      const methodTexts = {
        'cod': 'Thanh toán khi nhận hàng',
        'vnpay': 'VnPay',
        'momo': 'MoMo'
      }
      return methodTexts[method] || method
    }

    const getPaymentStatusClass = (status) => {
      const statusClasses = {
        'pending': 'payment-pending',
        'paid': 'payment-paid',
        'failed': 'payment-failed'
      }
      return statusClasses[status] || 'payment-pending'
    }

    const getPaymentStatusText = (status) => {
      const statusTexts = {
        'pending': 'Chờ thanh toán',
        'paid': 'Đã thanh toán',
        'failed': 'Thanh toán thất bại'
      }
      return statusTexts[status] || 'Chờ thanh toán'
    }

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const getItemImageUrl = (item) => {
      // Sử dụng image_url từ backend (đã có /storage/)
      if (item.variant?.product?.mainImage?.image_url) {
        return item.variant.product.mainImage.image_url
      }
      if (item.variant?.product?.main_image?.image_url) {
        return item.variant.product.main_image.image_url
      }
      return 'https://placehold.co/100x100?text=No+Image'
    }

    return {
      trackingCode,
      order,
      error,
      isSearching,
      searchOrder: searchOrderHandler,
      formatPrice,
      getImageUrl,
      handleImageError,
      getStatusClass,
      getStatusText,
      getPaymentMethodText,
      getPaymentStatusClass,
      getPaymentStatusText,
      formatDate,
      getItemImageUrl
    }
  }
}
</script>

<style scoped>
.order-tracking-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.98) 100%);
  border-radius: 20px;
  padding: 28px;
  margin-top: 20px;
  border: 1px solid rgba(226, 232, 240, 0.6);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.06), 0 4px 16px rgba(102, 126, 234, 0.08);
  backdrop-filter: blur(20px);
  position: relative;
  overflow: hidden;
  max-width: 800px;
  margin-left: auto;
  margin-right: auto;
}

.order-tracking-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
  border-radius: 20px 20px 0 0;
}

.tracking-form h4 {
  margin: 0 0 10px 0;
  font-size: 18px;
  font-weight: 700;
  color: #1a202c;
  display: flex;
  align-items: center;
  gap: 10px;
  letter-spacing: -0.3px;
}

.tracking-form h4::before {
  content: '📦';
  font-size: 20px;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
}

.form-description {
  margin: 0 0 20px 0;
  font-size: 10px;
  color: #64748b;
  line-height: 1.5;
  font-weight: 500;
  letter-spacing: 0.1px;
}

.input-group {
  display: flex;
  gap: 14px;
  margin-bottom: 20px;
  position: relative;
  align-items: stretch;
}

.input-wrapper {
  flex: 1;
  position: relative;
  display: flex;
  align-items: center;
}

.tracking-input {
  flex: 1;
  padding: 10px 14px;
  padding-right: 16px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 14px;
  outline: none;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  background: rgba(255, 255, 255, 0.95);
  font-weight: 500;
  color: #1a202c;
  box-sizing: border-box;
  min-height: 42px;
}

.search-icon-btn {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  width: 28px;
  height: 28px;
  background: #f8fafc;
  color: #64748b;
  border: 1px solid #e2e8f0;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  z-index: 1;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}


.tracking-input::placeholder {
  color: #94a3b8;
  font-weight: 400;
}

.tracking-input:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1), 0 8px 25px rgba(102, 126, 234, 0.15);
  background: rgba(255, 255, 255, 1);
  transform: translateY(-2px);
}

.tracking-input:hover {
  border-color: #cbd5e1;
  background: rgba(255, 255, 255, 0.98);
  transform: translateY(-1px);
}

.search-btn {
  padding: 10px 16px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  white-space: nowrap;
  display: flex;
  align-items: center;
  gap: 6px;
  min-width: 100px;
  justify-content: center;
  position: relative;
  overflow: hidden;
  min-height: 42px;
  box-shadow: 0 4px 16px rgba(102, 126, 234, 0.25);
}

.search-btn i {
  font-size: 14px;
  transition: transform 0.3s ease;
}

.search-btn:hover:not(:disabled) i {
  transform: scale(1.1);
}

.search-icon-btn {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  width: 26px;
  height: 26px;
  background: #f1f5f9;
  /* nền sáng nhẹ, đồng bộ với input */
  color: #64748b;
  /* màu icon trung tính */
  border: 1px solid #e2e8f0;
  border-radius: 50%;
  /* tròn hẳn */
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  z-index: 2;
}

.search-icon-btn:hover:not(:disabled) {
  background: #667eea;
  /* tím đậm khi hover */
  color: #fff;
  /* icon trắng */
  border-color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.25);
  transform: translateY(-50%) scale(1.1);
}

.search-icon-btn:active:not(:disabled) {
  transform: translateY(-50%) scale(0.95);
}

.search-icon-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.search-icon-btn i {
  font-size: 12px;
  transition: transform 0.3s ease;
}

.search-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.search-btn:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 0 12px 32px rgba(102, 126, 234, 0.4);
}

.search-btn:hover:not(:disabled)::before {
  left: 100%;
}

.search-btn:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.search-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
  background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
}

.error-message {
  color: #dc2626;
  font-size: 14px;
  margin-top: 12px;
  padding: 12px 16px;
  background: linear-gradient(135deg, rgba(220, 38, 38, 0.1) 0%, rgba(239, 68, 68, 0.1) 100%);
  border-radius: 10px;
  border: 1px solid rgba(220, 38, 38, 0.2);
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
}

.error-message::before {
  content: '⚠️';
  font-size: 16px;
}

/* Order details */
.order-details {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 2px solid rgba(226, 232, 240, 0.6);
  position: relative;
}

.order-details::before {
  content: '';
  position: absolute;
  top: -1px;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 2px;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  border-radius: 1px;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.order-header h5 {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #1a202c;
  display: flex;
  align-items: center;
  gap: 8px;
}

.order-header h5::before {
  content: '📋';
  font-size: 18px;
}

.order-status {
  padding: 6px 16px;
  border-radius: 25px;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.status-pending {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.15) 100%);
  color: #d97706;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.status-confirmed {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(37, 99, 235, 0.15) 100%);
  color: #2563eb;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.status-processing {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.15) 0%, rgba(124, 58, 237, 0.15) 100%);
  color: #7c3aed;
  border: 1px solid rgba(139, 92, 246, 0.3);
}

.status-shipped {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.15) 100%);
  color: #059669;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.status-delivered {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(22, 163, 74, 0.15) 100%);
  color: #16a34a;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.status-cancelled {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%);
  color: #dc2626;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.status-returned {
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.15) 0%, rgba(147, 51, 234, 0.15) 100%);
  color: #9333ea;
  border: 1px solid rgba(168, 85, 247, 0.3);
}

.order-info {
  margin-bottom: 24px;
  background: rgba(248, 250, 252, 0.6);
  border-radius: 12px;
  padding: 16px;
  border: 1px solid rgba(226, 232, 240, 0.4);
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid rgba(226, 232, 240, 0.3);
}

.info-row:last-child {
  border-bottom: none;
}

.info-row .label {
  font-size: 13px;
  color: #64748b;
  font-weight: 600;
}

.info-row .value {
  font-size: 13px;
  color: #1a202c;
  font-weight: 700;
}

.payment-pending {
  color: #d97706;
  font-weight: 700;
}

.payment-paid {
  color: #059669;
  font-weight: 700;
}

.payment-failed {
  color: #dc2626;
  font-weight: 700;
}

/* Order items */
.order-items {
  margin-bottom: 24px;
}

.order-items h6 {
  margin: 0 0 14px 0;
  font-size: 15px;
  font-weight: 700;
  color: #1a202c;
  display: flex;
  align-items: center;
  gap: 8px;
}

.order-items h6::before {
  content: '📦';
  font-size: 16px;
}

.item-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.order-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: linear-gradient(135deg, rgba(248, 250, 252, 0.8) 0%, rgba(241, 245, 249, 0.8) 100%);
  border-radius: 12px;
  border: 1px solid rgba(226, 232, 240, 0.5);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.order-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
  border-radius: 0 2px 2px 0;
}

.order-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  border-color: rgba(102, 126, 234, 0.3);
}

.item-image {
  width: 80px;
  height: 80px;
  flex-shrink: 0;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.order-item:hover .item-image {
  border-color: rgba(102, 126, 234, 0.4);
  transform: scale(1.05);
}

.item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.order-item:hover .item-image img {
  transform: scale(1.1);
}

/* Thông tin sản phẩm */
.item-info {
  flex: 1;
  min-width: 0;
  /* Bắt buộc để text ellipsis hoạt động */
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 4px;
}

.item-name {
  font-size: 14px;
  font-weight: 700;
  color: #1a202c;
  white-space: nowrap;
  /* không xuống dòng */
  overflow: hidden;
  /* ẩn phần dư */
  text-overflow: ellipsis;
  /* hiện dấu ... */
}

.item-variant {
  font-size: 12px;
  color: #64748b;
  font-weight: 500;
  white-space: nowrap;
}

.item-price {
  font-size: 12px;
  color: #64748b;
  font-weight: 600;
}

/* Giá sản phẩm */
.item-prices {
  display: flex;
  align-items: center;
  gap: 8px;
  /* khoảng cách giữa giá cũ và giá mới */
  flex-wrap: wrap;
  /* nếu hẹp quá thì tự xuống hàng */
}

.old-price {
  font-size: 13px;
  color: #94a3b8;
  text-decoration: line-through;
}

.new-price {
  font-size: 15px;
  font-weight: 700;
  color: #1a202c;
}

.item-quantity {
  font-size: 13px;
  color: #64748b;
  font-weight: 500;
  white-space: nowrap;
}

.no-items-message {
  text-align: center;
  padding: 24px 16px;
  background: linear-gradient(135deg, rgba(248, 250, 252, 0.8) 0%, rgba(241, 245, 249, 0.8) 100%);
  border-radius: 12px;
  border: 1px solid rgba(226, 232, 240, 0.5);
}

.no-items-message p {
  margin: 0;
  color: #64748b;
  font-size: 14px;
  font-weight: 500;
}

/* Shipping address */
.shipping-address {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 2px solid rgba(226, 232, 240, 0.6);
  position: relative;
}

.shipping-address::before {
  content: '';
  position: absolute;
  top: -1px;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 2px;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  border-radius: 1px;
}

.shipping-address h6 {
  margin: 0 0 14px 0;
  font-size: 15px;
  font-weight: 700;
  color: #1a202c;
  display: flex;
  align-items: center;
  gap: 8px;
}

.shipping-address h6::before {
  content: '📍';
  font-size: 16px;
}

.address-content {
  background: linear-gradient(135deg, rgba(248, 250, 252, 0.8) 0%, rgba(241, 245, 249, 0.8) 100%);
  border-radius: 12px;
  padding: 16px;
  border: 1px solid rgba(226, 232, 240, 0.5);
}

.address-content p {
  margin: 5px 0;
  font-size: 13px;
  color: #475569;
  line-height: 1.4;
  font-weight: 500;
}

.address-content p:first-child {
  font-weight: 700;
  color: #1a202c;
  font-size: 14px;
}

/* Responsive design */
@media (max-width: 480px) {
  .order-tracking-card {
    padding: 16px;
    margin-top: 12px;
  }

  .input-group {
    flex-direction: column;
    gap: 12px;
  }

  .search-btn {
    width: 100%;
    min-width: auto;
  }

  /* Ẩn nút kính lúp bên trong input trên mobile */
  .search-icon-btn {
    display: none;
  }

  .tracking-input {
    padding-right: 18px;
    /* Reset padding khi không có icon */
  }

  .order-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .info-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }

  .order-item {
    flex-direction: column;
    text-align: center;
    gap: 12px;
  }

  .tracking-form h4,
  .order-header h5,
  .order-items h6,
  .shipping-address h6 {
    font-size: 16px;
  }
}
</style>
