<template>
  <div class="products-grid">
    <div 
      v-for="product in products" 
      :key="product.id" 
      class="chat-product-card product-card"
      @click="viewProduct(product)"
    >
      <div class="product-image">
        <img 
          :src="getImageUrl(product)" 
          :alt="product.name"
          @error="handleImageError"
        />
        <div v-if="product.discount_price" class="discount-badge">
          -{{ calculateDiscountPercentage(product) }}%
        </div>
      </div>
      <div class="product-info">
        <h4 class="product-name">{{ product.name }}</h4>
        <div class="product-category">{{ product.categories?.name }}</div>
        <div class="product-price">
          <span v-if="product.discount_price" class="original-price">
            {{ formatPrice(product.price) }}
          </span>
          <span class="current-price">
            {{ formatPrice(product.discount_price || product.price) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAIChat } from '../../composable/useAIChat'

export default {
  name: 'ProductCard',
  props: {
    products: {
      type: Array,
      required: true
    }
  },
  emits: ['view-product'],
  setup() {
    const { formatPrice, calculateDiscountPercentage, getPlaceholderImage, getImageUrl, handleImageError } = useAIChat()
    
    return {
      formatPrice,
      calculateDiscountPercentage,
      getPlaceholderImage,
      getImageUrl,
      handleImageError
    }
  },
  methods: {
    viewProduct(product) {
      this.$emit('view-product', product)
    }
  }
}
</script>

<style scoped>
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 16px;
  margin-top: 16px;
  padding: 12px;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  border: 1px solid rgba(226, 232, 240, 0.8);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.chat-product-card {
  transform: scale(0.95);
  margin: 0 auto;
  transition: all 0.3s ease;
  border-radius: 12px;
  overflow: hidden;
  background: white;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  cursor: pointer;
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border: 1px solid rgba(226, 232, 240, 0.8);
  max-width: 100%;
  backdrop-filter: blur(5px);
}

.chat-product-card:hover {
  transform: scale(1);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  border-color: rgba(102, 126, 234, 0.3);
}

.product-image {
  position: relative;
  width: 100%;
  height: 140px;
  border-radius: 8px 8px 0 0;
  overflow: hidden;
  margin-bottom: 0;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.chat-product-card:hover .product-image img {
  transform: scale(1.05);
}

.discount-badge {
  position: absolute;
  top: 8px;
  right: 8px;
  background: linear-gradient(135deg, #ff4757 0%, #ff3742 100%);
  color: white;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: bold;
  box-shadow: 0 2px 8px rgba(255, 71, 87, 0.3);
}

.product-info {
  padding: 12px;
}

.product-name {
  font-size: 14px;
  font-weight: 600;
  color: #202124;
  margin: 0 0 6px 0;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-category {
  font-size: 12px;
  color: #5f6368;
  margin-bottom: 8px;
  font-weight: 500;
}

.product-price {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.original-price {
  font-size: 12px;
  color: #9aa0a6;
  text-decoration: line-through;
}

.current-price {
  font-size: 16px;
  font-weight: 700;
  color: #ff4757;
  background: linear-gradient(135deg, #ff4757 0%, #ff3742 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
</style>
