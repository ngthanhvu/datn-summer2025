<template>
  <div class="ai-chatbot">
    <!-- Chat Widget Button -->
    <div v-if="!isOpen" @click="toggleChat" class="chat-widget-button" :class="{ 'pulse': hasUnreadMessages }">
      <div class="chat-icon">
        <img class="chat-icon-img" src="/chatbot.gif" alt="Mở trò chuyện" />
      </div>
      <div class="chat-badge" v-if="hasUnreadMessages">{{ unreadCount }}</div>
    </div>

    <!-- Scroll hint bubble -->
    <div v-if="!isOpen" class="widget-hint">
      Xin chào! Tôi là trợ lí ảo.
    </div>

    <!-- Chat Window -->
    <div v-if="isOpen" class="chat-window">
      <!-- Header -->
      <div class="chat-header">
        <div class="chat-header-info">
          <div class="ai-avatar">
            <img class="ai-avatar-img"
              src="https://lapsedhistorian.com/wp-content/uploads/2024/09/125887131_Chatbot%20Message%20Bubble.jpg"
              alt="Trợ Lí DEVGANG" />
          </div>
          <div class="chat-title">
            <h3>Trợ Lí DEVGANG</h3>
            <span class="status" :class="{ 'online': isOnline }">
              {{ isOnline ? 'Đang hoạt động' : 'Đang kết nối...' }}
            </span>
          </div>
        </div>
        <button @click="toggleChat" class="close-btn" type="button" aria-label="Đóng chatbot">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12L19 6.41Z"
              fill="currentColor" />
          </svg>
        </button>
      </div>

      <!-- Messages Container -->
      <div class="messages-container" ref="messagesContainer">
        <div v-for="(message, index) in messages" :key="index" class="message"
          :class="{ 'user-message': message.isUser, 'ai-message': !message.isUser }">
          <div class="message-avatar" v-if="!message.isUser">
            <img class="ai-message-avatar-img"
              src="https://lapsedhistorian.com/wp-content/uploads/2024/09/125887131_Chatbot%20Message%20Bubble.jpg"
              alt="Trợ Lí DEVGANG" />
          </div>
          <div class="message-content">
            <div class="message-text">
              <div v-html="formatMessage(message.text)"></div>

              <!-- Product Cards - LUÔN hiển thị khi có sản phẩm -->
              <ProductCard v-if="message.products && message.products.length > 0" :products="message.products"
                :show-purchase-form="message.show_purchase_form || false" @view-product="viewProduct"
                @add-to-cart-success="handleAddToCartSuccess" />

              <!-- Coupon Cards -->
              <CouponCard v-if="message.coupons && message.coupons.length > 0" :coupons="message.coupons"
                @save-coupon="handleSaveCoupon" @use-coupon="useCoupon" />

              <!-- Flash Sale Cards -->
              <FlashSaleCard v-if="message.flashSales && message.flashSales.length > 0"
                :flash-sales="message.flashSales" />

              <!-- Order Tracking Card -->
              <OrderTrackingCard v-if="message.orderTracking" @order-found="handleOrderFound" />

              <!-- Payment Button -->
              <div v-if="message.showPaymentButton" class="payment-button-container">
                <button @click="goToCheckout" class="payment-button">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M20 4H4C2.89 4 2.01 4.89 2.01 6L2 18C2 19.11 2.89 20 4 20H20C21.11 20 22 19.11 22 18V6C22 4.89 21.11 4 20 4ZM20 18H4V12H20V18ZM20 8H4V6H20V8Z"
                      fill="currentColor" />
                  </svg>
                  Thanh toán ngay
                </button>
              </div>
            </div>

            <div class="message-time">{{ formatTime(message.timestamp) }}</div>
          </div>
        </div>

        <div v-if="isTyping" class="message ai-message">
          <div class="message-avatar">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 1H5C3.89 1 3 1.89 3 3V21C3 22.11 3.89 23 5 23H19C20.11 23 21 22.11 21 21V9ZM19 21H5V3H13V9H19V21Z"
                fill="currentColor" />
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
        <button v-for="action in quickActions" :key="action.text" @click="sendQuickMessage(action.text)"
          class="quick-action-btn">
          {{ action.text }}
        </button>
      </div>

      <!-- Input Area -->
      <div class="input-area">
        <div class="input-container">
          <textarea v-model="currentMessage" @keydown.enter.prevent="sendMessage" @keydown.enter="handleEnter"
            placeholder="Nhập tin nhắn của bạn..." class="message-input" rows="1" ref="messageInput"></textarea>
          <button @click="sendMessage" class="send-btn" :disabled="!currentMessage.trim() || isTyping">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M2.01 21L23 12L2.01 3L2 10L17 12L2 14L2.01 21Z" fill="currentColor" />
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
import { useCart } from '../composable/useCart'
import ProductCard from './chat/ProductCard.vue'
import CouponCard from './chat/CouponCard.vue'
import FlashSaleCard from './chat/FlashSaleCard.vue'
import OrderTrackingCard from './chat/OrderTrackingCard.vue'

