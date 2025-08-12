import { ref, reactive } from 'vue'
import { useAuth } from './useAuth'

export function useAIChat() {
  const { user } = useAuth()
  
  const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'
  
  // State
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

  // Helper methods để lọc sản phẩm
  const isSpecificProductQuestion = (message) => {
    const specificKeywords = ['váy', 'áo', 'quần', 'giày', 'túi', 'đầm', 'sơ mi', 'polo', 'khoác']
    return specificKeywords.some(keyword => message.includes(keyword))
  }

  const filterProductsByQuery = (products, query) => {
    if (!products || products.length === 0) return []
    
    const queryLower = query.toLowerCase()
    const stopWords = ['tôi', 'muốn', 'mua', 'cần', 'tìm', 'có', 'ạ', 'à', 'và', 'hoặc', 'này', 'đó', 'kia', 'ôi']
    
    // Loại bỏ stop words và lấy từ khóa chính
    let keywords = queryLower.split(' ')
    keywords = keywords.filter(word => !stopWords.includes(word) && word.length >= 2)
    
    console.log('Filtering products for query:', queryLower)
    console.log('Keywords after filtering:', keywords)
    
    if (keywords.length === 0) return products
    
    // Lọc sản phẩm theo từ khóa với logic chính xác hơn
    // ƯU TIÊN các từ khóa cụ thể trước (áo sơ mi, áo polo, áo khoác) trước từ khóa chung (áo)
    const result = products.filter(product => {
      const productName = (product.name || '').toLowerCase()
      const categoryName = (product.categories?.name || '').toLowerCase()
      
      // ---- KIỂM TRA CÁC TỪ KHÓA CỤ THỂ TRƯỚC ----
      
      // Nếu có từ khóa "áo sơ mi", chỉ hiển thị sản phẩm có "sơ mi" trong tên hoặc danh mục
      if (queryLower.includes('áo sơ mi') || queryLower.includes('sơ mi')) {
        return productName.includes('sơ mi') || categoryName.includes('sơ mi')
      }
      
      // Nếu có từ khóa "áo polo", chỉ hiển thị sản phẩm có "polo" trong tên hoặc danh mục
      if (queryLower.includes('áo polo') || queryLower.includes('polo')) {
        return productName.includes('polo') || categoryName.includes('polo')
      }
      
      // Nếu có từ khóa "áo khoác", chỉ hiển thị sản phẩm có "khoác" trong tên hoặc danh mục
      if (queryLower.includes('áo khoác') || queryLower.includes('khoác')) {
        return productName.includes('khoác') || categoryName.includes('khoác')
      }
      
      // ---- KIỂM TRA CÁC TỪ KHÓA CHUNG ----
      
      // Nếu có từ khóa "váy", chỉ hiển thị sản phẩm có "váy" trong tên hoặc danh mục
      if (queryLower.includes('váy')) {
        return productName.includes('váy') || categoryName.includes('váy')
      }
      
      // Nếu có từ khóa "áo" (chung), hiển thị sản phẩm có "áo" trong tên hoặc danh mục
      if (queryLower.includes('áo')) {
        return productName.includes('áo') || categoryName.includes('áo')
      }
      
      // Nếu có từ khóa "quần", chỉ hiển thị sản phẩm có "quần" trong tên hoặc danh mục
      if (queryLower.includes('quần')) {
        return productName.includes('quần') || categoryName.includes('quần')
      }
      
      // Nếu có từ khóa "giày", chỉ hiển thị sản phẩm có "giày" trong tên hoặc danh mục
      if (queryLower.includes('giày')) {
        return productName.includes('giày') || categoryName.includes('giày')
      }
      
      // Nếu có từ khóa "túi", chỉ hiển thị sản phẩm có "túi" trong tên hoặc danh mục
      if (queryLower.includes('túi')) {
        return productName.includes('túi') || categoryName.includes('túi')
      }
      
      // Nếu có từ khóa "đầm", chỉ hiển thị sản phẩm có "đầm" trong tên hoặc danh mục
      if (queryLower.includes('đầm')) {
        return productName.includes('đầm') || categoryName.includes('đầm')
      }
      
      // Xử lý trường hợp "mua" - ưu tiên từ khóa cụ thể trước
      if (queryLower.includes('mua')) {
        // Kiểm tra từ khóa cụ thể trước
        if (queryLower.includes('áo sơ mi') || queryLower.includes('sơ mi')) {
          return productName.includes('sơ mi') || categoryName.includes('sơ mi')
        }
        if (queryLower.includes('áo polo') || queryLower.includes('polo')) {
          return productName.includes('polo') || categoryName.includes('polo')
        }
        if (queryLower.includes('áo khoác') || queryLower.includes('khoác')) {
          return productName.includes('khoác') || categoryName.includes('khoác')
        }
        
        // Sau đó mới kiểm tra từ khóa chung
        const productKeywords = ['váy', 'áo', 'quần', 'giày', 'túi', 'đầm']
        const foundKeyword = productKeywords.find(keyword => queryLower.includes(keyword))
        
        if (foundKeyword) {
          return productName.includes(foundKeyword) || categoryName.includes(foundKeyword)
        }
      }
      
      // Nếu không có từ khóa cụ thể, sử dụng logic cũ
      const hasMatchingKeyword = keywords.some(keyword => 
        productName.includes(keyword) || categoryName.includes(keyword)
      )
      return hasMatchingKeyword
    })
    
    console.log('Filtered products count:', result.length)
    return result
  }

  const sendMessage = async (message) => {
    if (!message.trim() || isTyping.value) return

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

        // Chỉ hiển thị dữ liệu phù hợp với loại câu hỏi
        if (!userJustGreeted && data.context) {
          // Lọc sản phẩm để chỉ hiển thị sản phẩm thực sự liên quan
          if (data.context.products && data.context.products.length > 0) {
            // Kiểm tra xem câu hỏi có liên quan đến sản phẩm cụ thể không
            const userMessageLower = message.toLowerCase()
            const isSpecificQuestion = isSpecificProductQuestion(userMessageLower)
            
            if (isSpecificQuestion) {
              // Lọc sản phẩm theo từ khóa trong câu hỏi
              const filteredProducts = filterProductsByQuery(data.context.products, userMessageLower)
              console.log('Original products:', data.context.products.length)
              console.log('Filtered products:', filteredProducts.length)
              console.log('Query:', userMessageLower)
              if (filteredProducts.length > 0) {
                aiMessage.products = filteredProducts
              }
            } else {
              // Nếu là câu hỏi chung, hiển thị tất cả sản phẩm
              aiMessage.products = data.context.products
            }
          }
          
          if (data.context.coupons && data.context.coupons.length > 0) {
            aiMessage.coupons = data.context.coupons
          }
          
          if (data.context.flash_sales && data.context.flash_sales.length > 0) {
            aiMessage.flashSales = data.context.flash_sales
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
    isOpen.value = !isOpen.value
    if (isOpen.value) {
      hasUnreadMessages.value = false
      unreadCount.value = 0
    }
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

  // Chat helpers
  const buildClientContextHint = () => {
    // Lấy danh sách product_ids từ tin nhắn AI gần nhất có products
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
    formatTime,
    
    // Chat helpers
    formatPrice,
    calculateDiscountPercentage,
    getPlaceholderImage,
    getImageUrl,
    handleImageError,
    viewProduct,
    
    // Product filtering helpers
    isSpecificProductQuestion,
    filterProductsByQuery
  }
}
