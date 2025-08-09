import { ref, reactive } from 'vue'
import { useAuth } from './useAuth'

export function useAIChat() {
  const { user } = useAuth()
  
  const apiBaseUrl = import.meta.env.VITE_API_BASE_URL
  
  const isOpen = ref(false)
  const isTyping = ref(false)
  const messages = reactive([])
  const currentMessage = ref('')
  const hasUnreadMessages = ref(false)
  const unreadCount = ref(0)

  const normalizeText = (text) => text
    .toLowerCase()
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .replace(/[^a-z0-9\s]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()

  const isGreeting = (text) => {
    const t = normalizeText(text)
    if (t.length === 0) return false

    const tokens = t.split(' ').filter(Boolean)
    const tokenSet = new Set(tokens)
    const maxGreetingWords = 4
    if (tokens.length > maxGreetingWords) return false

    const keywordTokens = ['hi', 'hello', 'alo', 'chao', 'xin', 'hey']
    const hasSimpleToken = keywordTokens.some(k => tokenSet.has(k))

    const exactPhrases = [
      'xin chao', 'chao', 'chao ban', 'chao shop', 'chao ad',
      'good morning', 'good afternoon', 'good evening'
    ]

    const hasChaoSubstring = t.includes('chao') || t.includes('xinchao')

    return (
      exactPhrases.includes(t) ||
      (hasSimpleToken && tokens.length <= maxGreetingWords) ||
      hasChaoSubstring
    )
  }

  const sendMessage = async (message) => {
    if (!message.trim() || isTyping.value) return

    messages.push({
      text: message,
      isUser: true,
      timestamp: new Date()
    })

    isTyping.value = true

    try {
      const response = await fetch(`${apiBaseUrl}/api/ai/chat`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ message })
      })

      const data = await response.json()
      
      console.log('AI Chat Response:', data)
      if (data.context && data.context.products) {
        console.log('Products in context:', data.context.products)
        data.context.products.forEach(product => {
          console.log('Product:', product.name, 'MainImage:', product.mainImage)
        })
      }

      if (data.success) {
        const aiMessage = {
          text: data.message,
          isUser: false,
          timestamp: new Date()
        }

        const userJustGreeted = isGreeting(message)

        if (userJustGreeted) {
          aiMessage.text = 'Chào bạn! Rất vui được hỗ trợ bạn hôm nay. Bạn cần tìm gì ạ?'
        }

        if (!userJustGreeted && data.context && data.context.products) {
          aiMessage.products = data.context.products
        }

        if (!userJustGreeted && data.context && data.context.coupons) {
          aiMessage.coupons = data.context.coupons
        }

        if (!userJustGreeted && data.context && data.context.flash_sales) {
          aiMessage.flashSales = data.context.flash_sales
        }

        messages.push(aiMessage)
      } else {
        messages.push({
          text: 'Xin lỗi, tôi đang gặp sự cố. Vui lòng thử lại sau.',
          isUser: false,
          timestamp: new Date()
        })
      }
    } catch (error) {
      console.error('AI Chat Error:', error)
      messages.push({
        text: 'Xin lỗi, có lỗi xảy ra. Vui lòng thử lại sau.',
        isUser: false,
        timestamp: new Date()
      })
    } finally {
      isTyping.value = false
    }
  }



  const searchProducts = async (query) => {
    try {
      const response = await fetch(`${apiBaseUrl}/api/ai/search-products?query=${encodeURIComponent(query)}`, {
        headers: {
          'Accept': 'application/json'
        }
      })

      const data = await response.json()
      return data.success ? data.products : []
    } catch (error) {
      console.error('Search Products Error:', error)
      return []
    }
  }

  const getAvailableCoupons = async () => {
    try {
      const response = await fetch(`${apiBaseUrl}/api/ai/coupons`, {
        headers: {
          'Accept': 'application/json'
        }
      })

      const data = await response.json()
      return data.success ? data.coupons : []
    } catch (error) {
      console.error('Get Coupons Error:', error)
      return []
    }
  }

  const getActiveFlashSales = async () => {
    try {
      const response = await fetch(`${apiBaseUrl}/api/ai/flash-sales`, {
        headers: {
          'Accept': 'application/json'
        }
      })

      const data = await response.json()
      return data.success ? data.flash_sales : []
    } catch (error) {
      console.error('Get Flash Sales Error:', error)
      return []
    }
  }

  const toggleChat = () => {
    isOpen.value = !isOpen.value
    if (isOpen.value) {
      hasUnreadMessages.value = false
      unreadCount.value = 0
    }
  }

  const addWelcomeMessage = () => {
    if (messages.length === 0) {
      messages.push({
        text: 'Xin chào! Tôi là AI trợ lý của cửa hàng. Tôi có thể giúp bạn:\n\n• Tìm kiếm và tư vấn sản phẩm\n• Thông tin về mã giảm giá và khuyến mãi\n• Hướng dẫn quy trình thanh toán\n• Thông tin về flash sale\n• Tư vấn về danh mục sản phẩm\n\nBạn có thể hỏi tôi bất cứ điều gì!',
        isUser: false,
        timestamp: new Date()
      })
    }
  }

  const clearMessages = () => {
    messages.length = 0
  }

  const formatMessage = (text) => {
    return text.replace(/\n/g, '<br>')
  }

  const formatTime = (timestamp) => {
    return new Date(timestamp).toLocaleTimeString('vi-VN', {
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  return {
    // State
    isOpen,
    isTyping,
    messages,
    currentMessage,
    hasUnreadMessages,
    unreadCount,
    
    sendMessage,
    toggleChat,
    addWelcomeMessage,
    clearMessages,
    searchProducts,
    getAvailableCoupons,
    getActiveFlashSales,
    formatMessage,
    formatTime
  }
}
