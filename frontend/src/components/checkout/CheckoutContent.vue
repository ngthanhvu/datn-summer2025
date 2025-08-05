<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import AddressList from './AddressList.vue'
import AddressForm from './AddressForm.vue'
import PaymentMethods from './PaymentMethods.vue'
import OrderSummary from './OrderSummary.vue'

import { useAddress } from '../../composable/useAddress'
import { useCart } from '../../composable/useCart'
import { useCheckout } from '../../composable/useCheckout'
import { useCoupon } from '../../composable/useCoupon'
import { usePayment } from '../../composable/usePayment'
import { useRouter } from 'vue-router'
import { useAuth } from '../../composable/useAuth'

const router = useRouter()

const addressService = useAddress()
const cartService = useCart()
const checkoutService = useCheckout()
const couponService = useCoupon()
const paymentService = usePayment()
const authService = useAuth()

const showAddressForm = ref(false)
const editingAddressIndex = ref(null)
const selectedAddress = ref(0)
const addresses = ref([])
const isLoading = ref(false)
const error = ref(null)

const cartItems = ref([])
const shipping = ref(0)
const discount = ref(0)
const appliedCoupon = ref(null)
const shippingZone = ref('')

const subtotal = computed(() => {
    return cartItems.value.reduce((total, item) => {
        return total + (item.price * item.quantity)
    }, 0)
})

const total = computed(() => {
    return Math.round(subtotal.value + shipping.value - discount.value)
})

const editingAddress = computed(() => {
    if (editingAddressIndex.value === null) return null
    return addresses.value[editingAddressIndex.value]
})

const openAddressModal = (index = null) => {
    editingAddressIndex.value = index
    showAddressForm.value = true
}

const closeAddressModal = () => {
    showAddressForm.value = false
    editingAddressIndex.value = null
}

const saveAddress = async (address) => {
    try {
        isLoading.value = true
        if (editingAddressIndex.value === null) {
            const newAddress = await addressService.createAddress({
                full_name: address.fullName,
                phone: address.phone,
                province: address.province,
                district: address.district,
                ward: address.ward,
                street: address.detail,
                hamlet: address.hamlet,
                note: address.note
            })
            await fetchAddresses()
        } else {
            const addressId = addresses.value[editingAddressIndex.value].id
            await addressService.updateAddress(addressId, {
                full_name: address.fullName,
                phone: address.phone,
                province: address.province,
                district: address.district,
                ward: address.ward,
                street: address.detail,
                hamlet: address.hamlet,
                note: address.note
            })
            await fetchAddresses()
        }
    } catch (err) {
        error.value = err.message || 'Có lỗi xảy ra khi lưu địa chỉ'
    } finally {
        isLoading.value = false
        closeAddressModal()
    }
}

const deleteAddress = async (index) => {
    try {
        const addressId = addresses.value[index].id
        await addressService.deleteAddress(addressId)

        await fetchAddresses()

        if (selectedAddress.value === index) {
            selectedAddress.value = 0
        }
    } catch (err) {
        error.value = err.message || 'Có lỗi xảy ra khi xóa địa chỉ'
    }
}

const fetchAddresses = async () => {
    try {
        isLoading.value = true
        const response = await addressService.getMyAddress()

        if (Array.isArray(response)) {
            addresses.value = response.map(addr => ({
                id: addr.id,
                fullName: addr.full_name,
                phone: addr.phone,
                province: addr.province,
                district: addr.district,
                ward: addr.ward,
                hamlet: addr.hamlet || '',
                detail: addr.street,
                note: addr.note || '',
                fullAddress: addressService.getFullAddress(addr)
            }))
        } else {
            addresses.value = []
        }
    } catch (err) {
        error.value = err.message || 'Có lỗi xảy ra khi lấy danh sách địa chỉ'
    } finally {
        isLoading.value = false
    }
}

const fetchCart = async () => {
    try {
        isLoading.value = true
        const cart = await cartService.fetchCart()
        cartItems.value = cart.map(item => ({
            id: item.id,
            name: item.variant?.product?.name || 'Sản phẩm',
            variant: `Size: ${item.variant?.size || 'N/A'} | Số lượng: ${item.quantity}`,
            price: item.price || 0, // Lấy giá đã lưu trong DB
            quantity: item.quantity,
            image: item.variant?.product?.main_image?.image_path || 'https://placehold.co/100x100'
        }))
    } catch (err) {
        error.value = err.message || 'Có lỗi xảy ra khi lấy giỏ hàng'
    } finally {
        isLoading.value = false
    }
}

