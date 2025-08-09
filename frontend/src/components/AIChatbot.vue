<template>
  <div class="ai-chatbot">
    <!-- Chat Widget Button -->
    <div 
      v-if="!isOpen" 
      @click="toggleChat" 
      class="chat-widget-button"
      :class="{ 'pulse': hasUnreadMessages }"
    >
      <div class="chat-icon">
        <img class="chat-icon-img" src="/chatbothehe.png" alt="Mở trò chuyện" />
      </div>
      <div class="chat-badge" v-if="hasUnreadMessages">{{ unreadCount }}</div>
    </div>

    <!-- Scroll hint bubble -->
    <div v-if="!isOpen && showScrollHint" class="widget-hint">
      Xin chào! Tôi là trợ lí ảo.
    </div>

    <!-- Chat Window -->
    <div v-if="isOpen" class="chat-window">
      <!-- Header -->
      <div class="chat-header">
        <div class="chat-header-info">
          <div class="ai-avatar">
            <img 
              class="ai-avatar-img"
              src="https://lapsedhistorian.com/wp-content/uploads/2024/09/125887131_Chatbot%20Message%20Bubble.jpg" 
              alt="Trợ Lí DEVGANG"
            />
          </div>
          <div class="chat-title">
            <h3>Trợ Lí DEVGANG</h3>
            <span class="status" :class="{ 'online': isOnline }">
              {{ isOnline ? 'Đang hoạt động' : 'Đang kết nối...' }}
            </span>
          </div>
        </div>
        <button @click="toggleChat" class="close-btn">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12L19 6.41Z" fill="currentColor"/>
          </svg>
        </button>
      </div>

      <!-- Messages Container -->
      <div class="messages-container" ref="messagesContainer">
        <div 
          v-for="(message, index) in messages" 
          :key="index" 
          class="message"
          :class="{ 'user-message': message.isUser, 'ai-message': !message.isUser }"
        >
          <div class="message-avatar" v-if="!message.isUser">
            <img 
              class="ai-message-avatar-img"
              src="https://lapsedhistorian.com/wp-content/uploads/2024/09/125887131_Chatbot%20Message%20Bubble.jpg" 
              alt="Trợ Lí DEVGANG"
            />
          </div>
                     <div class="message-content">
             <div class="message-text" v-html="formatMessage(message.text)"></div>
             
             <!-- Product Cards -->
             <div v-if="message.products && message.products.length > 0" class="products-grid">
               <div 
                 v-for="product in message.products" 
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
             
             <!-- Coupon Cards -->
             <div v-if="message.coupons && message.coupons.length > 0" class="coupons-section">
               <div class="coupon-title">MÃ GIẢM GIÁ HOT</div>
               <div 
                 v-for="(coupon, index) in message.coupons" 
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
             
             <!-- Flash Sale Cards -->
             <div v-if="message.flashSales && message.flashSales.length > 0" class="flashsale-section">
               <div class="flashsale-title">FLASH SALE ĐANG DIỄN RA</div>
               <div 
                 v-for="(flashSale, index) in message.flashSales" 
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
                           {{ flashSale.start_time }} - {{ flashSale.end_time }}
                         </div>
                       </div>
                     </div>
                     <div class="flashsale-badge">SALE</div>
                   </div>
                   <div v-if="flashSale.description" class="flashsale-desc">{{ flashSale.description }}</div>
                 </div>
               </div>
             </div>
             
             <div class="message-time">{{ formatTime(message.timestamp) }}</div>
           </div>
          
        </div>

        <div v-if="isTyping" class="message ai-message">
          <div class="message-avatar">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 1H5C3.89 1 3 1.89 3 3V21C3 22.11 3.89 23 5 23H19C20.11 23 21 22.11 21 21V9ZM19 21H5V3H13V9H19V21Z" fill="currentColor"/>
            </svg>
          </div>
          <div class="message-content">
            <div class="typing-indicator">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions" v-if="showQuickActions">
        <button 
          v-for="action in quickActions" 
          :key="action.text"
          @click="sendQuickMessage(action.text)"
          class="quick-action-btn"
        >
          {{ action.text }}
        </button>
      </div>

      <!-- Input Area -->
      <div class="input-area">
        <div class="input-container">
          <textarea
            v-model="currentMessage"
            @keydown.enter.prevent="sendMessage"
            @keydown.enter="handleEnter"
            placeholder="Nhập tin nhắn của bạn..."
            class="message-input"
            rows="1"
            ref="messageInput"
          ></textarea>
          <button 
            @click="sendMessage" 
            class="send-btn"
            :disabled="!currentMessage.trim() || isTyping"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M2.01 21L23 12L2.01 3L2 10L17 12L2 14L2.01 21Z" fill="currentColor"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useAIChat } from '../composable/useAIChat'