export default {
  name: 'AIChatbot',
  components: {
    ProductCard,
    CouponCard,
    FlashSaleCard,
    OrderTrackingCard
  },
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
      formatTime,
      formatPrice,
      viewProduct,
      cleanup
    } = useAIChat()

    const {
      cleanupOldCartItems,
      clearCartAfterPayment
    } = useCart()

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
      { text: 'Mã giảm giá 🎉' },
      { text: 'Flash sale 🔥' },
      { text: 'Hướng dẫn thanh toán 💳' },
      { text: 'Danh mục sản phẩm 🛒' },
      { text: 'Tra cứu đơn hàng 📦' },
      { text: 'Tìm theo giá 💰' }
    ]

    const toggleChat = () => {
      toggleAIChat()
      if (isOpen.value) {
        if (messages.value.length === 0) {
          addWelcomeMessage()
        }
        nextTick(() => {
          scrollToBottom()
          messageInput.value?.focus()
        })
      }
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

    const handleAddToCartSuccess = (data) => {
      // Thêm tin nhắn xác nhận khi thêm vào giỏ hàng thành công
      const price = data.variant?.price || data.product?.price || 0
      const size = data.variant?.size || 'N/A'
      const color = data.variant?.color || 'N/A'

      // Hiển thị phản hồi tự nhiên từ AI với nút thanh toán
      messages.value.push({
        text: `Tôi đã thêm vào giỏ hàng cho bạn rồi! 🛒\n\n📦 **${data.product.name}**\n📏 Size: ${size} | 🎨 Màu: ${color}\n📊 Số lượng: ${data.quantity}\n💰 Giá: ${formatPrice(price)}\n\n✅ Sản phẩm đã được thêm thành công vào giỏ hàng của bạn!\n\nBạn có muốn thanh toán ngay không ạ?`,
        isUser: false,
        timestamp: new Date(),
        showPaymentButton: true // Flag để hiển thị nút thanh toán
      })

      nextTick(() => {
        scrollToBottom()
      })
    }

    const handleOrderFound = (order) => {
      // Thêm tin nhắn xác nhận khi tìm thấy đơn hàng
      const finalPrice = order.final_price || order.total_price || 0

      messages.value.push({
        text: `✅ Đã tìm thấy đơn hàng!\n\n📦 Mã đơn hàng: ${order.tracking_code}\n📅 Ngày đặt: ${new Date(order.created_at).toLocaleDateString('vi-VN')}\n💰 Tổng tiền: ${formatPrice(finalPrice)}\n📊 Trạng thái: ${getOrderStatusText(order.status)}`,
        isUser: false,
        timestamp: new Date()
      })

      nextTick(() => {
        scrollToBottom()
      })
    }

    const handleSaveCoupon = async (coupon) => {
      try {
        // Gọi API để lưu mã giảm giá vào database
        const response = await fetch(`http://127.0.0.1:8000/api/coupons/${coupon.id}/claim`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}` // JWT token
          }
        })

        const result = await response.json()

        if (result.status) {
          // Thêm tin nhắn xác nhận khi lưu mã giảm giá thành công
          messages.value.push({
            text: `🎉 **Đã lưu mã giảm giá thành công!**\n\n💎 **${coupon.name}**\n🔑 Mã: \`${coupon.code}\`\n💰 Giảm: ${coupon.type === 'percent' ? `${coupon.value}%` : formatPrice(coupon.value)}\n📦 Đơn tối thiểu: ${formatPrice(coupon.min_order_value)}\n⏰ Hạn sử dụng: ${new Date(coupon.end_date).toLocaleDateString('vi-VN')}\n\n✨ **Mã giảm giá đã được lưu vào tài khoản của bạn!**\n💡 Bạn có thể sử dụng khi thanh toán đơn hàng.\n\n🚀 **Tiết kiệm ngay hôm nay!**`,
            isUser: false,
            timestamp: new Date()
          })

        } else {
          messages.value.push({
            text: `❌ **${result.message}**\n\nVui lòng thử lại hoặc liên hệ hỗ trợ.`,
            isUser: false,
            timestamp: new Date()
          })

          console.error('API error saving coupon:', result.message)
        }

        nextTick(() => {
          scrollToBottom()
        })

      } catch (error) {
        console.error('Error saving coupon:', error)

        // Thêm tin nhắn lỗi
        messages.value.push({
          text: `❌ **Có lỗi xảy ra khi lưu mã giảm giá!**\n\nVui lòng thử lại hoặc liên hệ hỗ trợ.`,
          isUser: false,
          timestamp: new Date()
        })

        nextTick(() => {
          scrollToBottom()
        })
      }
    }

    // Method để sử dụng mã giảm giá
    const useCoupon = async (couponId) => {
      try {
        const response = await fetch(`http://127.0.0.1:8000/api/coupons/${couponId}/use`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })

        const result = await response.json()

        if (result.status) {
          messages.value.push({
            text: `✅ **Đã sử dụng mã giảm giá thành công!**\n\n🎉 Mã giảm giá đã được áp dụng cho đơn hàng của bạn.`,
            isUser: false,
            timestamp: new Date()
          })
          await showSavedCoupons()

        } else {
          messages.value.push({
            text: `❌ **${result.message}**\n\nVui lòng thử lại hoặc liên hệ hỗ trợ.`,
            isUser: false,
            timestamp: new Date()
          })

          console.error('API error using coupon:', result.message)
        }

        nextTick(() => {
          scrollToBottom()
        })

      } catch (error) {
        console.error('Error using coupon:', error)

        messages.value.push({
          text: `❌ **Có lỗi xảy ra khi sử dụng mã giảm giá!**\n\nVui lòng thử lại hoặc liên hệ hỗ trợ.`,
          isUser: false,
          timestamp: new Date()
        })

        nextTick(() => {
          scrollToBottom()
        })
      }
    }

    // Method để xem mã giảm giá đã lưu
    const showSavedCoupons = async () => {
      try {
        // Gọi API để lấy mã giảm giá đã lưu từ database
        const response = await fetch(`http://127.0.0.1:8000/api/coupons/my-coupons`, {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}` // JWT token
          }
        })

        const result = await response.json()

        if (result.status) {
          const savedCoupons = result.coupons

          if (savedCoupons.length === 0) {
            messages.value.push({
              text: `📝 **Mã giảm giá đã lưu:**\n\n😔 Bạn chưa lưu mã giảm giá nào.\n💡 Hãy lưu mã giảm giá để tiết kiệm khi mua hàng!`,
              isUser: false,
              timestamp: new Date()
            })
          } else {
            let messageText = `📝 **Mã giảm giá đã lưu (${savedCoupons.length} mã):**\n\n`

            savedCoupons.forEach((coupon, index) => {
              messageText += `${index + 1}. 💎 **${coupon.name}**\n`
              messageText += `   🔑 Mã: \`${coupon.code}\`\n`
              messageText += `   💰 Giảm: ${coupon.type === 'percent' ? `${coupon.value}%` : formatPrice(coupon.value)}\n`
              messageText += `   📦 Đơn tối thiểu: ${formatPrice(coupon.min_order_value)}\n`
              messageText += `   ⏰ Hạn sử dụng: ${new Date(coupon.end_date).toLocaleDateString('vi-VN')}\n`
              messageText += `   📅 Lưu lúc: ${new Date(coupon.pivot.claimed_at).toLocaleDateString('vi-VN')}\n`
              messageText += `   📊 Trạng thái: ${coupon.pivot.status === 'claimed' ? '🟢 Chưa sử dụng' : '🔴 Đã sử dụng'}\n\n`
            })

            messageText += `✨ **Tổng cộng ${savedCoupons.length} mã giảm giá đã lưu!**\n💡 Bạn có thể sử dụng các mã này khi thanh toán đơn hàng.`

            messages.value.push({
              text: messageText,
              isUser: false,
              timestamp: new Date()
            })
          }
        } else {
          // Thêm tin nhắn lỗi từ API
          messages.value.push({
            text: `❌ **${result.message}**\n\nVui lòng thử lại hoặc liên hệ hỗ trợ.`,
            isUser: false,
            timestamp: new Date()
          })

          console.error('API error getting saved coupons:', result.message)
        }

        nextTick(() => {
          scrollToBottom()
        })

      } catch (error) {
        console.error('Error showing saved coupons:', error)

        messages.value.push({
          text: `❌ **Có lỗi xảy ra khi hiển thị mã giảm giá đã lưu!**\n\nVui lòng thử lại hoặc liên hệ hỗ trợ.`,
          isUser: false,
          timestamp: new Date()
        })

        nextTick(() => {
          scrollToBottom()
        })
      }
    }

    const goToCheckout = async () => {
      try {
        // Dọn dẹp cart items cũ trước
        await cleanupOldCartItems()

        window.location.href = '/thanh-toan'
      } catch (error) {
        console.error('Error cleaning up cart before payment:', error)
        window.location.href = '/thanh-toan'
      }
    }

    const getOrderStatusText = (status) => {
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

    watch(messages, (newMessages) => {
      if (!isOpen.value && newMessages.length > 0) {
        const lastMessage = newMessages[newMessages.length - 1]
        if (!lastMessage.isUser) {
          hasUnreadMessages.value = true
          unreadCount.value++
        }
      }
    })

    onMounted(async () => {
      const textarea = messageInput.value
      if (textarea) {
        textarea.addEventListener('input', function () {
          this.style.height = 'auto'
          this.style.height = this.scrollHeight + 'px'
        })
      }
      window.addEventListener('scroll', handleScrollShowHint, { passive: true })

      // Dọn dẹp cart items cũ khi khởi tạo chatbot
      try {
        await cleanupOldCartItems()
      } catch (error) {
        console.error('Error cleaning up cart on mount:', error)
      }
    })

    onUnmounted(() => {
      window.removeEventListener('scroll', handleScrollShowHint)
      if (scrollHintTimer) clearTimeout(scrollHintTimer)
      cleanup()
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
      handleAddToCartSuccess,
      handleOrderFound,
      handleSaveCoupon,
      useCoupon,
      showSavedCoupons,
      goToCheckout,
      formatMessage,
      formatTime,
      formatPrice,
      viewProduct
    }
  }
}
</script>

