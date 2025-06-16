<script setup>
import { ref, computed, onMounted } from 'vue'
import AddressList from '~/components/checkout/AddressList.vue'
import AddressForm from '~/components/checkout/AddressForm.vue'
import PaymentMethods from '~/components/checkout/PaymentMethods.vue'
import OrderSummary from '~/components/checkout/OrderSummary.vue'
import { useAddress } from '~/composables/useAddress'
import { useCart } from '~/composables/useCarts'
import { useCheckout } from '~/composables/useCheckout'
import { useCoupon } from '~/composables/useCoupon'
import { usePayment } from '~/composables/usePayment'

const addressService = useAddress()
const cartService = useCart()
const checkoutService = useCheckout()
const couponService = useCoupon()
const paymentService = usePayment()

const showAddressForm = ref(false)
const editingAddressIndex = ref(null)
const selectedAddress = ref(0)
const addresses = ref([])
const isLoading = ref(false)
const error = ref(null)

const cartItems = ref([])
const shipping = ref(30000)
const discount = ref(0)
const appliedCoupon = ref(null)

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
        const response = await addressService.getAddresses()

        if (response && response.data && Array.isArray(response.data)) {
            addresses.value = response.data.map(addr => ({
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
            console.error('API không trả về mảng địa chỉ:', response)
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
            price: item.variant?.price || 0,
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
const paymentMethods = [
    {
        title: 'Thanh toán khi nhận hàng (COD)',
        description: 'Thanh toán bằng tiền mặt khi nhận hàng',
        code: 'cod',
        image: 'https://cdn-icons-png.flaticon.com/512/2897/2897832.png',
        img: 'https://cdn-icons-png.flaticon.com/512/2897/2897832.png'
    },
    {
        title: 'VNPay',
        description: 'Thanh toán qua cổng thanh toán VNPay',
        code: 'vnpay',
        image: 'https://vinadesign.vn/uploads/images/2023/05/vnpay-logo-vinadesign-25-12-57-55.jpg',
        img: 'https://vinadesign.vn/uploads/images/2023/05/vnpay-logo-vinadesign-25-12-57-55.jpg'
    },
    {
        title: 'Momo',
        description: 'Thanh toán qua ví điện tử Momo',
        code: 'momo',
        image: 'https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png',
        img: 'https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png'
    },
    {
        title: 'PayPal',
        description: 'Thanh toán qua PayPal',
        code: 'paypal',
        image: 'https://rgb.vn/wp-content/uploads/2014/05/rgb_vn_new_branding_paypal_2014_logo_detail.png',
        img: 'https://rgb.vn/wp-content/uploads/2014/05/rgb_vn_new_branding_paypal_2014_logo_detail.png'
    }
]

const placeOrder = async () => {
    try {
        if (addresses.value.length === 0) {
            error.value = 'Vui lòng thêm địa chỉ giao hàng'
            return
        }

        isLoading.value = true

        const cart = await cartService.fetchCart()

        const items = cart.map(item => ({
            variant_id: item.variant.id,
            quantity: item.quantity,
            price: item.variant.price || 0
        }))

        const orderData = {
            address_id: addresses.value[selectedAddress.value].id,
            payment_method: paymentMethods[selectedPaymentMethod.value].code,
            coupon_id: appliedCoupon.value?.id || null,
            items: items,
            note: '',
            total_price: subtotal.value,
            shipping_fee: shipping.value,
            discount_price: discount.value,
            final_price: total.value
        }

        console.log('Creating order with data:', orderData)
        const result = await checkoutService.createOrder(orderData)
        console.log('Order creation result:', result)

        if (result && result.order) {
            const paymentMethod = paymentMethods[selectedPaymentMethod.value].code
            const orderId = result.order.id
            const amount = result.order.final_price

            console.log('Payment method:', paymentMethod)
            console.log('Order ID:', orderId)
            console.log('Amount:', amount)

            if (paymentMethod === 'cod') {
                navigateTo(`/status?status=success&orderId=${orderId}&amount=${amount}`)
            } else {
                let paymentUrl
                let paymentResult

                switch (paymentMethod) {
                    case 'vnpay':
                        paymentResult = await paymentService.generateVnpayUrl(orderId, amount)
                        paymentUrl = paymentResult.payment_url
                        break
                    case 'momo':
                        paymentResult = await paymentService.generateMomoUrl(orderId, amount)
                        paymentUrl = paymentResult.payment_url
                        break
                    case 'paypal':
                        paymentResult = await paymentService.generatePaypalUrl(orderId, amount)
                        paymentUrl = paymentResult.payment_url
                        break
                }
                if (paymentUrl) {
                    window.location.href = paymentUrl
                } else {
                    throw new Error('Không thể tạo URL thanh toán')
                }
            }
        } else {
            throw new Error('Không thể tạo đơn hàng')
        }
    } catch (err) {
        console.error('Error in placeOrder:', err)
        error.value = err.message || 'Có lỗi xảy ra khi đặt hàng'
    } finally {
        isLoading.value = false
    }
}

onMounted(async () => {
    try {
        isLoading.value = true
        await Promise.all([
            fetchAddresses(),
            fetchCart()
        ])
    } catch (err) {
        error.value = err.message || 'Có lỗi xảy ra khi tải dữ liệu'
    } finally {
        isLoading.value = false
    }
})
</script>

<template>
    <div class="tw-max-w-7xl tw-mx-auto tw-px-4 md:tw-px-6 tw-py-8">
        <h1 class="tw-text-2xl tw-font-bold tw-mb-8">Thanh toán</h1>

        <div v-if="isLoading" class="tw-flex tw-justify-center tw-items-center tw-py-12">
            <div
                class="tw-animate-spin tw-rounded-full tw-h-12 tw-w-12 tw-border-t-2 tw-border-b-2 tw-border-[#81AACC]">
            </div>
        </div>

        <div v-else-if="error" class="tw-bg-red-50 tw-p-4 tw-rounded-md tw-text-red-600 tw-mb-6">
            {{ error }}
        </div>

        <div v-else class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8">
            <div class="tw-space-y-8">
                <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm">
                    <AddressList :addresses="addresses" :selected-address="selectedAddress"
                        @select="selectedAddress = $event" @edit="openAddressModal" @delete="deleteAddress"
                        @add="openAddressModal" />

                    <PaymentMethods :methods="paymentMethods" :selected-method="selectedPaymentMethod"
                        @select="selectedPaymentMethod = $event" />
                </div>
            </div>
            <div class="tw-space-y-8">
                <OrderSummary :items="cartItems" :subtotal="subtotal" :shipping="shipping" :discount="discount"
                    @place-order="placeOrder" @apply-coupon="applyCoupon" />
            </div>
        </div>
        <AddressForm :show="showAddressForm" :editing-index="editingAddressIndex" :address="editingAddress"
            @close="closeAddressModal" @save="saveAddress" />
    </div>
</template>


<style scoped>
/* Add any component-specific styles here */
</style>