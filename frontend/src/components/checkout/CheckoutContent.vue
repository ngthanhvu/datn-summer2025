<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { push } from 'notivue'
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
const selectedAddress = ref(null)
const addresses = ref([])
const isLoading = ref(false)
const isAddressLoading = ref(false) // Separate loading state for address operations
const error = ref(null)
const isPlacingOrder = ref(false)


const cartItems = ref([])
const shipping = ref(0)
const isFreeShipping = ref(false)
const shippingZone = ref('')
const shippingLoading = ref(false)
const shippingCalculated = ref(false)
const discount = ref(0)
const appliedDiscountCoupon = ref(null)
const appliedShippingCoupon = ref(null)
const appliedCoupon = ref(null)
const orderSummaryRef = ref(null)
const isCouponLoading = ref(false) // Separate loading state for coupon

const subtotal = computed(() => {
    return cartItems.value.reduce((total, item) => {
        return total + (item.price * item.quantity)
    }, 0)
})

const shippingForTotal = computed(() => {
    // If free shipping is applied, return 0
    if (isFreeShipping.value) return 0
    // Otherwise return the calculated shipping fee
    return shipping.value
})

const total = computed(() => {
    return Math.round(subtotal.value + shippingForTotal.value - discount.value)
})

const editingAddress = computed(() => {
    if (editingAddressIndex.value === null) return null
    return addresses.value[editingAddressIndex.value]
})

const currentSelectedAddress = computed(() => {
    if (addresses.value.length === 0 || selectedAddress.value === null) return null
    if (selectedAddress.value >= 0 && selectedAddress.value < addresses.value.length) {
        return addresses.value[selectedAddress.value]
    }
    return null
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
        isAddressLoading.value = true
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

            // Add new address to local array instead of fetching all addresses
            const addressToAdd = {
                id: newAddress.id,
                fullName: address.fullName,
                phone: address.phone,
                province: address.province,
                district: address.district,
                ward: address.ward,
                hamlet: address.hamlet || '',
                detail: address.detail,
                note: address.note || '',
                fullAddress: address.fullAddress,
                district_id: newAddress.district_id || address.district,
                ward_code: newAddress.ward_code || address.ward
            }

            addresses.value.push(addressToAdd)
            // Select the newly added address
            selectedAddress.value = addresses.value.length - 1
            await nextTick()
            if (orderSummaryRef.value) {
                orderSummaryRef.value.forceShippingCalculation()
            }
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

            // Update address in local array instead of fetching all addresses
            addresses.value[editingAddressIndex.value] = {
                ...addresses.value[editingAddressIndex.value],
                fullName: address.fullName,
                phone: address.phone,
                province: address.province,
                district: address.district,
                ward: address.ward,
                hamlet: address.hamlet || '',
                detail: address.detail,
                note: address.note || '',
                fullAddress: address.fullAddress
            }
        }
    } catch (err) {
        error.value = err.message || 'Có lỗi xảy ra khi lưu địa chỉ'
    } finally {
        isAddressLoading.value = false
        closeAddressModal()
    }
}

