<template>
    <div class="orders-page">
        <div class="page-header">
            <h1>Quản lý đơn hàng</h1>
            <p class="text-gray-600">Quản lý và theo dõi đơn hàng</p>
        </div>

        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3>Chờ xử lý</h3>
                    <p>{{ pendingOrders }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon processing">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <div class="stat-content">
                    <h3>Đang giao</h3>
                    <p>{{ processingOrders }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>Hoàn thành</h3>
                    <p>{{ completedOrders }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon cancelled">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>Đã hủy</h3>
                    <p>{{ cancelledOrders }}</p>
                </div>
            </div>
        </div>

        <Table :columns="columns" :data="orders" @edit="handleEdit" />

        <Modal :show="showModal" :title="'Chi tiết đơn hàng #' + selectedOrder?.id" size="lg" @close="closeModal">
            <div class="order-info">
                <div class="info-group">
                    <h3>Thông tin khách hàng</h3>
                    <p><strong>Tên:</strong> {{ selectedOrder?.customerName }}</p>
                    <p><strong>Email:</strong> {{ selectedOrder?.customerEmail }}</p>
                    <p><strong>SĐT:</strong> {{ selectedOrder?.customerPhone }}</p>
                    <p><strong>Địa chỉ:</strong> {{ selectedOrder?.shippingAddress }}</p>
                </div>

                <div class="info-group">
                    <h3>Thông tin đơn hàng</h3>
                    <p><strong>Ngày đặt:</strong> {{ selectedOrder?.orderDate }}</p>
                    <p><strong>Phương thức thanh toán:</strong> {{ selectedOrder?.paymentMethod }}</p>
                    <p><strong>Trạng thái thanh toán:</strong>
                        <span :class="['status-badge', selectedOrder?.isPaid ? 'active' : 'inactive']">
                            {{ selectedOrder?.isPaid ? 'Đã thanh toán' : 'Chưa thanh toán' }}
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
                            <tr v-for="item in selectedOrder?.items" :key="item.id">
                                <td>{{ item.name }}</td>
                                <td>{{ item.quantity }}</td>
                                <td>{{ formatPrice(item.price) }}</td>
                                <td>{{ formatPrice(item.price * item.quantity) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3">Tổng tiền:</td>
                                <td>{{ formatPrice(selectedOrder?.total) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="order-status">
                <h3>Cập nhật trạng thái</h3>
                <Form :fields="[{
                    name: 'status',
                    label: '',
                    type: 'select',
                    options: statusOptions
                }]" :initial-data="{ status: selectedOrder?.status }" v-model="selectedOrder"
                    @submit="updateOrderStatus" />
            </div>
        </Modal>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})

import { ref, computed } from 'vue'
import Table from '~/components/admin/Table.vue'
import Modal from '~/components/admin/Modal.vue'
import Form from '~/components/admin/Form.vue'

// Table columns configuration
const columns = [
    { key: 'id', label: 'Mã đơn' },
    { key: 'customerName', label: 'Khách hàng' },
    { key: 'orderDate', label: 'Ngày đặt' },
    { key: 'total', label: 'Tổng tiền' },
    { key: 'status', label: 'Trạng thái', type: 'status' }
]

// Status options
const statusOptions = [
    { value: 'pending', label: 'Chờ xử lý' },
    { value: 'processing', label: 'Đang giao' },
    { value: 'completed', label: 'Hoàn thành' },
    { value: 'cancelled', label: 'Đã hủy' }
]

// Mock data
const orders = ref([
    {
        id: 'DH001',
        customerName: 'Nguyễn Văn A',
        customerEmail: 'nguyenvana@email.com',
        customerPhone: '0123456789',
        shippingAddress: '123 Đường ABC, Quận 1, TP.HCM',
        orderDate: '2024-01-15',
        total: 31990000,
        status: 'pending',
        paymentMethod: 'COD',
        isPaid: false,
        items: [
            {
                id: 1,
                name: 'iPhone 13 Pro Max',
                quantity: 1,
                price: 30990000
            },
            {
                id: 2,
                name: 'Ốp lưng iPhone',
                quantity: 2,
                price: 500000
            }
        ]
    },
    {
        id: 'DH002',
        customerName: 'Trần Thị B',
        customerEmail: 'tranthib@email.com',
        customerPhone: '0987654321',
        shippingAddress: '456 Đường XYZ, Quận 2, TP.HCM',
        orderDate: '2024-01-16',
        total: 25990000,
        status: 'processing',
        paymentMethod: 'Banking',
        isPaid: true,
        items: [
            {
                id: 3,
                name: 'Samsung Galaxy S21',
                quantity: 1,
                price: 25990000
            }
        ]
    },
    {
        id: 'DH003',
        customerName: 'Lê Văn C',
        customerEmail: 'levanc@email.com',
        customerPhone: '0369852147',
        shippingAddress: '789 Đường DEF, Quận 3, TP.HCM',
        orderDate: '2024-01-17',
        total: 35990000,
        status: 'completed',
        paymentMethod: 'Banking',
        isPaid: true,
        items: [
            {
                id: 4,
                name: 'MacBook Pro M1',
                quantity: 1,
                price: 35990000
            }
        ]
    },
    {
        id: 'DH004',
        customerName: 'Phạm Thị D',
        customerEmail: 'phamthid@email.com',
        customerPhone: '0741852963',
        shippingAddress: '321 Đường GHI, Quận 4, TP.HCM',
        orderDate: '2024-01-18',
        total: 23990000,
        status: 'cancelled',
        paymentMethod: 'COD',
        isPaid: false,
        items: [
            {
                id: 5,
                name: 'iPad Pro 2021',
                quantity: 1,
                price: 23990000
            }
        ]
    }
])

// Modal state
const showModal = ref(false)
const selectedOrder = ref(null)

// Computed properties for statistics
const pendingOrders = computed(() =>
    orders.value.filter(order => order.status === 'pending').length
)

const processingOrders = computed(() =>
    orders.value.filter(order => order.status === 'processing').length
)

const completedOrders = computed(() =>
    orders.value.filter(order => order.status === 'completed').length
)

const cancelledOrders = computed(() =>
    orders.value.filter(order => order.status === 'cancelled').length
)

// Handlers
const handleEdit = (order) => {
    selectedOrder.value = { ...order }
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    selectedOrder.value = null
}

const updateOrderStatus = () => {
    const index = orders.value.findIndex(o => o.id === selectedOrder.value.id)
    if (index !== -1) {
        orders.value[index].status = selectedOrder.value.status
    }
    closeModal()
}

// Utility functions
const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}
</script>

<style scoped>
.orders-page {
    padding: 1.5rem;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 1.875rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}

/* Stats cards */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-icon.pending {
    background: #fef3c7;
    color: #d97706;
}

.stat-icon.processing {
    background: #dbeafe;
    color: #2563eb;
}

.stat-icon.completed {
    background: #dcfce7;
    color: #16a34a;
}

.stat-icon.cancelled {
    background: #fee2e2;
    color: #dc2626;
}

.stat-content h3 {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.stat-content p {
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
}

/* Order info styles */
.order-info {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.info-group h3 {
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 1rem;
}

.info-group p {
    margin-bottom: 0.5rem;
    color: #6b7280;
}

.info-group strong {
    color: #374151;
}

.products-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.products-table th,
.products-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.products-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
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
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 1rem;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.active {
    background: #dcfce7;
    color: #166534;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #991b1b;
}
</style>