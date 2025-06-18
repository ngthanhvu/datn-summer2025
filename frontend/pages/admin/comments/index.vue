<template>
    <div class="comments-page">
        <div class="page-header tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Đánh giá</h1>
                <p class="tw-text-gray-600">Quản lý đánh giá sản phẩm</p>
            </div>
            <div class="tw-flex tw-gap-3">
                <select v-model="filterCategory" class="tw-border tw-rounded tw-px-4 tw-py-2">
                    <option value="">Tất cả danh mục</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>
                <select v-model="filterBrand" class="tw-border tw-rounded tw-px-4 tw-py-2">
                    <option value="">Tất cả thương hiệu</option>
                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                        {{ brand.name }}
                    </option>
                </select>
                <button @click="fetchReviews" class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2">
                    <i class="fas fa-sync-alt tw-mr-2"></i>Làm mới
                </button>
            </div>
        </div>

        <div class="tw-bg-white tw-rounded-lg tw-shadow tw-mb-6">
            <div class="tw-p-4 tw-border-b tw-flex tw-items-center tw-gap-4">
                <button :class="['tw-font-semibold tw-pb-2', activeTab === 'reviews' ? 'tw-border-b-2 tw-border-primary tw-text-primary' : 'tw-text-gray-500']" @click="activeTab = 'reviews'">
                    Danh sách đánh giá <span v-if="filteredReviews.length > 0" class="tw-bg-primary tw-text-white tw-rounded-full tw-px-2 tw-ml-1 tw-text-xs">{{ filteredReviews.length }}</span>
                </button>
                <button :class="['tw-font-semibold tw-pb-2', activeTab === 'products' ? 'tw-border-b-2 tw-border-primary tw-text-primary' : 'tw-text-gray-500']" @click="activeTab = 'products'">
                    Sản phẩm đánh giá
                </button>
            </div>
        </div>

        <ProductReviewMenu v-if="activeTab === 'products'" />
        <CommentsList v-else :comments="filteredReviews"
            @update-status="handleUpdateStatus"
            @delete="handleDelete"
            @add-reply="handleAddReply"
            @update-reply="handleUpdateReply"
        />
    </div>
</template>

<script setup>
useHead({
    title: "Quản lý đánh giá",
    meta: [
        { name: "description", content: "Quản lý đánh giá sản phẩm của bạn" }
    ]
})
definePageMeta({
    layout: 'admin',
    middleware: 'admin'
})