export default {
  name: 'AIChatbot',
  setup() {
    const {
      isOpen,
      isTyping,
      messages,
      currentMessage,
      hasUnreadMessages,
      unreadCount,
      sendMessage: sendAIMessage,
      toggleChat: toggleAIChat,
      addWelcomeMessage,
      formatMessage,
      formatTime
    } = useAIChat()
    
    const isOnline = ref(true)
    const messagesContainer = ref(null)
    const messageInput = ref(null)
    const showQuickActions = ref(true)
    const showScrollHint = ref(false)
    let scrollHintTimer = null

    const handleScrollShowHint = () => {
      if (isOpen.value) return
      if (sessionStorage.getItem('ai_scroll_hint_shown') === '1') return
      showScrollHint.value = true
      sessionStorage.setItem('ai_scroll_hint_shown', '1')
      if (scrollHintTimer) clearTimeout(scrollHintTimer)
      scrollHintTimer = setTimeout(() => {
        showScrollHint.value = false
      }, 5000)
    }

    const quickActions = [
      { text: 'Tìm sản phẩm 🔍' },
      { text: 'Mã khuyến mãi 🎉' },
      { text: 'Flash sale 🔥' },
      { text: 'Hướng dẫn thanh toán 💳' },
      { text: 'Danh mục sản phẩm 🛒' }
    ]

    const toggleChat = () => {
      toggleAIChat()
      if (isOpen.value) {
        if (messages.length === 0) {
          addWelcomeMessage()
        }
        nextTick(() => {
          scrollToBottom()
          messageInput.value?.focus()
        })
      }
      // Hide hint once user opens chat
      if (isOpen.value) {
        showScrollHint.value = false
      }
    }

    const sendMessage = async () => {
      if (!currentMessage.value.trim() || isTyping.value) return

      const userMessage = currentMessage.value.trim()
      currentMessage.value = ''

      scrollToBottom()
      showQuickActions.value = false

      await sendAIMessage(userMessage)
      
      nextTick(() => {
        scrollToBottom()
      })
    }

    const sendQuickMessage = (text) => {
      currentMessage.value = text
      sendMessage()
    }

    const handleEnter = (event) => {
      if (event.shiftKey) {
        return 
      }
      sendMessage()
    }

    const scrollToBottom = () => {
      nextTick(() => {
        if (messagesContainer.value) {
          messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
        }
      })
    }

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

    const handleImageError = (event) => {
      console.log('Image error for:', event.target.alt, 'URL:', event.target.src)
      event.target.src = getPlaceholderImage()
    }

    const getPlaceholderImage = () => {
      return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjhmYWZjIi8+CiAgPHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzY0NzQ4YiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pgo8L3N2Zz4='
    }

    const getImageUrl = (product) => {
      console.log('Product:', product.name)
      console.log('MainImage:', product.mainImage)
      console.log('MainImage (snake_case):', product.main_image)
      console.log('Image URL:', product.mainImage?.image_url)
      console.log('Image URL (snake_case):', product.main_image?.image_url)
      
      const mainImage = product.mainImage || product.main_image
      if (mainImage && mainImage.image_url) {
        return mainImage.image_url
      }
      return getPlaceholderImage()
    }

    const viewProduct = (product) => {
      window.open(`/san-pham/${product.slug}`, '_blank')
    }

    watch(messages, (newMessages) => {
      if (!isOpen.value && newMessages.length > 0) {
        const lastMessage = newMessages[newMessages.length - 1]
        if (!lastMessage.isUser) {
          hasUnreadMessages.value = true
          unreadCount.value++
        }
      }
    })

    onMounted(() => {
      const textarea = messageInput.value
      if (textarea) {
        textarea.addEventListener('input', function() {
          this.style.height = 'auto'
          this.style.height = this.scrollHeight + 'px'
        })
      }
      window.addEventListener('scroll', handleScrollShowHint, { passive: true })
    })

    onUnmounted(() => {
      window.removeEventListener('scroll', handleScrollShowHint)
      if (scrollHintTimer) clearTimeout(scrollHintTimer)
    })

    return {
      isOpen,
      isTyping,
      isOnline,
      hasUnreadMessages,
      unreadCount,
      currentMessage,
      messages,
      messagesContainer,
      messageInput,
      showQuickActions,
      showScrollHint,
      quickActions,
      toggleChat,
      sendMessage,
      sendQuickMessage,
      handleEnter,
      formatMessage,
      formatTime,
      viewProduct,
      getImageUrl,
      handleImageError,
      calculateDiscountPercentage,
      formatPrice
    }
  }
}
</script>

