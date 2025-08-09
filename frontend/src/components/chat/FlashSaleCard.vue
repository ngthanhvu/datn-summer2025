<template>
  <div class="flashsale-section">
    <div class="flashsale-title">FLASH SALE ĐANG DIỄN RA</div>
    <div 
      v-for="(flashSale, index) in flashSales" 
      :key="flashSale.id || index"
      class="flashsale-item"
      :class="index === 0 ? 'flashsale-item-premium' : 'flashsale-item-standard'"
    >
      <div class="flashsale-content">
        <div class="flashsale-main">
          <div class="flashsale-info">
            <div class="flashsale-name">{{ flashSale.name }}</div>
            <div class="flashsale-time">
              <span class="time-label">Thời gian:</span>
              <div class="time-value">
                {{ formatDate(flashSale.start_time) }} - {{ formatDate(flashSale.end_time) }}
              </div>
            </div>
          </div>
          <div class="flashsale-badge">SALE</div>
        </div>
        <div v-if="flashSale.description" class="flashsale-desc">{{ flashSale.description }}</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FlashSaleCard',
  props: {
    flashSales: {
      type: Array,
      required: true
    }
  },
  setup() {
    const formatDate = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString('vi-VN')
    }
    
    return {
      formatDate
    }
  }
}
</script>

<style scoped>
@import url('../../assets/css/chat-animations.css');
.flashsale-section {
  margin: 12px 0;
  padding: 16px;
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(255, 107, 107, 0.4);
  border: 2px solid rgba(255, 255, 255, 0.3);
  position: relative;
  overflow: hidden;
}

.flashsale-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  animation: flashShimmer 2s infinite;
}

.flashsale-title {
  text-align: center;
  color: white;
  font-weight: 800;
  font-size: 16px;
  margin-bottom: 16px;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
  animation: flashPulse 1.5s infinite;
  position: relative;
  z-index: 1;
}

.flashsale-item {
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

.flashsale-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
}

.flashsale-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
  transition: left 0.6s ease;
}

.flashsale-item:hover::before {
  left: 100%;
}

.flashsale-item-premium {
  background: linear-gradient(135deg, #ff4757 0%, #ff3742 100%);
  border: 3px solid #ffd700;
  box-shadow: 0 6px 24px rgba(255, 71, 87, 0.5);
}

.flashsale-item-standard {
  background: linear-gradient(135deg, #ff9ff3 0%, #f368e0 100%);
  border: 2px solid #ff9ff3;
  box-shadow: 0 4px 20px rgba(255, 159, 243, 0.4);
}

.flashsale-content {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.flashsale-main {
  display: flex;
  align-items: center;
  gap: 12px;
}

.flashsale-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.flashsale-name {
  color: white;
  font-weight: 700;
  font-size: 15px;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  letter-spacing: 0.5px;
  line-height: 1.3;
}

.flashsale-badge {
  background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
  color: #333;
  font-size: 9px;
  font-weight: 800;
  padding: 4px 8px;
  border-radius: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  animation: flashBadge 2s infinite;
  box-shadow: 0 2px 8px rgba(255, 215, 0, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.3);
  white-space: nowrap;
  align-self: flex-start;
  margin-top: 2px;
}



.flashsale-time {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.time-label {
  color: #ffeaa7;
  font-weight: 600;
  font-size: 11px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.time-value {
  color: white;
  font-weight: 600;
  font-size: 11px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
  background: rgba(255, 255, 255, 0.12);
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.25);
  line-height: 1.4;
  display: block;
  width: 100%;
  text-align: center;
}

.flashsale-desc {
  color: rgba(255, 255, 255, 0.9);
  font-size: 11px;
  font-style: italic;
  line-height: 1.4;
  padding-left: 0;
}
</style>
