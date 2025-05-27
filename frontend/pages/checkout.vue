<template>
    <div class="tw-max-w-7xl tw-mx-auto tw-px-4 md:tw-px-6 tw-py-8">
        <h1 class="tw-text-2xl tw-font-bold tw-mb-8">Thanh toán</h1>

        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8">
            <!-- Left Column - Customer Information -->
            <div class="tw-space-y-8">
                <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm">
                    <AddressList :addresses="addresses" :selected-address="selectedAddress"
                        @select="selectedAddress = $event" @edit="openAddressModal" @delete="deleteAddress"
                        @add="openAddressModal" />

                    <PaymentMethods :methods="paymentMethods" :selected-method="selectedPaymentMethod"
                        @select="selectedPaymentMethod = $event" />
                </div>
            </div>

            <!-- Right Column - Order Summary -->
            <div class="tw-space-y-8">
                <OrderSummary :items="orderItems" :subtotal="subtotal" :shipping="shipping" :discount="discount"
                    @place-order="placeOrder" @apply-coupon="applyCoupon" />
            </div>
        </div>

        <!-- Address Modal -->
        <AddressForm :show="showAddressForm" :editing-index="editingAddressIndex" :address="editingAddress"
            @close="closeAddressModal" @save="saveAddress" />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import AddressList from '~/components/checkout/AddressList.vue'
import AddressForm from '~/components/checkout/AddressForm.vue'
import PaymentMethods from '~/components/checkout/PaymentMethods.vue'
import OrderSummary from '~/components/checkout/OrderSummary.vue'

// Address Management
const showAddressForm = ref(false)
const editingAddressIndex = ref(null)
const selectedAddress = ref(0)

const addresses = ref([
    {
        fullName: 'Nguyễn Văn A',
        phone: '0123456789',
        fullAddress: '123 Đường ABC, Phường 1, Quận 1, TP. HCM'
    }
])

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

const saveAddress = (address) => {
    if (editingAddressIndex.value === null) {
        addresses.value.push(address)
    } else {
        addresses.value[editingAddressIndex.value] = address
    }
    closeAddressModal()
}

const deleteAddress = (index) => {
    addresses.value.splice(index, 1)
    if (selectedAddress.value === index) {
        selectedAddress.value = 0
    }
}

// Payment Methods
const selectedPaymentMethod = ref(0)
const paymentMethods = [
    {
        title: 'Thanh toán khi nhận hàng (COD)',
        description: 'Thanh toán bằng tiền mặt khi nhận hàng'
    },
    {
        title: 'Chuyển khoản ngân hàng',
        description: 'Chuyển khoản trực tiếp vào tài khoản ngân hàng'
    },
    {
        title: 'Ví điện tử',
        description: 'Thanh toán qua Momo, VNPay, ZaloPay'
    }
]

// Order Summary
const orderItems = ref([
    {
        name: 'Áo sơ mi trắng',
        variant: 'Size: M | Số lượng: 1',
        price: 1290000,
        image: 'https://placehold.co/100x100'
    },
    {
        name: 'Quần jean slim fit',
        variant: 'Size: 32 | Số lượng: 1',
        price: 890000,
        image: 'https://placehold.co/100x100'
    }
])

const subtotal = computed(() => {
    return orderItems.value.reduce((total, item) => total + item.price, 0)
})

const shipping = ref(30000)
const discount = ref(0)

const applyCoupon = (code) => {
    // Implement coupon logic here
    console.log('Applying coupon:', code)
}

const placeOrder = () => {
    // Implement order placement logic here
    console.log('Placing order with:', {
        address: addresses.value[selectedAddress.value],
        paymentMethod: paymentMethods[selectedPaymentMethod.value],
        items: orderItems.value,
        subtotal: subtotal.value,
        shipping: shipping.value,
        discount: discount.value
    })
}
</script>

<style scoped>
/* Add any component-specific styles here */
</style>