<style scoped>
.ai-chatbot {
  position: fixed;
  bottom: 90px; 
  right: 20px;
  z-index: 1000;
  font-family: 'Inter', sans-serif;
}

:root.chatwidget-open .ai-chatbot .chat-widget-button { display: none; }

.chat-widget-button {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: none; 
  transition: all 0.3s ease;
  position: relative;
  border: none;
}

.chat-widget-button:hover {
  transform: scale(1.05);
  box-shadow: none;
}

.chat-widget-button.pulse {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4); }
  50% { box-shadow: 0 4px 20px rgba(102, 126, 234, 0.8); }
  100% { box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4); }
}

.chat-icon-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 0; /* keep original PNG edges */
  display: block;
  pointer-events: none; /* ensure clicks pass to button */
}

.chat-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ff4757;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
}

.chat-window {
  width: 480px;
  height: 600px;
  background: 
    linear-gradient(rgba(255,255,255,0.88), rgba(248,250,252,0.88)),
    url('/ai-chatbot-bg.png') center/cover no-repeat;
  border-radius: 24px;
  box-shadow: 0 25px 80px rgba(0, 0, 0, 0.12), 0 8px 32px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(20px);
}

.chat-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 24px 20px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  overflow: hidden;
}

.chat-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
  animation: shimmer 3s infinite;
}

.chat-header-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.ai-avatar {
  width: 44px;
  height: 44px;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(10px);
  position: relative;
  z-index: 1;
}

.ai-avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
  display: block;
}

.close-btn {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: background 0.3s ease;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

.chat-title h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  position: relative;
  z-index: 1;
}

.status {
  font-size: 13px;
  opacity: 0.9;
  font-weight: 500;
  position: relative;
  z-index: 1;
}

.status.online {
  color: #00ff88;
  text-shadow: 0 1px 2px rgba(0, 255, 136, 0.3);
}



.messages-container {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
  background: transparent; 
}

.message {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  max-width: 80%;
}

.user-message {
  margin-left: auto;
  flex-direction: row-reverse;
}

.message-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  color: #5f6368;
  flex-shrink: 0;
  border: 2px solid rgba(226, 232, 240, 0.8);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  backdrop-filter: blur(10px);
}

.ai-message-avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
  display: block;
}

.user-avatar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: 2px solid rgba(102, 126, 234, 0.3);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.message-content {
  flex: 1;
}

.message-text {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  padding: 12px 16px;
  border-radius: 18px;
  font-size: 14px;
  line-height: 1.6;
  color: #1a202c;
  border: 1px solid rgba(226, 232, 240, 0.6);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(0, 0, 0, 0.04);
  backdrop-filter: blur(10px);
  position: relative;
  overflow: hidden;
}

.message-text::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
}

.user-message .message-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: 1px solid rgba(102, 126, 234, 0.2);
  box-shadow: 0 4px 16px rgba(102, 126, 234, 0.22), 0 2px 6px rgba(102, 126, 234, 0.08);
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.message-time {
  font-size: 11px;
  color: #9ca3af;
  margin-top: 6px;
  text-align: right;
  font-weight: 500;
  opacity: 0.8;
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
}

.user-message .message-time {
  text-align: left;
}

.typing-indicator {
  display: flex;
  gap: 6px;
  padding: 16px 20px;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  border-radius: 20px;
  border: 1px solid rgba(226, 232, 240, 0.6);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  backdrop-filter: blur(10px);
}

.typing-indicator span {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  animation: typing 1.4s infinite ease-in-out;
  box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
}

.typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
.typing-indicator span:nth-child(2) { animation-delay: -0.16s; }

@keyframes typing {
  0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
  40% { transform: scale(1); opacity: 1; }
}

.quick-actions {
  padding: 20px;
  border-top: 1px solid rgba(226, 232, 240, 0.6);
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
}

.quick-action-btn {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  border: 1px solid rgba(226, 232, 240, 0.8);
  padding: 10px 18px;
  border-radius: 24px;
  font-size: 13px;
  color: #374151;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 500;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  backdrop-filter: blur(10px);
}

.quick-action-btn:hover {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(102, 126, 234, 0.25);
  border-color: rgba(102, 126, 234, 0.3);
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 16px;
  margin-top: 16px;
  padding: 8px;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 16px;
  border: 1px solid rgba(226, 232, 240, 0.6);
}

