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
            <div class="message-text">
              <div v-html="formatMessage(message.text)"></div>
              
              <!-- Product Cards -->
              <ProductCard 
                v-if="message.products && message.products.length > 0" 
                :products="message.products" 
                @view-product="viewProduct"
              />
               
              <!-- Coupon Cards -->
              <CouponCard 
                v-if="message.coupons && message.coupons.length > 0" 
                :coupons="message.coupons" 
              />
               
              <!-- Flash Sale Cards -->
              <FlashSaleCard 
                v-if="message.flashSales && message.flashSales.length > 0" 
                :flash-sales="message.flashSales" 
              />
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
import ProductCard from './chat/ProductCard.vue'
import CouponCard from './chat/CouponCard.vue'
import FlashSaleCard from './chat/FlashSaleCard.vue'

export default {
  name: 'AIChatbot',
  components: {
    ProductCard,
    CouponCard,
    FlashSaleCard
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
      viewProduct
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
      { text: 'Mã giảm giá 🎉' },
      { text: 'Flash sale 🔥' },
      { text: 'Hướng dẫn thanh toán 💳' },
      { text: 'Danh mục sản phẩm 🛒' }
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
  z-index: 1000;
  font-family: 'Inter', sans-serif;
  max-width: 100vw;
  max-height: 100vh;
  overflow: hidden;
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
  animation: buttonPulse 2s infinite;
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
  max-width: 100vw;
  max-height: 100vh;
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

.typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
.typing-indicator span:nth-child(2) { animation-delay: -0.16s; }



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

/* Simple text formatting for chat messages */
.message-text strong {
  font-weight: 600;
  color: #2d3748;
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
    max-width: 100vw;
    max-height: 100vh;
    overflow: hidden;
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

  /* On very small screens, keep it stacked above ChatWidget */
  .ai-chatbot {
    bottom: 90px;
    right: 20px;
  }
 
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


</style>
