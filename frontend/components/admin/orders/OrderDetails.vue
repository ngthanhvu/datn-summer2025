<template>
    <div class="order-info">
        <div class="info-group">
            <h3>Thông tin khách hàng</h3>
            <p><strong>Tên:</strong> {{ order?.customerName }}</p>
            <p><strong>Email:</strong> {{ order?.customerEmail }}</p>
            <p><strong>SĐT:</strong> {{ order?.customerPhone }}</p>
            <p><strong>Địa chỉ:</strong> {{ order?.shippingAddress }}</p>
        </div>

        <div class="info-group">
            <h3>Thông tin đơn hàng</h3>
            <p><strong>Ngày đặt:</strong> {{ order?.orderDate }}</p>
            <p><strong>Phương thức thanh toán:</strong> {{ order?.paymentMethod }}</p>
            <p><strong>Trạng thái thanh toán:</strong>
                <span :class="['status-badge', order?.isPaid ? 'active' : 'inactive']">
                    {{ order?.isPaid ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                </span>
            </p>
        </div>

        <div class="info-group">
            <h3>Sản phẩm</h3>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in order?.items" :key="item.id">
                        <td>{{ item.name }}</td>
                        <td>{{ item.quantity }}</td>
                        <td>{{ formatPrice(item.price) }}</td>
                        <td>{{ formatPrice(item.price * item.quantity) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">Tổng tiền:</td>
                        <td>{{ formatPrice(order?.total) }}</td>
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
            }]" :initial-data="{ status: order?.status }" @submit="updateOrderStatus" />
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import Form from '~/components/admin/Form.vue'

const props = defineProps({
    order: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['update-status'])

const statusOptions = [
    { value: 'pending', label: 'Chờ xử lý' },
    { value: 'processing', label: 'Đang giao' },
    { value: 'completed', label: 'Hoàn thành' },
    { value: 'cancelled', label: 'Đã hủy' }
]

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const updateOrderStatus = (data) => {
    emit('update-status', { id: props.order.id, status: data.status })
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