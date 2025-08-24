<template>
  <div class="products-grid">
    <div v-for="product in products" :key="product.id" class="chat-product-card product-card">
      <div class="product-image" @click="viewProduct(product)">
        <img :src="getImageUrl(product)" :alt="product.name" @error="handleImageError" />
        <div v-if="product.discount_price" class="discount-badge">
          -{{ calculateDiscountPercentage(product) }}%
        </div>
      </div>
      <div class="product-info">
        <h4 class="product-name" @click="viewProduct(product)">{{ product.name }}</h4>
        <div class="product-category">{{ product.categories?.name }}</div>
        <div class="product-price">
          <span v-if="product.discount_price" class="original-price">
            {{ formatPrice(product.price) }}
          </span>
          <span class="current-price">
            {{ formatPrice(product.discount_price || product.price) }}
          </span>
        </div>

        <!-- Thêm vào giỏ hàng section - Chỉ hiển thị khi cần thiết -->
        <div class="add-to-cart-section" v-if="showPurchaseForm">
          <div class="variant-selectors">
            <!-- Size selector -->
            <div class="selector-group">
              <label>Size:</label>
              <select v-model="selectedVariants[product.id].size" @change="onVariantChange(product)">
                <option value="">Chọn size</option>
                <option v-if="product.available_sizes && product.available_sizes.length > 0"
                  v-for="size in product.available_sizes" :key="size" :value="size">
                  {{ size }}
                </option>
                <option v-else value="Mặc định">Mặc định</option>
              </select>
            </div>

            <!-- Color selector -->
            <div class="selector-group">
              <label>Màu:</label>
              <select v-model="selectedVariants[product.id].color" @change="onVariantChange(product)">
                <option value="">Chọn màu</option>
                <option v-if="product.available_colors && product.available_colors.length > 0"
                  v-for="color in product.available_colors" :key="color" :value="color">
                  {{ color }}
                </option>
                <option v-else value="Mặc định">Mặc định</option>
              </select>
            </div>
          </div>

          <!-- Quantity selector -->
          <div class="quantity-selector">
            <label>Số lượng:</label>
            <div class="quantity-controls">
              <button @click="decreaseQuantity(product)"
                :disabled="selectedVariants[product.id].quantity <= 1">-</button>
              <input type="number" v-model="selectedVariants[product.id].quantity" min="1"
                :max="getMaxQuantity(product)" @input="onQuantityChange(product)" />
              <button @click="increaseQuantity(product)"
                :disabled="selectedVariants[product.id].quantity >= getMaxQuantity(product)">+</button>
            </div>
          </div>

          <!-- Add to cart button -->
          <button class="add-to-cart-btn" @click="addToCartHandler(product)"
            :disabled="!canAddToCart(product) || isAddingToCart[product.id]">
            <span v-if="isAddingToCart[product.id]">Đang thêm...</span>
            <span v-else>Thêm vào giỏ hàng</span>
          </button>

          <!-- Stock info -->
          <div class="stock-info">
            <span
              :class="{ 'in-stock': getStockQuantity(product) > 0, 'out-of-stock': getStockQuantity(product) <= 0 }">
              {{ getStockQuantity(product) > 0 ? `Còn ${getStockQuantity(product)} sản phẩm` : 'Hết hàng' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useCart } from '../../composable/useCart'

const props = defineProps({
  products: {
    type: Array,
    required: true
  },
  showPurchaseForm: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['view-product', 'add-to-cart-success'])

const { addToCart } = useCart()

const selectedVariants = reactive({})
const isAddingToCart = reactive({})

// Khởi tạo variants mặc định
props.products.forEach(product => {
  const defaultSize = product.default_size || (product.available_sizes?.[0] || 'Mặc định')
  const defaultColor = product.default_color || (product.available_colors?.[0] || 'Mặc định')

  selectedVariants[product.id] = {
    size: defaultSize,
    color: defaultColor,
    quantity: 1,
    variantId: null
  }

  if (product.variants?.length) {
    const defaultVariant = product.variants.find(v =>
      v.size === defaultSize && v.color === defaultColor
    )
    if (defaultVariant) {
      selectedVariants[product.id].variantId = defaultVariant.id
    } else {
      const firstAvailableVariant = product.variants.find(v => v.inventory?.quantity > 0)
      if (firstAvailableVariant) {
        selectedVariants[product.id].variantId = firstAvailableVariant.id
        selectedVariants[product.id].size = firstAvailableVariant.size
        selectedVariants[product.id].color = firstAvailableVariant.color
      }
    }
  }
})

function onVariantChange(product) {
  const selected = selectedVariants[product.id]
  if (!product.variants?.length) {
    selected.variantId = product.id
    selected.quantity = 1
    return
  }

  const variant = product.variants.find(v =>
    v.size === selected.size && v.color === selected.color
  )

  if (variant) {
    selected.variantId = variant.id
    selected.quantity = 1
  } else {
    selected.variantId = null
  }
}

function onQuantityChange(product) {
  const selected = selectedVariants[product.id]
  const stockQuantity = getStockQuantity(product)

  if (selected.quantity > stockQuantity) {
    selected.quantity = stockQuantity
    alert(`Số lượng tối đa có thể chọn là ${stockQuantity}.`)
  } else if (selected.quantity < 1) {
    selected.quantity = 1
  }
}

function increaseQuantity(product) {
  const selected = selectedVariants[product.id]
  const stockQuantity = getStockQuantity(product)
  if (selected.quantity < stockQuantity) {
    selected.quantity++
  } else {
    alert(`Chỉ còn ${stockQuantity} sản phẩm trong kho.`)
  }
}

function decreaseQuantity(product) {
  const selected = selectedVariants[product.id]
  if (selected.quantity > 1) {
    selected.quantity--
  }
}

function getMaxQuantity(product) {
  return selectedVariants[product.id].variantId ? getStockQuantity(product) : 1
}

function getStockQuantity(product) {
  const selected = selectedVariants[product.id]
  if (!selected.variantId) return 0

  if (selected.variantId === product.id) {
    if (product.variants?.length) {
      return product.variants[0]?.inventory?.quantity || 0
    }
    return 999
  }

  const variant = product.variants?.find(v => v.id === selected.variantId)
  return variant?.inventory?.quantity || 0
}

function canAddToCart(product) {
  const selected = selectedVariants[product.id]
  const stockQuantity = getStockQuantity(product)

  return selected.quantity > 0 &&
    stockQuantity > 0 &&
    selected.quantity <= stockQuantity &&
    !!selected.variantId
}

async function addToCartHandler(product) {
  const selected = selectedVariants[product.id]

  if (!canAddToCart(product)) {
    const stockQuantity = getStockQuantity(product)

    if (selected.quantity <= 0) {
      alert('Vui lòng chọn số lượng sản phẩm')
    } else if (stockQuantity <= 0) {
      alert('Sản phẩm này đã hết hàng')
    } else if (selected.quantity > stockQuantity) {
      alert(`Chỉ còn ${stockQuantity} sản phẩm trong kho.`)
      selected.quantity = stockQuantity
    } else {
      alert('Vui lòng chọn size và màu sắc')
    }

    return
  }

  isAddingToCart[product.id] = true

  try {
    let variant = null
    let variantId = selected.variantId
    let price = product.discount_price || product.price || 0

    if (variantId === product.id) {
      if (product.variants?.length) {
        variant = product.variants[0]
        variantId = variant.id
        price = variant.price || price
      } else {
        variant = {
          id: product.id,
          size: selected.size || 'Mặc định',
          color: selected.color || 'Mặc định',
          price
        }
      }
    } else {
      variant = product.variants?.find(v => v.id === selected.variantId)
      if (variant) {
        price = variant.price || price
      }
    }

    const result = await addToCart(variantId, selected.quantity, price)

    if (result) {
      emit('add-to-cart-success', {
        product,
        variant,
        quantity: selected.quantity,
        message: 'Tôi đã thêm vào giỏ hàng cho bạn rồi! 🛒'
      })
      selected.quantity = 1
    } else {
      console.error('Add to cart failed')
      alert('Có lỗi xảy ra khi thêm vào giỏ hàng')
    }
  } catch (error) {
    console.error('Add to cart error:', error)
    alert(error.message || 'Có lỗi xảy ra khi thêm vào giỏ hàng')
  } finally {
    isAddingToCart[product.id] = false
  }
}

function formatPrice(price) {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price)
}

function calculateDiscountPercentage(product) {
  if (!product.discount_price) return 0
  return Math.round(((product.price - product.discount_price) / product.price) * 100)
}

function getPlaceholderImage() {
  return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjhmYWZjIi8+CiAgPHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzY0NzQ4YiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pgo8L3N2Zz4='
}

function getImageUrl(product) {
  const mainImage = product.mainImage || product.main_image
  if (mainImage?.image_url) {
    return mainImage.image_url
  }
  return getPlaceholderImage()
}

function handleImageError(event) {
  event.target.src = getPlaceholderImage()
}

function viewProduct(product) {
  emit('view-product', product)
}
</script>

<style scoped>
.chat-product-card {
  display: flex;
  flex-direction: column;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  overflow: hidden;
  width: 100%;
  max-width: 320px;
  margin: 8px auto;
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.chat-product-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
}

.product-image {
  width: 100%;
  height: 180px;
  background: #f9fafb;
  display: flex;
  justify-content: center;
  align-items: center;
}

.product-image img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.product-info {
  padding: 12px 14px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.product-name {
  font-size: 15px;
  font-weight: 600;
  color: #111827;
  margin: 0;
  line-height: 1.3;
}

.product-category {
  font-size: 13px;
  color: #6b7280;
}

.product-price {
  display: flex;
  align-items: center;
  gap: 6px;
}

.original-price {
  font-size: 13px;
  color: #9ca3af;
  text-decoration: line-through;
}

.current-price {
  font-size: 16px;
  font-weight: 700;
  color: #ef4444;
}

.add-to-cart-section {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid #f3f4f6;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.selector-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.selector-group label {
  font-size: 12px;
  color: #374151;
}

.selector-group select {
  padding: 6px 8px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
}

.quantity-controls {
  display: flex;
  align-items: center;
  gap: 6px;
}

.quantity-controls button {
  width: 28px;
  height: 28px;
  border: 1px solid #d1d5db;
  background: #f9fafb;
  border-radius: 6px;
  cursor: pointer;
}

.quantity-controls input {
  width: 50px;
  height: 28px;
  text-align: center;
  border: 1px solid #d1d5db;
  border-radius: 6px;
}

.add-to-cart-btn {
  padding: 8px 12px;
  background: #4f46e5;
  color: #fff;
  font-weight: 600;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.2s ease;
}

.add-to-cart-btn:hover {
  background: #4338ca;
}

.stock-info {
  font-size: 12px;
  font-weight: 600;
}

.stock-info .in-stock {
  color: #059669;
}

.stock-info .out-of-stock {
  color: #dc2626;
}
</style>
