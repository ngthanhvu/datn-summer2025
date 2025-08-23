<template>
  <div class="products-grid">
    <div 
      v-for="product in products" 
      :key="product.id" 
      class="chat-product-card product-card"
    >
      <div class="product-image" @click="viewProduct(product)">
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
                <option v-if="product.available_sizes && product.available_sizes.length > 0" v-for="size in product.available_sizes" :key="size" :value="size">
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
                <option v-if="product.available_colors && product.available_colors.length > 0" v-for="color in product.available_colors" :key="color" :value="color">
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
              <button @click="decreaseQuantity(product)" :disabled="selectedVariants[product.id].quantity <= 1">-</button>
              <input 
                type="number" 
                v-model="selectedVariants[product.id].quantity" 
                min="1" 
                :max="getMaxQuantity(product)"
                @input="onQuantityChange(product)"
              />
              <button @click="increaseQuantity(product)" :disabled="selectedVariants[product.id].quantity >= getMaxQuantity(product)">+</button>
            </div>
          </div>
          
          <!-- Add to cart button -->
          <button 
            class="add-to-cart-btn"
            @click="addToCartHandler(product)"
            :disabled="!canAddToCart(product) || isAddingToCart[product.id]"
          >
            <span v-if="isAddingToCart[product.id]">Đang thêm...</span>
            <span v-else>Thêm vào giỏ hàng</span>
          </button>
          
          <!-- Stock info -->
          <div class="stock-info">
            <span :class="{ 'in-stock': getStockQuantity(product) > 0, 'out-of-stock': getStockQuantity(product) <= 0 }">
              {{ getStockQuantity(product) > 0 ? `Còn ${getStockQuantity(product)} sản phẩm` : 'Hết hàng' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { useCart } from '../../composable/useCart'

export default {
  name: 'ProductCard',
  props: {
    products: {
      type: Array,
      required: true
    },
    showPurchaseForm: {
      type: Boolean,
      default: false
    }
  },
  emits: ['view-product', 'add-to-cart-success'],
  setup(props, { emit }) {
    const { addToCart } = useCart()
    
    const selectedVariants = reactive({})
    const isAddingToCart = reactive({})
    
    // Initialize selected variants for each product
    console.log('ProductCard: Initializing with products:', props.products)
    
    props.products.forEach(product => {
      console.log('ProductCard: Processing product:', {
        id: product.id,
        name: product.name,
        price: product.price,
        discount_price: product.discount_price,
        variants: product.variants,
        available_sizes: product.available_sizes,
        available_colors: product.available_colors,
        default_size: product.default_size,
        default_color: product.default_color
      })
      
      // Sử dụng mặc định từ backend hoặc fallback
      const defaultSize = product.default_size || (product.available_sizes && product.available_sizes.length > 0 ? product.available_sizes[0] : 'Mặc định')
      const defaultColor = product.default_color || (product.available_colors && product.available_colors.length > 0 ? product.available_colors[0] : 'Mặc định')
      
      selectedVariants[product.id] = {
        size: defaultSize,
        color: defaultColor,
        quantity: 1,
        variantId: null
      }
      
      // Tự động tìm variant ID cho mặc định
      if (product.variants && product.variants.length > 0) {
        const defaultVariant = product.variants.find(v => 
          v.size === defaultSize && v.color === defaultColor
        )
        if (defaultVariant) {
          selectedVariants[product.id].variantId = defaultVariant.id
        } else {
          // Nếu không tìm thấy variant chính xác, dùng variant đầu tiên có tồn kho
          const firstAvailableVariant = product.variants.find(v => 
            v.inventory && v.inventory.quantity > 0
          )
          if (firstAvailableVariant) {
            selectedVariants[product.id].variantId = firstAvailableVariant.id
            selectedVariants[product.id].size = firstAvailableVariant.size
            selectedVariants[product.id].color = firstAvailableVariant.color
          }
        }
      }
    })
    
    const onVariantChange = (product) => {
      const selected = selectedVariants[product.id]
      
      // Kiểm tra xem product.variants có tồn tại và có dữ liệu không
      if (!product.variants || !Array.isArray(product.variants) || product.variants.length === 0) {
        // Nếu không có variants, sử dụng product ID làm variant ID
        selected.variantId = product.id
        selected.quantity = 1
        return
      }
      
      const variant = product.variants.find(v => 
        v.size === selected.size && v.color === selected.color
      )
      
      if (variant) {
        selected.variantId = variant.id
        // Reset quantity to 1 when variant changes
        selected.quantity = 1
      } else {
        selected.variantId = null
      }
    }
    
    const onQuantityChange = (product) => {
      const selected = selectedVariants[product.id]
      const maxQty = getMaxQuantity(product)
      const stockQuantity = getStockQuantity(product)
      
      // Đảm bảo quantity không vượt quá tồn kho
      if (selected.quantity > stockQuantity) {
        selected.quantity = stockQuantity
        alert(`Số lượng tối đa có thể chọn là ${stockQuantity}. Không thể vượt quá tồn kho.`)
      } else if (selected.quantity < 1) {
        selected.quantity = 1
      }
      
      // Kiểm tra lại nếu quantity vẫn vượt quá tồn kho
      if (selected.quantity > stockQuantity) {
        selected.quantity = stockQuantity
      }
      
      // Log để debug
      console.log(`Quantity changed for ${product.name}: ${selected.quantity}, Max: ${maxQty}, Stock: ${stockQuantity}`)
      
      // Validation cuối cùng để đảm bảo không vượt quá tồn kho
      if (selected.quantity > stockQuantity) {
        console.warn(`Quantity validation failed: ${selected.quantity} > ${stockQuantity}`)
        selected.quantity = stockQuantity
      }
    }
    
    const increaseQuantity = (product) => {
      const selected = selectedVariants[product.id]
      const stockQuantity = getStockQuantity(product)
      
      // Kiểm tra tồn kho trước khi tăng
      if (selected.quantity < stockQuantity) {
        selected.quantity++
        console.log(`Quantity increased for ${product.name}: ${selected.quantity}/${stockQuantity}`)
      } else {
        alert(`Chỉ còn ${stockQuantity} sản phẩm trong kho. Không thể tăng thêm số lượng.`)
        console.warn(`Cannot increase quantity: ${selected.quantity} >= ${stockQuantity}`)
      }
    }
    
    const decreaseQuantity = (product) => {
      const selected = selectedVariants[product.id]
      if (selected.quantity > 1) {
        selected.quantity--
      }
    }
    
    const getMaxQuantity = (product) => {
      if (!selectedVariants[product.id].variantId) return 1
      
      // Luôn sử dụng getStockQuantity để đảm bảo tính nhất quán
      return getStockQuantity(product)
    }
    
    const getStockQuantity = (product) => {
      if (!selectedVariants[product.id].variantId) return 0
      
      // Nếu là product ID (không có variants), tìm variant đầu tiên hoặc sử dụng product
      if (selectedVariants[product.id].variantId === product.id) {
        // Tìm variant đầu tiên của sản phẩm
        if (product.variants && product.variants.length > 0) {
          const variant = product.variants[0]
          const stock = variant?.inventory?.quantity || 0
          console.log(`Default variant stock for ${product.name}: ${stock}`)
          return stock
        }
        // Nếu không có variants, trả về 999 (giả sử có vô hạn)
        console.log(`No variants found for ${product.name}, assuming unlimited stock`)
        return 999
      }
      
      const variant = product.variants?.find(v => v.id === selectedVariants[product.id].variantId)
      const stock = variant?.inventory?.quantity || 0
      console.log(`Variant stock for ${product.name}: ${stock}`)
      return stock
    }
    
    const canAddToCart = (product) => {
      const selected = selectedVariants[product.id]
      
      // Kiểm tra quantity phải > 0
      if (selected.quantity <= 0) {
        return false
      }
      
      // Kiểm tra tồn kho
      const stockQuantity = getStockQuantity(product)
      if (stockQuantity <= 0) {
        return false
      }
      
      // Kiểm tra quantity không vượt quá tồn kho
      if (selected.quantity > stockQuantity) {
        return false
      }
      
      // Log để debug
      console.log(`canAddToCart check for ${product.name}: quantity=${selected.quantity}, stock=${stockQuantity}`)
      
      // Kiểm tra xem có variant được chọn không
      if (!selected.variantId) {
        return false
      }
      
      return true
    }
    
    const addToCartHandler = async (product) => {
      const selected = selectedVariants[product.id]
      
      if (!canAddToCart(product)) {
        // Hiển thị thông báo lỗi cụ thể
        if (selected.quantity <= 0) {
          alert('Vui lòng chọn số lượng sản phẩm')
          return
        }
        
        const stockQuantity = getStockQuantity(product)
        if (stockQuantity <= 0) {
          alert('Sản phẩm này đã hết hàng')
          return
        }
        
        if (selected.quantity > stockQuantity) {
          alert(`Chỉ còn ${stockQuantity} sản phẩm trong kho. Không thể thêm ${selected.quantity} sản phẩm.`)
          // Reset quantity về tồn kho
          selected.quantity = stockQuantity
          return
        }
        
        // Log để debug
        console.log(`addToCartHandler validation passed for ${product.name}: quantity=${selected.quantity}, stock=${stockQuantity}`)
        
        alert('Vui lòng chọn size và màu sắc')
        return
      }
      
      // Kiểm tra số lượng trong giỏ hàng trước khi thêm
      const stockQuantity = getStockQuantity(product)
      if (selected.quantity > stockQuantity) {
        alert(`Chỉ còn ${stockQuantity} sản phẩm trong kho. Không thể thêm ${selected.quantity} sản phẩm.`)
        // Reset quantity về tồn kho
        selected.quantity = stockQuantity
        return
      }
      
      // Kiểm tra xem giỏ hàng đã có bao nhiêu sản phẩm này
      // Đây là validation quan trọng để tránh vượt quá tồn kho
      console.log(`Stock validation: requested=${selected.quantity}, stock=${stockQuantity}`)
      
      // Nếu số lượng yêu cầu vượt quá tồn kho, không cho phép thêm
      if (selected.quantity > stockQuantity) {
        alert(`Chỉ còn ${stockQuantity} sản phẩm trong kho. Không thể thêm ${selected.quantity} sản phẩm.`)
        selected.quantity = stockQuantity
        return
      }
      
      isAddingToCart[product.id] = true
      
      try {
        let variant
        let variantId = selected.variantId
        let price = product.discount_price || product.price || 0
        
        // Nếu là product ID (không có variants), cần tìm hoặc tạo variant thực sự
        if (selected.variantId === product.id) {
          // Tìm variant đầu tiên của sản phẩm hoặc tạo variant mặc định
          if (product.variants && product.variants.length > 0) {
            variant = product.variants[0]
            variantId = variant.id
            price = variant.price || price
          } else {
            // Nếu không có variants, sử dụng product ID làm variant ID
            console.log('Product has no variants, using product ID as variant ID')
            variantId = product.id
            variant = {
              id: product.id,
              size: selected.size || 'Mặc định',
              color: selected.color || 'Mặc định',
              price: price
            }
          }
        } else {
          // Tìm variant từ product.variants
          variant = product.variants?.find(v => v.id === selected.variantId)
          if (variant) {
            price = variant.price || price
          }
        }
        
        console.log('Adding to cart with:', { variantId, quantity: selected.quantity, price, product: product.name })
        console.log('addToCart function:', addToCart)
        
        const result = await addToCart(variantId, selected.quantity, price)
        
        if (result) {
          // Thêm thông báo thành công đẹp hơn
          emit('add-to-cart-success', {
            product,
            variant,
            quantity: selected.quantity,
            message: 'Tôi đã thêm vào giỏ hàng cho bạn rồi! 🛒'
          })
          
          // Reset quantity về 1 sau khi thêm thành công
          selected.quantity = 1
          
          // Log thành công
          console.log(`Successfully added ${selected.quantity} ${product.name} to cart`)
        } else {
          // Handle error
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
    
    // Helper functions
    const formatPrice = (price) => {
      return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
      }).format(price)
    }
    
    const calculateDiscountPercentage = (product) => {
      if (!product.discount_price) return 0
      return Math.round(((product.price - product.discount_price) / product.price) * 100)
    }
    
    const getPlaceholderImage = () => {
      return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjhmYWZjIi8+CiAgPHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzY0NzQ4YiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pgo8L3N2Zz4='
    }
    
    const getImageUrl = (product) => {
      console.log('getImageUrl called for product:', {
        name: product.name,
        mainImage: product.mainImage,
        main_image: product.main_image
      })
      
      const mainImage = product.mainImage || product.main_image
      if (mainImage && mainImage.image_url) {
        console.log('Using image_url:', mainImage.image_url)
        return mainImage.image_url
      }
      
      console.log('No image_url found, using placeholder')
      return getPlaceholderImage()
    }
    
    const handleImageError = (event) => {
      event.target.src = getPlaceholderImage()
    }
    
    return {
      formatPrice,
      calculateDiscountPercentage,
      getPlaceholderImage,
      getImageUrl,
      handleImageError,
      selectedVariants,
      isAddingToCart,
      onVariantChange,
      onQuantityChange,
      increaseQuantity,
      decreaseQuantity,
      getMaxQuantity,
      getStockQuantity,
      canAddToCart,
      addToCartHandler
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
