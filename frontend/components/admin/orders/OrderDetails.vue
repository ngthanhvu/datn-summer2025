<template>
    <div class="order-info">
        <div v-if="loading" class="tw-p-4 tw-text-center">
            <div
                class="tw-inline-block tw-animate-spin tw-rounded-full tw-h-8 tw-w-8 tw-border-4 tw-border-primary tw-border-t-transparent">
            </div>
        </div>

        <div v-else-if="error" class="tw-p-4 tw-text-center tw-text-red-500">
            {{ error }}
        </div>

        <template v-else-if="currentOrder">
            <div class="info-group">
                <h3>Thông tin khách hàng</h3>
                <p><strong>Tên:</strong> {{ currentOrder.user?.username }}</p>
                <p><strong>Email:</strong> {{ currentOrder.user?.email }}</p>
                <p><strong>SĐT:</strong> {{ currentOrder.user?.phone }}</p>
                <p><strong>Địa chỉ:</strong> {{ currentOrder.address?.full_name }}, {{ currentOrder.address?.phone }},
                    {{ currentOrder.address?.street }}, {{ currentOrder.address?.ward }}, {{
                        currentOrder.address?.district }}, {{ currentOrder.address?.province }}</p>
            </div>

            <div class="info-group">
                <h3>Thông tin đơn hàng</h3>
                <p><strong>Mã đơn:</strong> #{{ currentOrder.id }}</p>
                <p><strong>Ngày đặt:</strong> {{ new Date(currentOrder.created_at).toLocaleDateString('vi-VN') }}</p>
                <p><strong>Phương thức thanh toán:</strong> {{ getPaymentMethod(currentOrder.payment_method) }}</p>
                <p><strong>Trạng thái thanh toán:</strong>
                    <span :class="[
                        'status-badge',
                        {
                            'active': currentOrder.payment_status === 'paid',
                            'inactive': currentOrder.payment_status === 'pending' || currentOrder.payment_status === 'failed' || currentOrder.payment_status === 'canceled'
                        }
                    ]">
                        {{ getPaymentStatus(currentOrder.payment_status) }}
                    </span>
                </p>
            </div>

            <div class="info-group">
                <h3>Sản phẩm</h3>
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Phân loại</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in currentOrder.orderDetails" :key="item.id">
                            <td>
                                <div class="tw-flex tw-items-center">
                                    <img :src="item.variant?.product?.mainImage?.image_path"
                                        :alt="item.variant?.product?.name"
                                        class="tw-w-12 tw-h-12 tw-object-cover tw-rounded">
                                    <div class="tw-ml-3">
                                        <div class="tw-font-medium">{{ item.variant?.product?.name }}</div>
                                        <div class="tw-text-xs tw-text-gray-500">SKU: {{ item.variant?.sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="tw-text-sm">
                                    <span class="tw-text-gray-500">Màu:</span> {{ item.variant?.color }}
                                </div>
                                <div class="tw-text-sm">
                                    <span class="tw-text-gray-500">Size:</span> {{ item.variant?.size }}
                                </div>
                            </td>
                            <td>{{ item.quantity }}</td>
                            <td>{{ formatPrice(item.price) }}</td>
                            <td>{{ formatPrice(item.total_price) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="tw-text-right">Tổng tiền hàng:</td>
                            <td>{{ formatPrice(currentOrder.total_price) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="tw-text-right">Phí vận chuyển:</td>
                            <td>{{ formatPrice(currentOrder.shipping_fee || 0) }}</td>
                        </tr>
                        <tr v-if="currentOrder.discount_price > 0">
                            <td colspan="4" class="tw-text-right">Giảm giá:</td>
                            <td>-{{ formatPrice(currentOrder.discount_price) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="tw-text-right tw-font-bold">Thành tiền:</td>
                            <td class="tw-font-bold">{{ formatPrice(currentOrder.final_price) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="order-status">
                <h3>Cập nhật trạng thái</h3>
                <Form :fields="[{
                    name: 'status',
                    label: '',
                    type: 'select',
                    options: statusOptions
                }]" :initial-data="{ status: currentOrder.status }" @submit="handleUpdateStatus" />
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Form from '~/components/admin/Form.vue'
import { useOrder } from '~/composables/useOrder'

const props = defineProps({
    orderId: {
        type: [String, Number],
        required: true
    }
})

const {
    currentOrder,
    loading,
    error,
    getOrder,
    updateOrderStatus,
    getOrderStatus,
    getPaymentStatus,
    getPaymentMethod,
    formatPrice
} = useOrder()

const statusOptions = [
    { value: 'pending', label: 'Chờ xử lý' },
    { value: 'processing', label: 'Đang giao' },
    { value: 'shipping', label: 'Đang giao hàng' },
    { value: 'completed', label: 'Hoàn thành' },
    { value: 'cancelled', label: 'Đã hủy' }
]

onMounted(async () => {
    await getOrder(props.orderId)
})

const handleUpdateStatus = async (data) => {
    try {
        await updateOrderStatus(props.orderId, data.status)
        await getOrder(props.orderId)
    } catch (err) {
        console.error('Failed to update order status:', err)
    }
}
</script>

<style scoped>
.order-info {
    padding: 1.5rem;
}

.info-group {
    margin-bottom: 2rem;
}

.info-group h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 1rem;
}

.info-group p {
    margin-bottom: 0.5rem;
    color: #4b5563;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-badge.active {
    background-color: #f0fdf4;
    color: #15803d;
}

.status-badge.inactive {
    background-color: #fef2f2;
    color: #dc2626;
}

.products-table {
    width: 100%;
    border-collapse: collapse;
}

.products-table th,
.products-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.products-table th {
    font-weight: 500;
    color: #6b7280;
    background-color: #f9fafb;
}

.products-table tfoot td {
    font-weight: 600;
    color: #111827;
}

.order-status {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

.order-status h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 1rem;
}
</style>