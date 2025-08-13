import { ref, reactive } from 'vue'
import { useAuth } from './useAuth'

export function useAIChat() {
  const { user } = useAuth()
  
  const apiBaseUrl = import.meta.env.VITE_API_BASE_URL 
  
  const isOpen = ref(false)
  const isTyping = ref(false)
  const messages = ref([])
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

  const isSpecificProductQuestion = (message) => {
    const messageLower = message.toLowerCase()
    
    const hasQuestionWord = messageLower.includes('gì') || 
                           messageLower.includes('nào') || 
                           messageLower.includes('có') || 
                           messageLower.includes('không') ||
                           messageLower.includes('màu') ||
                           messageLower.includes('size') ||
                           messageLower.includes('giá') ||
                           messageLower.includes('còn') ||
                           messageLower.includes('hàng')
    
    return messageLower.split(' ').length >= 3 || hasQuestionWord
  }

  const filterProductsByQuery = (products, query) => {
    if (!products || products.length === 0) return []
    
    const queryLower = query.toLowerCase()
    const stopWords = ['tôi', 'muốn', 'mua', 'cần', 'tìm', 'có', 'ạ', 'à', 'và', 'hoặc', 'này', 'đó', 'kia', 'ôi', 'cho', 'với', 'trong', 'ngoài', 'trên', 'dưới', 'bên', 'của', 'là', 'thì', 'mà', 'nhưng', 'hoặc', 'vì', 'nên', 'để', 'từ', 'đến', 'tại', 'về', 'theo', 'cùng', 'cả', 'mỗi', 'mọi', 'mấy', 'bao', 'nhiêu', 'mấy', 'bao', 'nhiêu', 'mấy', 'bao', 'nhiêu']
    
    let keywords = queryLower.split(' ')
    keywords = keywords.filter(word => !stopWords.includes(word) && word.length >= 2)
    
    console.log('Filtering products for query:', queryLower)
    console.log('Keywords after filtering:', keywords)
    
    if (keywords.length === 0) return []
    
    const result = products.filter(product => {
      const productName = (product.name || '').toLowerCase()
      const categoryName = (product.categories?.name || '').toLowerCase()
      const productDescription = (product.description || '').toLowerCase()
      
      const matchingKeywords = keywords.filter(keyword => 
        productName.includes(keyword) || 
        categoryName.includes(keyword) || 
        productDescription.includes(keyword)
      )
      
      return matchingKeywords.length >= 1
    })
    
    console.log('Filtered products count:', result.length)
    console.log('Filtered products:', result.map(p => p.name))
    
    return result.slice(0, 6)
  }

  const sendMessage = async (message) => {
    if (!message.trim() || isTyping.value) return

    if (message.trim().length < 2) {
      messages.value.push({
        text: 'Vui lòng nhập tin nhắn rõ ràng hơn để tôi có thể hỗ trợ bạn tốt nhất.',
        isUser: false,
        timestamp: new Date()
      })
      return
    }

    messages.value.push({
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
        body: JSON.stringify({ 
          message,
          context: buildClientContextHint()
        })
      })

      const data = await response.json()
      
      console.log('AI Chat Response:', data)
      if (data.context && data.context.products) {
        console.log('Products in context:', data.context.products)
        console.log('Products count:', data.context.products.length)
        data.context.products.forEach(product => {
          console.log('Product:', product.name, 'MainImage:', product.mainImage)
        })
      } else {
        console.log('No products in context or context is empty')
      }

      if (data.success) {
        const aiMessage = {
          text: data.message,
          isUser: false,
          timestamp: new Date()
        }

        const userJustGreeted = isGreeting(message)

        if (userJustGreeted) {
          aiMessage.text = 'Chào bạn! Rất vui được hỗ trợ bạn hôm nay. Bạn cần tìm gì ạ?\n\nTôi có thể giúp bạn:\n• Tìm kiếm sản phẩm cụ thể\n• Xem mã giảm giá và khuyến mãi\n• Thông tin flash sale\n• Hướng dẫn mua hàng'
          messages.value.push(aiMessage)
          return
        }

        // Xử lý context sản phẩm
        if (data.context && data.context.products && data.context.products.length > 0) {
          console.log('Context products found:', data.context.products.length)
          console.log('Products:', data.context.products)
          
          // Luôn hiển thị sản phẩm nếu có trong context
          aiMessage.products = data.context.products.slice(0, 6)
          console.log('Setting products from context:', aiMessage.products.length)
        }
        
        // Xử lý context mã giảm giá
        if (data.context && data.context.coupons && data.context.coupons.length > 0) {
          const hasCouponRequest = message.toLowerCase().includes('mã giảm') || 
                                 message.toLowerCase().includes('coupon') || 
                                 message.toLowerCase().includes('khuyến mãi') || 
                                 message.toLowerCase().includes('giảm giá') || 
                                 message.toLowerCase().includes('discount')
          
          if (hasCouponRequest) {
            aiMessage.coupons = data.context.coupons.slice(0, 3)
          }
        }
        
        // Xử lý context flash sale
        if (data.context && data.context.flash_sales && data.context.flash_sales.length > 0) {
          const hasFlashSaleRequest = message.toLowerCase().includes('flash sale') || 
                                    message.toLowerCase().includes('khuyến mãi') || 
                                    message.toLowerCase().includes('giảm giá') || 
                                    message.toLowerCase().includes('hot') || 
                                    message.toLowerCase().includes('nóng')
          
          if (hasFlashSaleRequest) {
            aiMessage.flashSales = data.context.flash_sales.slice(0, 3)
          }
        }

        messages.value.push(aiMessage)
      } else {
        messages.value.push({
          text: 'Xin lỗi, tôi đang gặp sự cố. Vui lòng thử lại sau.',
          isUser: false,
          timestamp: new Date()
        })
      }
    } catch (error) {
      console.error('AI Chat Error:', error)
      messages.value.push({
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
    console.log('AIChatbot toggleChat called, current state:', isOpen.value)
    isOpen.value = !isOpen.value
    if (isOpen.value) {
      hasUnreadMessages.value = false
      unreadCount.value = 0
      // Thêm class để ẩn ChatWidget khi AIChatbot mở
      document.documentElement.classList.add('ai-chatbot-open')
    } else {
      // Xóa class khi AIChatbot đóng
      document.documentElement.classList.remove('ai-chatbot-open')
    }
    console.log('AIChatbot new state:', isOpen.value)
  }

  const addWelcomeMessage = () => {
    if (messages.value.length === 0) {
      messages.value.push({
        text: '👋 Xin chào! Tôi là trợ lý AI của DEVGANG Shop. Rất vui được hỗ trợ bạn hôm nay!\n\n🌟 Tôi có thể giúp bạn:\n\n🔍 Tìm kiếm và tư vấn sản phẩm\n🎫 Thông tin mã giảm giá & khuyến mãi\n💳 Hướng dẫn thanh toán\n🔥 Thông tin flash sale hot\n📂 Tư vấn danh mục sản phẩm\n\n💬 Hãy nhắn tin cho tôi hoặc chọn các gợi ý bên dưới nhé!',
        isUser: false,
        timestamp: new Date()
      })
    }
  }

  const clearMessages = () => {
    messages.value.length = 0
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

  const buildClientContextHint = () => {
    for (let i = messages.value.length - 1; i >= 0; i--) {
      const m = messages.value[i]
      if (!m.isUser && Array.isArray(m.products) && m.products.length > 0) {
        const ids = m.products
          .map(p => p.id)
          .filter(id => typeof id === 'number' || typeof id === 'string')
        if (ids.length > 0) {
          return { product_ids: ids }
        }
      }
    }
    return {}
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

  const getPlaceholderImage = () => {
    return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjhmYWZjIi8+CiAgPHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzY0NzQ4YiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pgo8L3N2Zz4='
  }

  const getImageUrl = (product) => {
    const mainImage = product.mainImage || product.main_image
    if (mainImage && mainImage.image_url) {
      return mainImage.image_url
    }
    return getPlaceholderImage()
  }

  const handleImageError = (event) => {
    event.target.src = getPlaceholderImage()
  }

  const viewProduct = (product) => {
    window.open(`/san-pham/${product.slug}`, '_blank')
  }
  
  // Cleanup function
  const cleanup = () => {
    document.documentElement.classList.remove('ai-chatbot-open')
    document.documentElement.classList.remove('chatwidget-open')
  }
  
  return {
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
    formatTime,
    formatPrice,
    calculateDiscountPercentage,
    getImageUrl,
    handleImageError,
    viewProduct,
    cleanup
  }
}