.chat-product-card {
  transform: scale(0.9);
  margin: 0 auto;
  transition: all 0.3s ease;
}

.chat-product-card:hover {
  transform: scale(0.95);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.input-area {
  padding: 24px 20px;
  border-top: 1px solid rgba(226, 232, 240, 0.6);
  background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  backdrop-filter: blur(10px);
}

.input-container {
  display: flex;
  gap: 12px;
  align-items: flex-end;
}

.message-input {
  flex: 1;
  border: 2px solid rgba(226, 232, 240, 0.8);
  border-radius: 28px;
  padding: 14px 20px;
  font-size: 14px;
  resize: none;
  max-height: 120px;
  outline: none;
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.message-input:focus {
  border-color: #667eea;
  box-shadow: 0 4px 16px rgba(102, 126, 234, 0.15);
  background: rgba(255, 255, 255, 0.95);
}

.send-btn {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  position: relative;
  overflow: hidden;
}

.send-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s ease;
}

.send-btn:hover:not(:disabled) {
  transform: scale(1.05);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.send-btn:hover:not(:disabled)::before {
  left: 100%;
}

.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: scale(0.95);
}

/* Product Card Styles */
.product-card {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border-radius: 16px;
  padding: 12px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid rgba(226, 232, 240, 0.8);
  max-width: 100%;
  overflow: hidden;
  backdrop-filter: blur(5px);
}

.product-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
  border-color: rgba(102, 126, 234, 0.3);
}

