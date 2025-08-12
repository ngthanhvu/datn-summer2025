import { ref, reactive } from 'vue'
import { useAuth } from './useAuth'

export function useAIChat() {
  const { user } = useAuth()
  
  const apiBaseUrl = import.meta.env.VITE_API_BASE_URL 
  
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
    const messageLower = message.toLowerCase()
    
    // Các từ khóa sản phẩm chính
    const productKeywords = [
      'váy', 'áo', 'quần', 'giày', 'túi', 'đầm', 'sơ mi', 'polo', 'khoác',
      'shirt', 'pants', 'dress', 'shoes', 'bag', 'jacket', 'coat', 'blazer'
    ]
    
    // Các từ khóa hành động liên quan đến sản phẩm
    const actionKeywords = [
      'mua', 'tìm', 'cần', 'muốn', 'thích', 'xem', 'cho', 'với', 'giá', 'giá bao nhiêu',
      'có không', 'còn không', 'màu gì', 'size nào', 'kiểu dáng', 'chất liệu'
    ]
    
    // Kiểm tra xem có từ khóa sản phẩm không
    const hasProductKeyword = productKeywords.some(keyword => messageLower.includes(keyword))
    
    // Kiểm tra xem có từ khóa hành động không
    const hasActionKeyword = actionKeywords.some(keyword => messageLower.includes(keyword))
    
    // Chỉ coi là câu hỏi sản phẩm cụ thể khi có cả từ khóa sản phẩm và hành động
    // hoặc có từ khóa sản phẩm rất cụ thể
    const specificProductKeywords = ['sơ mi', 'polo', 'khoác', 'đầm', 'giày', 'túi']
    const hasSpecificProduct = specificProductKeywords.some(keyword => messageLower.includes(keyword))
    
    return (hasProductKeyword && hasActionKeyword) || hasSpecificProduct
  }

  const filterProductsByQuery = (products, query) => {
    if (!products || products.length === 0) return []
    
    const queryLower = query.toLowerCase()
    const stopWords = ['tôi', 'muốn', 'mua', 'cần', 'tìm', 'có', 'ạ', 'à', 'và', 'hoặc', 'này', 'đó', 'kia', 'ôi', 'cho', 'với', 'trong', 'ngoài', 'trên', 'dưới', 'bên', 'của', 'là', 'thì', 'mà', 'nhưng', 'hoặc', 'vì', 'nên', 'để', 'từ', 'đến', 'tại', 'về', 'theo', 'cùng', 'cả', 'mỗi', 'mọi', 'mấy', 'bao', 'nhiêu', 'mấy', 'bao', 'nhiêu', 'mấy', 'bao', 'nhiêu']
    
    // Loại bỏ stop words và lấy từ khóa chính
    let keywords = queryLower.split(' ')
    keywords = keywords.filter(word => !stopWords.includes(word) && word.length >= 2)
    
    console.log('Filtering products for query:', queryLower)
    console.log('Keywords after filtering:', keywords)
    
    if (keywords.length === 0) return []
    
    // Định nghĩa các từ khóa sản phẩm chính
    const productKeywords = {
      'áo': ['áo', 'shirt', 'top', 'blouse'],
      'quần': ['quần', 'pants', 'trousers', 'jeans'],
      'váy': ['váy', 'dress', 'skirt'],
      'giày': ['giày', 'shoes', 'sneakers', 'boots'],
      'túi': ['túi', 'bag', 'handbag', 'backpack'],
      'đầm': ['đầm', 'dress', 'gown'],
      'sơ mi': ['sơ mi', 'shirt', 'formal'],
      'polo': ['polo', 'polo shirt'],
      'khoác': ['khoác', 'jacket', 'coat', 'blazer']
    }
    
    // Tìm từ khóa sản phẩm trong câu hỏi
    let foundProductType = null
    let foundSpecificType = null
    
    for (const [mainType, synonyms] of Object.entries(productKeywords)) {
      if (queryLower.includes(mainType)) {
        foundProductType = mainType
        break
      }
      for (const synonym of synonyms) {
        if (queryLower.includes(synonym)) {
          foundProductType = mainType
          break
        }
      }
      if (foundProductType) break
    }
    
    // Tìm từ khóa cụ thể
    if (queryLower.includes('sơ mi')) foundSpecificType = 'sơ mi'
    if (queryLower.includes('polo')) foundSpecificType = 'polo'
    if (queryLower.includes('khoác')) foundSpecificType = 'khoác'
    
    console.log('Found product type:', foundProductType)
    console.log('Found specific type:', foundSpecificType)
    
    // Lọc sản phẩm dựa trên từ khóa tìm được
    const result = products.filter(product => {
      const productName = (product.name || '').toLowerCase()
      const categoryName = (product.categories?.name || '').toLowerCase()
      const productDescription = (product.description || '').toLowerCase()
      
      // Nếu có từ khóa cụ thể, ưu tiên tìm kiếm chính xác
      if (foundSpecificType) {
        if (foundSpecificType === 'sơ mi') {
          return productName.includes('sơ mi') || categoryName.includes('sơ mi') || productDescription.includes('sơ mi')
        }
        if (foundSpecificType === 'polo') {
          return productName.includes('polo') || categoryName.includes('polo') || productDescription.includes('polo')
        }
        if (foundSpecificType === 'khoác') {
          return productName.includes('khoác') || categoryName.includes('khoác') || productDescription.includes('khoác')
        }
      }
      
      // Nếu có từ khóa sản phẩm chính
      if (foundProductType) {
        const synonyms = productKeywords[foundProductType]
        const hasMatchingType = synonyms.some(synonym => 
          productName.includes(synonym) || 
          categoryName.includes(synonym) || 
          productDescription.includes(synonym)
        )
        
        if (hasMatchingType) {
          // Kiểm tra thêm các từ khóa khác trong câu hỏi để tăng độ chính xác
          const otherKeywords = keywords.filter(keyword => 
            !synonyms.includes(keyword) && 
            !Object.values(productKeywords).flat().includes(keyword)
          )
          
          if (otherKeywords.length === 0) {
            return true // Chỉ có từ khóa sản phẩm, trả về tất cả sản phẩm loại đó
          }
          
          // Kiểm tra xem có từ khóa khác nào khớp không
          const hasOtherKeyword = otherKeywords.some(keyword => 
            productName.includes(keyword) || 
            categoryName.includes(keyword) || 
            productDescription.includes(keyword)
          )
          
          return hasOtherKeyword
        }
      }
      
      // Nếu không có từ khóa sản phẩm rõ ràng, sử dụng logic tìm kiếm chung
      // Nhưng yêu cầu ít nhất 2 từ khóa khớp để tăng độ chính xác
      const matchingKeywords = keywords.filter(keyword => 
        productName.includes(keyword) || 
        categoryName.includes(keyword) || 
        productDescription.includes(keyword)
      )
      
      return matchingKeywords.length >= 2
    })
    
    console.log('Filtered products count:', result.length)
    console.log('Filtered products:', result.map(p => p.name))
    
    // Giới hạn kết quả trả về để tránh spam
    return result.slice(0, 6)
  }

  const sendMessage = async (message) => {
    if (!message.trim() || isTyping.value) return

    // Kiểm tra tin nhắn quá ngắn hoặc spam
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
          aiMessage.text = 'Chào bạn! Rất vui được hỗ trợ bạn hôm nay. Bạn cần tìm gì ạ?\n\nTôi có thể giúp bạn:\n• Tìm kiếm sản phẩm cụ thể\n• Xem mã giảm giá và khuyến mãi\n• Thông tin flash sale\n• Hướng dẫn mua hàng'
          // Không hiển thị sản phẩm cho tin nhắn chào hỏi
          messages.value.push(aiMessage)
          return
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
              
              // Chỉ hiển thị sản phẩm nếu có kết quả lọc được và số lượng hợp lý
              if (filteredProducts.length > 0 && filteredProducts.length <= 6) {
                aiMessage.products = filteredProducts
              } else if (filteredProducts.length > 6) {
                // Nếu quá nhiều kết quả, chỉ hiển thị 6 sản phẩm đầu tiên
                aiMessage.products = filteredProducts.slice(0, 6)
              } else {
                // Nếu không tìm thấy sản phẩm phù hợp, thông báo cho người dùng
                aiMessage.text += '\n\nXin lỗi, tôi không tìm thấy sản phẩm phù hợp với yêu cầu của bạn. Bạn có thể thử tìm kiếm với từ khóa khác hoặc xem các sản phẩm liên quan.'
              }
              // Nếu không có kết quả lọc được, không hiển thị sản phẩm
            } else {
              // Nếu là câu hỏi chung, chỉ hiển thị sản phẩm nếu người dùng yêu cầu cụ thể
              const generalProductRequests = ['sản phẩm', 'hàng', 'đồ', 'quần áo', 'thời trang']
              const hasGeneralRequest = generalProductRequests.some(keyword => userMessageLower.includes(keyword))
              
              if (hasGeneralRequest) {
                // Chỉ hiển thị tối đa 3 sản phẩm cho câu hỏi chung
                aiMessage.products = data.context.products.slice(0, 3)
              }
            }
          }
          
          // Chỉ hiển thị coupon khi người dùng hỏi cụ thể
          if (data.context.coupons && data.context.coupons.length > 0) {
            const couponKeywords = ['mã giảm giá', 'coupon', 'khuyến mãi', 'giảm giá', 'discount']
            const hasCouponRequest = couponKeywords.some(keyword => message.toLowerCase().includes(keyword))
            
            if (hasCouponRequest) {
              aiMessage.coupons = data.context.coupons.slice(0, 3) // Giới hạn 3 coupon
            }
          }
          
          // Chỉ hiển thị flash sale khi người dùng hỏi cụ thể
          if (data.context.flash_sales && data.context.flash_sales.length > 0) {
            const flashSaleKeywords = ['flash sale', 'khuyến mãi', 'giảm giá', 'hot', 'nóng']
            const hasFlashSaleRequest = flashSaleKeywords.some(keyword => message.toLowerCase().includes(keyword))
            
            if (hasFlashSaleRequest) {
              aiMessage.flashSales = data.context.flash_sales.slice(0, 3) // Giới hạn 3 flash sale
            }
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