import { ref, computed, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { useAdminReviews } from '~/composables/useAdminReviews'
import CommentsList from '~/components/admin/comments/CommentsList.vue'
import ProductReviewMenu from '~/components/admin/comments/ProductReviewMenu.vue'

const { getUser, getToken, user } = useAuth()

const { getAllReviews, updateReviewStatus, deleteReview, addAdminReply, getReviewsByCategory, getReviewsByBrand, updateAdminReply } = useAdminReviews()

const reviews = ref([])
const categories = ref([])
const brands = ref([])
const loading = ref(false)
const error = ref(null)
const filterCategory = ref('')
const filterBrand = ref('')
const activeTab = ref('reviews')

const fetchCategories = async () => {
    try {
        const response = await fetch(`${useRuntimeConfig().public.apiBaseUrl}/api/categories`)
        const data = await response.json()
        categories.value = data
    } catch (err) {
        console.error('Lỗi khi tải danh mục:', err)
    }
}

const fetchBrands = async () => {
    try {
        const response = await fetch(`${useRuntimeConfig().public.apiBaseUrl}/api/brands`)
        const data = await response.json()
        brands.value = data
    } catch (err) {
        console.error('Lỗi khi tải thương hiệu:', err)
    }
}

const fetchReviews = async () => {
    loading.value = true
    error.value = null
    try {
        let data
        if (filterCategory.value && filterBrand.value) {
            data = await getReviewsByCategory(filterCategory.value)
            data = data.filter(review => {
                return review.product && review.product.brand_id == filterBrand.value
            })
        } else if (filterCategory.value) {
            data = await getReviewsByCategory(filterCategory.value)
        } else if (filterBrand.value) {
            data = await getReviewsByBrand(filterBrand.value)
        } else {
            data = await getAllReviews()
        }
        
        reviews.value = await Promise.all(data.map(async (review) => {
            if (review.parent_id) return null
            
            let productInfo = null
            try {
                const productResponse = await fetch(`${useRuntimeConfig().public.apiBaseUrl}/api/products/slug/${review.product_slug}`)
                const productData = await productResponse.json()
                productInfo = {
                    id: productData.id,
                    name: productData.name,
                    image: productData.images && productData.images.length > 0 
                        ? `${useRuntimeConfig().public.apiBaseUrl}/storage/${productData.images[0].image_path}` 
                        : 'https://via.placeholder.com/150',
                    category_id: productData.category_id,
                    product_slug: review.product_slug
                }
            } catch (err) {
                console.error('Lỗi khi tải thông tin sản phẩm:', err)
                productInfo = {
                    id: null,
                    name: 'Sản phẩm không xác định',
                    image: 'https://via.placeholder.com/150',
                    category_id: null,
                    product_slug: review.product_slug
                }
            }
            
            const adminReply = review.replies?.find(reply => reply.is_admin_reply)
            
            return {
                id: review.id,
                userName: review.user?.username || 'Người dùng ẩn danh',
                userAvatar: review.user?.avatar || 'https://randomuser.me/api/portraits/men/1.jpg',
                rating: review.rating,
                content: review.content,
                date: new Date(review.created_at).toISOString().split('T')[0],
                status: review.is_approved ? 'approved' : (review.is_hidden ? 'rejected' : 'pending'),
                productInfo,
                reply: adminReply ? {
                    id: adminReply.id,  
                    content: adminReply.content,
                    date: new Date(adminReply.created_at).toISOString().split('T')[0]
                } : null,
                replyText: '',
                isApproved: review.is_approved,
                isHidden: review.is_hidden,
                isEditing: false,  
                editReplyText: '',  
                replies: review.replies,
                images: review.images || []
            }
        }))
        
        reviews.value = reviews.value.filter(review => review !== null)
    } catch (err) {
        console.error('Lỗi khi tải đánh giá:', err)
        error.value = 'Không thể tải dữ liệu đánh giá. Vui lòng thử lại sau.'
    } finally {
        loading.value = false
    }
}

const filteredReviews = computed(() => {
    if (!filterCategory.value) return reviews.value
    return reviews.value.filter(review => 
        review.productInfo && review.productInfo.category_id == filterCategory.value
    )
})

const handleUpdateStatus = async ({ id, status }) => {
    try {
        const review = reviews.value.find(r => r.id === id)
        if (!review) return
        
        await updateReviewStatus(id, status)
        
        review.status = status
        review.isApproved = status === 'approved'
        review.isHidden = status === 'rejected'
    } catch (err) {
        console.error('Lỗi khi cập nhật trạng thái:', err)
        alert('Không thể cập nhật trạng thái đánh giá. Vui lòng thử lại.')
    }
}

const handleDelete = async (id) => {
    if (!confirm('Bạn có chắc chắn muốn xóa đánh giá này?')) return
    
    try {
        await deleteReview(id)
        
        const index = reviews.value.findIndex(r => r.id === id)
        if (index !== -1) {
            reviews.value.splice(index, 1)
        }
    } catch (err) {
        console.error('Lỗi khi xóa đánh giá:', err)
        alert('Không thể xóa đánh giá. Vui lòng thử lại.')
    }
}

const handleAddReply = async ({ id, content }) => {
    try {
        const review = reviews.value.find(r => r.id === id)
        if (!review) {
            console.error('Không tìm thấy review với id:', id)
            alert('Không tìm thấy đánh giá. Vui lòng làm mới trang.')
            return
        }
        
        if (!review.productInfo) {
            console.error('Thiếu thông tin sản phẩm cho review:', id)
            alert('Thiếu thông tin sản phẩm. Vui lòng làm mới trang.')
            return
        }
        
        const productSlug = review.product_slug || (review.productInfo && review.productInfo.product_slug)
        
        const replyData = {
            content: content,
            user_id: user.value.id,
            product_slug: productSlug,
            is_admin_reply: true,
            is_approved: true,
            is_hidden: false
        }
        
        console.log('Gửi request với dữ liệu:', replyData)
        
        const response = await addAdminReply(review.id, replyData)
        
        if (response) {
            review.reply = response
            const commentIndex = reviews.value.findIndex(r => r.id === id)
            if (commentIndex !== -1) {
                reviews.value[commentIndex].replyText = ''
            }
        }
    } catch (err) {
        console.error('Lỗi khi thêm phản hồi:', err)
        alert('Không thể thêm phản hồi. Vui lòng thử lại.')
    }
}

async function handleUpdateReply({ id, content }) {
    try {
        const review = reviews.value.find(r => r.id === id)
        if (!review) {
            console.error('Không tìm thấy review với id:', id)
            alert('Không thể cập nhật phản hồi. Review không tồn tại.')
            return
        }
        
        let adminReply = review.reply && review.reply.is_admin_reply ? review.reply : null
        
        if (!adminReply && review.replies && review.replies.length > 0) {
            adminReply = review.replies.find(r => r.is_admin_reply)
        }
        
        if (!adminReply) {
            console.error('Không tìm thấy admin reply cho review:', id)
            alert('Không thể cập nhật phản hồi. Admin reply không tồn tại.')
            return
        }
        
        if (!review.productInfo || !review.productInfo.product_slug) {
            console.error('Review không có product_slug:', review)
            alert('Không thể cập nhật phản hồi. Thiếu thông tin sản phẩm.')
            return
        }
        
        await getUser()
        if (!user.value || !user.value.id) {
            console.error('Không có thông tin user')
            alert('Không thể cập nhật phản hồi. Vui lòng đăng nhập lại.')
            return
        }
        
        console.log('Gửi request với dữ liệu:', {
            replyId: adminReply.id,
            content
        })
        
        const response = await updateAdminReply(adminReply.id, content)
        
        if (response) {
            if (review.reply) {
                review.reply.content = content
            }
            
            if (review.replies) {
                const replyIndex = review.replies.findIndex(r => r.is_admin_reply)
                if (replyIndex !== -1) {
                    review.replies[replyIndex].content = content
                }
            }
        }
    } catch (err) {
        console.error('Lỗi khi cập nhật phản hồi:', err)
        alert('Không thể cập nhật phản hồi. Vui lòng thử lại.')
    }
}

onMounted(() => {
    fetchCategories()
    fetchBrands()
    fetchReviews()
})
</script>

<style scoped>
.comments-page {
    padding: 1.5rem;
}

.tw-bg-primary {
    background-color: #3bb77e;
}
</style>