<style scoped>
@import url('../assets/css/chat-animations.css');

.ai-chatbot {
  position: fixed;
  bottom: 90px;
  right: 20px;
  z-index: 10;
  font-family: 'Inter', sans-serif;
  max-width: 100vw;
  max-height: 100vh;
  overflow: hidden;
}

:root.chatwidget-open .ai-chatbot .chat-widget-button {
  display: none;
}

:root.chatwidget-open .ai-chatbot .chat-window {
  display: none;
}

:root.chatwidget-open .ai-chatbot .widget-hint {
  display: none;
}

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
  z-index: 9999;
}

.chat-widget-button:hover {
  transform: scale(1.05);
  box-shadow: none;
}

.chat-widget-button.pulse {
  animation: buttonPulse 2s infinite;
}



.chat-icon-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 0;
  display: block;
  pointer-events: none;
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
  z-index: 10000;
}

.chat-window {
  width: 480px;
  height: 600px;
  background:
    linear-gradient(rgba(255, 255, 255, 0.88), rgba(248, 250, 252, 0.88)),
    url('/ai-chatbot-bg.png') center/cover no-repeat;
  border-radius: 24px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid #d4e6ff;
  backdrop-filter: blur(20px);
  max-width: 100vw;
  max-height: 100vh;
  z-index: 9999;
  position: relative;
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
  z-index: 10000;
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
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  cursor: pointer;
  padding: 10px;
  border-radius: 50%;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  position: relative;
  z-index: 10;
  min-width: 40px;
  min-height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  outline: none;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: scale(1.1);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.close-btn:active {
  transform: scale(0.95);
}

.close-btn:focus {
  outline: 2px solid rgba(255, 255, 255, 0.5);
  outline-offset: 2px;
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
  overflow-x: hidden;
  display: flex;
  flex-direction: column;
  gap: 12px;
  background: transparent;
  max-width: 100%;
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
  max-width: 100%;
  overflow: hidden;
}

.message-text {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  padding: 16px;
  border-radius: 18px;
  font-size: 14px;
  line-height: 1.6;
  color: #1a202c;
  border: 1px solid rgba(226, 232, 240, 0.6);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(0, 0, 0, 0.04);
  backdrop-filter: blur(10px);
  position: relative;
  overflow: visible;
  word-wrap: break-word;
  word-break: break-word;
  max-width: 100%;
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

.typing-indicator span:nth-child(1) {
  animation-delay: -0.32s;
}

.typing-indicator span:nth-child(2) {
  animation-delay: -0.16s;
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

.message-text strong {
  font-weight: 600;
  color: #2d3748;
}

.payment-button-container {
  margin-top: 16px;
  display: flex;
  justify-content: center;
}

.payment-button {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
  backdrop-filter: blur(10px);
}

.payment-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
}

.payment-button:active {
  transform: translateY(0);
}

.payment-button svg {
  width: 18px;
  height: 18px;
}

@media (max-width: 480px) {
  .ai-chatbot {
    bottom: 90px;
    right: 20px;
    z-index: 9999;
  }

  .chat-window {
    width: calc(100vw - 20px);
    height: calc(100vh - 120px);
    position: fixed;
    top: 80px;
    left: 10px;
    right: 10px;
    bottom: 20px;
    max-width: 100vw;
    max-height: 100vh;
    overflow: hidden;
    border-radius: 24px;
    z-index: 9999;
  }

  .chat-widget-button {
    width: 56px;
    height: 56px;
    z-index: 9999;
  }

  .messages-container {
    padding: 16px;
    gap: 12px;
  }

  .message-text {
    padding: 14px 16px;
    border-radius: 18px;
    font-size: 13px;
    line-height: 1.5;
  }

  .message-avatar {
    width: 32px;
    height: 32px;
  }

  .chat-title h3 {
    font-size: 18px;
  }

  .status {
    font-size: 13px;
  }

  .quick-actions {
    padding: 16px;
    gap: 10px;
  }

  .quick-action-btn {
    padding: 10px 16px;
    font-size: 12px;
  }

  .input-area {
    padding: 20px;
  }

  .input-container {
    gap: 12px;
  }

  .message-input {
    padding: 12px 18px;
    font-size: 13px;
  }

  .send-btn {
    width: 42px;
    height: 42px;
  }

  .payment-button {
    padding: 10px 20px;
    font-size: 13px;
  }

  .payment-button svg {
    width: 16px;
    height: 16px;
  }

  .close-btn {
    min-width: 44px;
    min-height: 44px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.3);
    z-index: 10000;
  }

  .close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.05);
  }

  .close-btn svg {
    width: 18px;
    height: 18px;
  }

  .chat-header {
    padding: 20px 16px 16px;
    z-index: 10000;
    position: relative;
  }

  .chat-header-info {
    gap: 10px;
  }

  .ai-avatar {
    width: 40px;
    height: 40px;
  }

  .chat-title h3 {
    font-size: 16px;
    line-height: 1.2;
  }

  .status {
    font-size: 12px;
  }
}

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
  z-index: 9999;
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

