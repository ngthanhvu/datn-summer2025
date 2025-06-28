# Dashboard Skeleton Components

Các component skeleton được sử dụng để hiển thị trạng thái loading cho dashboard admin.

## Components

### 1. StatsCardSkeleton.vue
Skeleton cho các thẻ thống kê (StatsCard).

**Sử dụng:**
```vue
<template>
  <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6">
    <StatsCardSkeleton v-for="i in 4" :key="i" />
  </div>
</template>

<script setup>
import StatsCardSkeleton from '~/components/admin/dashboard/StatsCardSkeleton.vue'
</script>
```

### 2. ChartSkeleton.vue
Skeleton cho các biểu đồ (RevenueChart, OrdersChart).

**Sử dụng:**
```vue
<template>
  <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-6">
    <ChartSkeleton />
    <ChartSkeleton />
  </div>
</template>

<script setup>
import ChartSkeleton from '~/components/admin/dashboard/ChartSkeleton.vue'
</script>
```

### 3. RecentOrdersSkeleton.vue
Skeleton cho bảng đơn hàng gần đây.

**Sử dụng:**
```vue
<template>
  <RecentOrdersSkeleton />
</template>

<script setup>
import RecentOrdersSkeleton from '~/components/admin/dashboard/RecentOrdersSkeleton.vue'
</script>
```

### 4. Skeleton.vue (UI Component)
Component skeleton tổng quát có thể tái sử dụng.

**Props:**
- `height`: Chiều cao (default: '4')
- `width`: Chiều rộng (default: 'full')
- `color`: Màu sắc (default: 'gray')
- `rounded`: Bo góc (default: 'md')
- `className`: Class tùy chỉnh
- `customStyle`: Style tùy chỉnh

**Sử dụng:**
```vue
<template>
  <!-- Skeleton cơ bản -->
  <Skeleton height="4" width="1/2" />
  
  <!-- Skeleton với tùy chỉnh -->
  <Skeleton 
    height="8" 
    width="full" 
    color="blue" 
    rounded="lg"
    className="tw-mb-2"
  />
</template>

<script setup>
import Skeleton from '~/components/ui/Skeleton.vue'
</script>
```

## Animation

Tất cả skeleton components đều sử dụng animation `skeleton-loading` với hiệu ứng gradient chạy từ trái sang phải.

```css
.skeleton-animation {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
  background-size: 400% 100%;
  animation: skeleton-loading 1.4s ease infinite;
}

@keyframes skeleton-loading {
  0% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}
```

## Best Practices

1. **Sử dụng đúng số lượng skeleton**: Đảm bảo số lượng skeleton matches với số lượng content thực tế
2. **Responsive design**: Skeleton nên responsive giống như content thực tế
3. **Performance**: Skeleton nên nhẹ và không ảnh hưởng đến performance
4. **Accessibility**: Skeleton nên có aria-label hoặc role phù hợp cho screen readers

## Ví dụ Implementation

```vue
<template>
  <div class="dashboard">
    <!-- Stats Cards -->
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6">
      <template v-if="loading">
        <StatsCardSkeleton v-for="i in 4" :key="i" />
      </template>
      <template v-else>
        <StatsCard v-for="stat in stats" :key="stat.id" :data="stat" />
      </template>
    </div>

    <!-- Charts -->
    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-6">
      <template v-if="loading">
        <ChartSkeleton />
        <ChartSkeleton />
      </template>
      <template v-else>
        <RevenueChart :data="revenueData" />
        <OrdersChart :data="ordersData" />
      </template>
    </div>

    <!-- Recent Orders -->
    <template v-if="loading">
      <RecentOrdersSkeleton />
    </template>
    <template v-else>
      <RecentOrders :orders="recentOrders" />
    </template>
  </div>
</template> 