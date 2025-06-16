<template>
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3>Chờ xác nhận</h3>
                <p>{{ pendingOrders }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon processing">
                <i class="fas fa-shipping-fast"></i>
            </div>
            <div class="stat-content">
                <h3>Đang xử lý</h3>
                <p>{{ processingOrders }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon shipping">
                <i class="fas fa-truck"></i>
            </div>
            <div class="stat-content">
                <h3>Đang giao hàng</h3>
                <p>{{ shippingOrders }}</p>
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
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    orders: {
        type: Array,
        required: true
    }
})

const pendingOrders = computed(() =>
    props.orders.filter(order => order?.status === 'pending').length
)

const processingOrders = computed(() =>
    props.orders.filter(order => order?.status === 'processing').length
)

const shippingOrders = computed(() =>
    props.orders.filter(order => order?.status === 'shipping').length
)

const completedOrders = computed(() =>
    props.orders.filter(order => order?.status === 'completed').length
)

const cancelledOrders = computed(() =>
    props.orders.filter(order => order?.status === 'cancelled').length
)
</script>

<style scoped>
.stats-cards {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-icon.pending {
    background: #fff7ed;
    color: #c2410c;
}

.stat-icon.processing {
    background: #eff6ff;
    color: #1d4ed8;
}

.stat-icon.shipping {
    background: #f0fdf4;
    color: #15803d;
}

.stat-icon.completed {
    background: #f0fdf4;
    color: #15803d;
}

.stat-icon.cancelled {
    background: #fef2f2;
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

@media (max-width: 1280px) {
    .stats-cards {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .stats-cards {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .stats-cards {
        grid-template-columns: 1fr;
    }
}
</style>