const applyCoupon = async (code) => {
    try {
        isLoading.value = true
        const result = await couponService.validateCoupon(code, subtotal.value)

        if (result.discount !== undefined) {
            appliedCoupon.value = result.coupon
            discount.value = Math.round(result.discount)
            error.value = null // Clear any previous errors
        } else {
            error.value = 'Mã giảm giá không hợp lệ'
        }
    } catch (err) {
        error.value = err.message || 'Mã giảm giá không hợp lệ'
        discount.value = 0
        appliedCoupon.value = null
    } finally {
        isLoading.value = false
    }
}

const selectedPaymentMethod = ref(0)
const paymentMethods = ref([])

const updatePaymentMethods = () => {
    const s = settings.value || {}

    paymentMethods.value = [
        {
            title: 'Thanh toán khi nhận hàng (COD)',
            description: Number(s.enableCod) ? 'Thanh toán bằng tiền mặt khi nhận hàng' : 'Sắp ra mắt',
            code: 'cod',
            image: 'https://cdn-icons-png.flaticon.com/512/2897/2897832.png',
            enabled: !!Number(s.enableCod)
        },
        {
            title: 'VNPay',
            description: Number(s.enableVnpay) ? 'Thanh toán qua cổng thanh toán VNPay' : 'Sắp ra mắt',
            code: 'vnpay',
            image: 'https://vinadesign.vn/uploads/images/2023/05/vnpay-logo-vinadesign-25-12-57-55.jpg',
            enabled: !!Number(s.enableVnpay)
        },
        {
            title: 'Momo',
            description: Number(s.enableMomo) ? 'Thanh toán qua ví điện tử Momo' : 'Sắp ra mắt',
            code: 'momo',
            image: 'https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png',
            enabled: !!Number(s.enableMomo)
        },
    ]
}


const handleShippingCalculated = (shippingData) => {
    shipping.value = shippingData.shippingFee?.total || 0;
    shippingZone.value = shippingData.zone || '';
};

watch(() => selectedAddress.value, (newValue) => {
    if (newValue === null || newValue >= addresses.value.length) {
        shipping.value = 0;
        shippingZone.value = '';
    }
});

const placeOrder = async () => {
    try {
        if (addresses.value.length === 0) {
            error.value = 'Vui lòng thêm địa chỉ giao hàng'
            return
        }

        // Kiểm tra user authentication
        if (!authService.isAuthenticated.value) {
            error.value = 'Vui lòng đăng nhập để đặt hàng'
            return
        }

        // Đảm bảo user đã được load
        if (!authService.user.value) {
            await authService.getUser()
        }

        if (!authService.user.value?.id) {
            error.value = 'Không thể xác định thông tin người dùng'
            return
        }

        isLoading.value = true

        const cart = await cartService.fetchCart()

        const items = cart.map(item => ({
            variant_id: item.variant.id,
            quantity: item.quantity,
            price: item.price
        }))

        const orderData = {
            address_id: addresses.value[selectedAddress.value].id,
            payment_method: paymentMethods.value[selectedPaymentMethod.value]?.code || 'cod',
            coupon_id: appliedCoupon.value?.id || null,
            items: items,
            note: '',
            total_price: subtotal.value,
            shipping_fee: shipping.value,
            discount_price: discount.value,
            final_price: total.value,
            user_id: authService.user.value.id, 
            shipping_zone: shippingZone.value
        }

        const result = await checkoutService.createOrder(orderData)

        // Handle new backend logic for online payment
        if (result && result.message === 'Redirect to payment gateway') {
            const paymentMethod = result.payment_method
            let paymentUrl
            let paymentResult
            if (paymentMethod === 'vnpay') {
                paymentResult = await paymentService.generateVnpayUrl(result.data)
                paymentUrl = paymentResult.payment_url
            } else if (paymentMethod === 'momo') {
                paymentResult = await paymentService.generateMomoUrl(result.data)
                paymentUrl = paymentResult.payment_url
            }
            if (paymentUrl) {
                window.location.href = paymentUrl
                return
            } else {
                throw new Error('Không thể tạo URL thanh toán')
            }
        }

        // COD logic (order created immediately)
        if (result && result.order) {
            // Redirect sang trang trạng thái đơn hàng
            router.push(`/status?status=success&orderId=${result.order.id}&amount=${result.order.final_price}&tracking_code=${result.order.tracking_code || ''}`)
            return
        } else {
            throw new Error('Không thể tạo đơn hàng')
        }
    } catch (err) {
        error.value = err.message || 'Có lỗi xảy ra khi đặt hàng'
    } finally {
        isLoading.value = false
    }
}

import useSettings from '../../composable/useSettingsApi'
const { settings, fetchSettings } = useSettings()