const deleteAddress = async (index) => {
    try {
        isAddressLoading.value = true
        const addressId = addresses.value[index].id
        await addressService.deleteAddress(addressId)

        // Remove address from local array
        addresses.value.splice(index, 1)

        // Update selectedAddress index after deletion
        if (selectedAddress.value === index) {
            if (addresses.value.length > 0) {
                selectedAddress.value = 0
            } else {
                selectedAddress.value = null
            }
        } else if (selectedAddress.value > index) {
            // If selected address is after the deleted one, adjust the index
            selectedAddress.value--
        }
    } catch (err) {
        error.value = err.message || 'Có lỗi xảy ra khi xóa địa chỉ'
    } finally {
        isAddressLoading.value = false
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
                fullAddress: addressService.getFullAddress(addr),
                district_id: addr.district_id || addr.district,
                ward_code: addr.ward_code || addr.ward
            }))
            if (selectedAddress.value === null && addresses.value.length > 0) {
                selectedAddress.value = 0
                await nextTick()
                if (orderSummaryRef.value) {
                    orderSummaryRef.value.forceShippingCalculation()
                }
            }
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
        isCouponLoading.value = true
        const result = await couponService.validateCoupon(code, subtotal.value)

        if (result.free_shipping) {
            // Apply/replace shipping coupon
            appliedShippingCoupon.value = result.coupon
            isFreeShipping.value = true
            shipping.value = 0 // Set shipping fee to 0
            shippingCalculated.value = true // Mark as calculated since we don't need to calculate
            error.value = null
            push.success('Áp dụng mã freeship thành công')

            // Trigger shipping calculation to get zone info even though fee is 0
            if (orderSummaryRef.value) {
                orderSummaryRef.value.handleFreeShippingApplied(true)
            }
        } else if (result.discount !== undefined) {
            // Apply/replace percentage/fixed coupon
            appliedDiscountCoupon.value = result.coupon
            appliedCoupon.value = result.coupon
            discount.value = Math.round(result.discount)
            error.value = null
            push.success('Áp dụng mã giảm giá thành công')
        } else {
            error.value = 'Mã giảm giá không hợp lệ'
        }
    } catch (err) {
        error.value = err.message || 'Mã giảm giá không hợp lệ'
        push.error(error.value)
        discount.value = 0
        appliedDiscountCoupon.value = null
        appliedShippingCoupon.value = null
        appliedCoupon.value = null
        isFreeShipping.value = false
    } finally {
        isCouponLoading.value = false
    }
}

// Apply list of codes (e.g., from modal) in order. Supports 1 shipping + 1 discount
const applyMultipleCoupons = async (codes) => {
    if (!Array.isArray(codes)) return

    isCouponLoading.value = true

    // Reset previous coupons first
    appliedShippingCoupon.value = null
    appliedDiscountCoupon.value = null
    appliedCoupon.value = null
    isFreeShipping.value = false
    discount.value = 0
    shipping.value = 0 // Reset shipping fee
    shippingCalculated.value = false // Reset shipping calculation status

    try {
        // Apply coupons in order: shipping first, then discount
        for (const code of codes) {
            try {
                await applyCoupon(code)
            } catch (_e) {
                // Continue with next coupon if one fails
            }
        }
    } finally {
        isCouponLoading.value = false
    }
}