@media (max-width: 360px) {
  .chat-window {
    width: calc(100vw - 16px);
    height: calc(100vh - 140px);
    top: 100px;
    left: 8px;
    right: 8px;
    bottom: 20px;
  }

  .chat-header {
    padding: 16px 12px 12px;
  }

  .chat-title h3 {
    font-size: 14px;
  }

  .status {
    font-size: 11px;
  }

  .ai-avatar {
    width: 36px;
    height: 36px;
  }

  .close-btn {
    min-width: 40px;
    min-height: 40px;
    padding: 10px;
  }

  .close-btn svg {
    width: 16px;
    height: 16px;
  }
}

@media (max-width: 480px) and (orientation: landscape) {
  .chat-window {
    height: calc(100vh - 80px);
    top: 60px;
    bottom: 20px;
  }

  .chat-header {
    padding: 16px 20px;
  }

  .messages-container {
    padding: 12px 16px;
  }

  .input-area {
    padding: 16px 20px;
  }
}

@media (max-height: 600px) {
  .chat-window {
    height: calc(100vh - 100px);
    top: 60px;
  }

  .chat-header {
    padding: 16px 20px;
  }

  .messages-container {
    padding: 12px 16px;
  }

  .quick-actions {
    padding: 12px 16px;
  }

  .input-area {
    padding: 16px 20px;
  }
}
</style>