onMounted(async () => {
    try {
        isLoading.value = true

        // Kiểm tra authentication trước
        const isAuthenticated = await authService.checkAuth()
        if (!isAuthenticated) {
            router.push('/login?redirect=' + encodeURIComponent(router.currentRoute.value.fullPath))
            return
        }

        await Promise.all([
            fetchSettings(),
            fetchAddresses(),
            fetchCart()
        ])
        updatePaymentMethods()
    } catch (err) {
        error.value = err.message || 'Có lỗi xảy ra khi tải dữ liệu'
    } finally {
        isLoading.value = false
    }
})
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-8">
        <h1 class="text-2xl font-bold mb-8">Thanh toán</h1>

        <!-- Skeleton Loading -->
        <div v-if="isLoading" class="grid grid-cols-1 lg:grid-cols-2 gap-8 animate-pulse">
            <!-- Cột trái -->
            <div class="space-y-8">
                <div class="bg-white p-6 rounded-lg shadow-sm space-y-6">
                    <!-- Skeleton địa chỉ giao hàng -->
                    <div>
                        <div class="h-5 bg-gray-200 rounded w-1/3 mb-4"></div>
                        <div class="space-y-4">
                            <div class="border border-gray-300 rounded-lg p-4">
                                <div class="h-4 bg-gray-200 rounded w-1/4 mb-2"></div>
                                <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                            </div>
                            <div class="border border-gray-300 rounded-lg p-4">
                                <div class="h-4 bg-gray-200 rounded w-1/4 mb-2"></div>
                                <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                            </div>
                            <div class="h-10 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    </div>

                    <!-- Skeleton phương thức thanh toán -->
                    <div>
                        <div class="h-5 bg-gray-200 rounded w-1/3 mb-4"></div>
                        <div class="space-y-3">
                            <div class="h-10 bg-gray-200 rounded"></div>
                            <div class="h-10 bg-gray-200 rounded"></div>
                            <div class="h-10 bg-gray-200 rounded"></div>
                            <div class="h-10 bg-gray-200 rounded"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải -->
            <div class="space-y-8">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="h-5 bg-gray-200 rounded w-1/3 mb-6"></div>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="h-20 w-20 bg-gray-200 rounded"></div>
                        <div class="flex-1 space-y-2">
                            <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    </div>

                    <div class="h-10 bg-gray-200 rounded mb-4"></div>

                    <div class="space-y-3 mb-6">
                        <div class="h-4 bg-gray-200 rounded w-full"></div>
                        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                        <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                    </div>

                    <div class="h-10 bg-gray-300 rounded w-full"></div>
                </div>
            </div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="bg-red-50 p-4 rounded-md text-red-600 mb-6">
            {{ error }}
        </div>

        <!-- Authentication Error -->
        <div v-else-if="!authService.isAuthenticated.value" class="bg-yellow-50 p-4 rounded-md text-yellow-600 mb-6">
            <p>Vui lòng <router-link to="/login" class="underline font-medium">đăng nhập</router-link> để tiếp tục thanh
                toán.</p>
        </div>

        <!-- Empty Cart -->
        <div v-else-if="cartItems.length === 0" class="bg-blue-50 p-4 rounded-md text-blue-600 mb-6">
            <p>Giỏ hàng trống. <router-link to="/products" class="underline font-medium">Tiếp tục mua sắm</router-link>
            </p>
        </div>

        <!-- No Address -->
        <div v-else-if="addresses.length === 0" class="bg-orange-50 p-4 rounded-md text-orange-600 mb-6">
            <p>Vui lòng thêm địa chỉ giao hàng để tiếp tục thanh toán.</p>
        </div>

        <!-- Nội dung thực -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-8">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <AddressList :addresses="addresses" :selected-address="selectedAddress"
                        @select="selectedAddress = $event" @edit="openAddressModal" @delete="deleteAddress"
                        @add="openAddressModal" />
                    <!-- ĐÃ DI CHUYỂN ShippingSection SANG CỘT PHẢI -->
                    <PaymentMethods :methods="paymentMethods" :selected-method="selectedPaymentMethod"
                        @select="selectedPaymentMethod = $event" />
                </div>
            </div>

            <div class="space-y-8">
                <OrderSummary 
                    :items="cartItems" 
                    :subtotal="subtotal" 
                    :shipping="shipping" 
                    :discount="discount" 
                    :shipping-zone="shippingZone"
                    :selected-address="addresses.length > 0 && selectedAddress >= 0 && selectedAddress < addresses.length ? addresses[selectedAddress] : null"
                    :cart-items="cartItems"
                    @place-order="placeOrder" 
                    @apply-coupon="applyCoupon"
                    @shipping-calculated="handleShippingCalculated" 
                />
            </div>
        </div>

        <AddressForm :show="showAddressForm" :editing-index="editingAddressIndex" :address="editingAddress"
            @close="closeAddressModal" @save="saveAddress" />
    </div>
</template>



<style scoped>
/* Add any component-specific styles here */
</style>