// Add function to remove coupons
const removeCoupon = (type) => {
    if (type === 'shipping') {
        appliedShippingCoupon.value = null
        isFreeShipping.value = false
        // Reset shipping calculation if no address is selected
        if (selectedAddress.value === null || selectedAddress.value >= addresses.value.length) {
            shipping.value = 0
            shippingZone.value = ''
            shippingCalculated.value = false
        } else {
            // If address is selected, recalculate shipping
            shippingCalculated.value = false
            shipping.value = 0 // Reset shipping fee to force recalculation
            if (orderSummaryRef.value) {
                orderSummaryRef.value.forceShippingCalculation()
            }
        }
    } else if (type === 'discount') {
        appliedDiscountCoupon.value = null
        appliedCoupon.value = null
        discount.value = 0
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
    if (shippingData.loading !== undefined) {
        shippingLoading.value = shippingData.loading;
    }

    if (shippingData.shippingFee) {
        // Only update shipping fee if free shipping is not applied
        if (!isFreeShipping.value) {
            shipping.value = shippingData.shippingFee?.total || 0;
        }
        shippingZone.value = shippingData.zone || '';
        // Only set shippingCalculated to true if we're not already in free shipping mode
        if (!isFreeShipping.value) {
            shippingCalculated.value = true;
        }
    }
};

watch(() => selectedAddress.value, (newValue) => {
    if (newValue === null || newValue >= addresses.value.length) {
        // Only reset shipping if not in free shipping mode
        if (!isFreeShipping.value) {
            shipping.value = 0;
            shippingZone.value = '';
            shippingLoading.value = false;
            shippingCalculated.value = false;
        }
    }
});

watch(() => currentSelectedAddress.value, (newAddress) => {
    if (!newAddress) {
        // Only reset shipping if not in free shipping mode
        if (!isFreeShipping.value) {
            shippingLoading.value = false;
            shippingCalculated.value = false;
        }
    }
});

// Show all error messages via Notivue
watch(error, (message) => {
    if (message) {
        push.error(String(message))
    }
})

const placeOrder = async () => {
    try {
        if (addresses.value.length === 0) {
            error.value = 'Vui lòng thêm địa chỉ giao hàng'
            push.warning(error.value)
            return
        }

        if (!authService.isAuthenticated.value) {
            error.value = 'Vui lòng đăng nhập để đặt hàng'
            push.warning(error.value)
            return
        }

        if (!authService.user.value) {
            await authService.getUser()
        }

        if (!authService.user.value?.id) {
            error.value = 'Không thể xác định thông tin người dùng'
            push.error(error.value)
            return
        }

        isPlacingOrder.value = true

        const cart = await cartService.fetchCart()

        const items = cart.map(item => ({
            variant_id: item.variant.id,
            quantity: item.quantity,
            price: item.price
        }))

        // Calculate final shipping fee considering free shipping
        let finalShippingFee = shipping.value
        let finalShippingZone = shippingZone.value

        if (isFreeShipping.value) {
            finalShippingFee = 0
            // Keep the shipping zone for display purposes
        }

        const orderData = {
            address_id: addresses.value[selectedAddress.value].id,
            payment_method: paymentMethods.value[selectedPaymentMethod.value]?.code || 'cod',
            // Prioritize shipping coupon ID when free shipping is applied, otherwise use discount coupon
            coupon_id: isFreeShipping.value && appliedShippingCoupon.value?.id
                ? appliedShippingCoupon.value.id
                : (appliedDiscountCoupon.value?.id || appliedShippingCoupon.value?.id || null),
            items: items,
            note: '',
            total_price: subtotal.value,
            shipping_fee: finalShippingFee,
            discount_price: discount.value,
            final_price: total.value,
            user_id: authService.user.value.id,
            shipping_zone: finalShippingZone
        }

        // Check if shipping calculation is needed (only if not free shipping)
        // If free shipping is applied, we don't need to calculate shipping fee
        if (!isFreeShipping.value && (shippingLoading.value || !shippingCalculated.value)) {
            error.value = 'Vui lòng tính phí vận chuyển trước khi thanh toán';
            push.warning(error.value)
            if (orderSummaryRef.value) {
                orderSummaryRef.value.forceShippingCalculation();
            }
            isPlacingOrder.value = false
            return
        }

        // Additional validation: if no free shipping and shipping fee is 0, require calculation
        if (!isFreeShipping.value && finalShippingFee <= 0) {
            error.value = 'Vui lòng tính phí vận chuyển trước khi thanh toán';
            push.warning(error.value)
            if (orderSummaryRef.value) {
                orderSummaryRef.value.forceShippingCalculation();
            }
            isPlacingOrder.value = false
            return
        }

        // Final validation: ensure we have either free shipping OR calculated shipping
        if (!isFreeShipping.value && !shippingCalculated.value) {
            error.value = 'Vui lòng tính phí vận chuyển trước khi thanh toán';
            push.warning(error.value)
            if (orderSummaryRef.value) {
                orderSummaryRef.value.forceShippingCalculation();
            }
            isPlacingOrder.value = false
            return
        }

        const result = await checkoutService.createOrder(orderData)

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

        if (result && result.order) {
            push.success('Đặt hàng thành công')
            router.push(`/status?status=success&orderId=${result.order.id}&amount=${result.order.final_price}&tracking_code=${result.order.tracking_code || ''}`)
            return
        } else {
            throw new Error('Không thể tạo đơn hàng')
        }
    } catch (err) {
        error.value = err.message || 'Có lỗi xảy ra khi đặt hàng'
        push.error(error.value)
    } finally {
        isPlacingOrder.value = false
    }
}

import useSettings from '../../composable/useSettingsApi'
const { settings, fetchSettings } = useSettings()

onMounted(async () => {
    try {
        isLoading.value = true

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

        <div v-if="isLoading" class="flex flex-col items-center justify-center py-16">
            <div class="mb-4">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-gray-200 border-t-[#81AACC]"></div>
            </div>
            <p class="text-gray-600 text-lg">Đang tải trang thanh toán...</p>
        </div>

        <!-- Authentication Error -->
        <div v-else-if="!authService.isAuthenticated.value" class="bg-yellow-50 p-4 rounded-md text-yellow-600 mb-6">
            <p>Vui lòng <router-link to="/login" class="underline font-medium">đăng nhập</router-link> để tiếp tục thanh
                toán.</p>
        </div>

        <!-- Empty Cart -->
        <div v-else-if="cartItems.length === 0"
            class="p-6 rounded-md text-gray-600 mb-6 flex flex-col items-center justify-center text-center">
            <i class="fa-solid fa-cart-shopping text-4xl mb-3"></i>
            <p class="mb-2">Giỏ hàng đang trống, tiếp tục mua hàng để thanh toán !!</p>
            <router-link to="/san-pham"
                class="px-4 py-2 bg-[#81AACC] text-white rounded-md hover:bg-[#6387A6] cursor-pointer transition">
                Tiếp tục mua sắm
            </router-link>
        </div>


        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-8">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="mb-6">
                        <div v-if="addresses.length > 0">
                            <AddressList :addresses="addresses" :selected-address="selectedAddress"
                                :is-loading="isAddressLoading" @select="selectedAddress = $event"
                                @edit="openAddressModal" @delete="deleteAddress" @add="openAddressModal" />
                        </div>

                        <div v-else class="text-center">
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                                <p class="text-gray-500 mb-4">Bạn chưa có địa chỉ giao hàng</p>
                                <button @click="openAddressModal()"
                                    class="bg-[#81AACC] text-white px-4 py-2 rounded-lg hover:bg-[#81AACC]/80 transition-colors">
                                    + Thêm địa chỉ mới
                                </button>
                            </div>
                        </div>
                    </div>

                    <PaymentMethods :methods="paymentMethods" :selected-method="selectedPaymentMethod"
                        @select="selectedPaymentMethod = $event" />
                </div>
            </div>

            <div class="space-y-8">
                <OrderSummary ref="orderSummaryRef" :items="cartItems" :subtotal="subtotal" :shipping="shippingForTotal"
                    :free-shipping="isFreeShipping" :applied-shipping-coupon="appliedShippingCoupon"
                    :applied-discount-coupon="appliedDiscountCoupon" :discount="discount" :shipping-zone="shippingZone"
                    :shipping-loading="shippingLoading" :selected-address="currentSelectedAddress"
                    :cart-items="cartItems" :is-placing-order="isPlacingOrder" :is-coupon-loading="isCouponLoading"
                    @place-order="placeOrder" @apply-coupon="applyCoupon" @apply-coupons="applyMultipleCoupons"
                    @shipping-calculated="handleShippingCalculated" @remove-coupon="removeCoupon" />
            </div>
        </div>

        <AddressForm :show="showAddressForm" :editing-index="editingAddressIndex" :address="editingAddress"
            :is-loading="isAddressLoading" @close="closeAddressModal" @save="saveAddress"
            @loading-change="(loading) => isAddressLoading = loading" />
    </div>
</template>



<style scoped>
/* Add any component-specific styles here */
</style>