.product-image {
  position: relative;
  width: 100%;
  height: 120px;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 8px;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.discount-badge {
  position: absolute;
  top: 8px;
  right: 8px;
  background: #ff4757;
  color: white;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: bold;
}

.product-info {
  padding: 0 4px;
}

.product-name {
  font-size: 14px;
  font-weight: 600;
  color: #202124;
  margin: 0 0 4px 0;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-category {
  font-size: 12px;
  color: #5f6368;
  margin-bottom: 6px;
}

.product-price {
  display: flex;
  align-items: center;
  gap: 8px;
}

.original-price {
  font-size: 12px;
  color: #9aa0a6;
  text-decoration: line-through;
}

.current-price {
  font-size: 14px;
  font-weight: 600;
  color: #ff4757;
}

/* Product display styling */
.product-line {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  margin: 6px 0;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  transition: all 0.3s ease;
  flex-wrap: wrap;
}

.product-line:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border-color: #667eea;
}

.product-name {
  font-weight: 600;
  color: #2d3748;
  font-size: 14px;
  flex: 1;
  min-width: 120px;
}

.product-price {
  color: #e53e3e;
  font-weight: 600;
  font-size: 13px;
  white-space: nowrap;
}

.product-brand {
  color: #4a5568;
  font-size: 12px;
  white-space: nowrap;
}

.product-size,
.product-color {
  color: #718096;
  font-size: 12px;
  white-space: nowrap;
  padding: 2px 8px;
  background: rgba(113, 128, 150, 0.1);
  border-radius: 12px;
  border: 1px solid rgba(113, 128, 150, 0.2);
}

/* Simple text formatting for chat messages */
.message-text strong {
  font-weight: 600;
  color: #2d3748;
}

/* Beautiful Coupon Section Styling */
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

/* Premium coupon (first one) */
.coupon-item-premium {
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
  border: 3px solid #ffd700;
  box-shadow: 0 8px 30px rgba(255, 107, 107, 0.5);
}

.coupon-item-premium .coupon-icon {
  font-size: 28px;
  animation: bounce 2s infinite;
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
  40% { transform: translateY(-12px); }
  60% { transform: translateY(-6px); }
}

/* Standard coupon (others) */
.coupon-item-standard {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  border: 2px solid #4facfe;
  box-shadow: 0 6px 25px rgba(79, 172, 254, 0.4);
}

.coupon-item-standard .coupon-icon {
  font-size: 24px;
  animation: rotate 4s linear infinite;
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.coupon-icon {
  font-size: 20px;
  min-width: 32px;
  text-align: center;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
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

/* Enhanced Product Cards */
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
}

.chat-product-card:hover {
  transform: scale(1);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
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

/* Beautiful Flash Sale Section Styling */
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

@keyframes flashShimmer {
  0% { left: -100%; }
  100% { left: 100%; }
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

@keyframes flashPulse {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.05); opacity: 0.9; }
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

/* Premium flash sale (first one) */
.flashsale-item-premium {
  background: linear-gradient(135deg, #ff4757 0%, #ff3742 100%);
  border: 3px solid #ffd700;
  box-shadow: 0 6px 24px rgba(255, 71, 87, 0.5);
}

.flashsale-item-premium .flashsale-icon {
  font-size: 24px;
  animation: flashBounce 1.5s infinite;
}

@keyframes flashBounce {
  0%, 20%, 50%, 80%, 100% { transform: translateY(0) scale(1); }
  40% { transform: translateY(-6px) scale(1.1); }
  60% { transform: translateY(-3px) scale(1.05); }
}

/* Standard flash sale (others) */
.flashsale-item-standard {
  background: linear-gradient(135deg, #ff9ff3 0%, #f368e0 100%);
  border: 2px solid #ff9ff3;
  box-shadow: 0 4px 20px rgba(255, 159, 243, 0.4);
}

.flashsale-item-standard .flashsale-icon {
  font-size: 20px;
  animation: flashRotate 3s linear infinite;
}

@keyframes flashRotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
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

.flashsale-icon {
  font-size: 20px;
  min-width: 32px;
  text-align: center;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
  margin-top: 2px;
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

@keyframes flashBadge {
  0%, 50%, 100% { opacity: 1; transform: scale(1); }
  25%, 75% { opacity: 0.8; transform: scale(1.1); }
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

/* Responsive */
@media (max-width: 480px) {
  .chat-window {
    width: calc(100vw - 20px);
    height: calc(100vh - 100px);
    position: fixed;
    top: 10px;
    left: 10px;
    right: 10px;
    bottom: 10px;
  }
  
  .chat-widget-button {
    width: 56px;
    height: 56px;
  }
  
  .messages-container {
    padding: 10px;
    gap: 10px;
  }

  .message-text {
    padding: 10px 12px;
    border-radius: 14px;
    font-size: 13px;
    line-height: 1.5;
  }

  .message-avatar {
    width: 28px;
    height: 28px;
  }

  .chat-title h3 { font-size: 16px; }
  .status { font-size: 12px; }

  .quick-actions { padding: 12px; gap: 8px; }
  .quick-action-btn { padding: 8px 12px; font-size: 12px; }

  .input-area { padding: 14px; }
  .input-container { gap: 8px; }
  .message-input { padding: 10px 14px; font-size: 13px; }
  .send-btn { width: 38px; height: 38px; }

  .products-grid { padding: 8px; gap: 10px; }
  .product-name { font-size: 13px; }
  .current-price { font-size: 14px; }

  .coupon-code {
    font-size: 16px;
  }
  
  .coupon-discount {
    font-size: 14px;
  }
  
  /* On very small screens, keep it stacked above ChatWidget */
  .ai-chatbot {
    bottom: 90px;
    right: 20px;
  }

  /* Compact coupons on mobile */
  .coupons-section { padding: 12px; border-radius: 12px; }
  .coupon-title { font-size: 14px; margin-bottom: 12px; }
  .coupon-item { gap: 10px; padding: 12px; margin: 8px 0; border-radius: 10px; }
  .coupon-name { font-size: 13px; }
  .coupon-code { font-size: 14px; padding: 3px 8px; border-radius: 12px; }
  .coupon-discount { font-size: 12px; }
  .coupon-min-order { font-size: 11px; }
  .coupon-desc { font-size: 10px; }
  .coupon-badge { font-size: 9px; padding: 4px 8px; top: 8px; right: 8px; }

  /* Compact flash sale on mobile */
  .flashsale-section { padding: 12px; border-radius: 12px; }
  .flashsale-title { font-size: 14px; margin-bottom: 12px; }
  .flashsale-item { padding: 12px; margin: 8px 0; border-radius: 10px; }
  .flashsale-name { font-size: 13px; }
  .time-label, .time-value { font-size: 10px; }
  .time-value { padding: 6px 8px; border-radius: 8px; }
  .flashsale-desc { font-size: 10px; }
 
}

/* Scroll hint bubble next to widget */
.widget-hint {
  position: fixed;
  right: 110px;
  bottom: 112px;
  background: #ffffff;
  color: #1f2937;
  border: 1px solid rgba(226, 232, 240, 0.9);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
  padding: 10px 14px;
  border-radius: 14px;
  font-size: 13px;
  z-index: 1001;
  animation: fadeInUp 300ms ease;
}

.widget-hint::after {
  content: '';
  position: absolute;
  right: -6px;
  bottom: 14px;
  width: 12px;
  height: 12px;
  background: #ffffff;
  border-left: 1px solid rgba(226, 232, 240, 0.9);
  border-bottom: 1px solid rgba(226, 232, 240, 0.9);
  transform: rotate(-45deg);
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
