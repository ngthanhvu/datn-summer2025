<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-300 p-6">
        <h2 class="font-bold text-xl mb-6 text-gray-900">Đơn hàng của tôi</h2>

        <!-- Tabs filter order status -->
        <div class="flex items-center bg-white rounded-lg mb-6 border-b border-gray-300 overflow-x-auto">
            <div v-for="status in tabOrderStatuses" :key="status.value" @click="selectedStatus = status.value" :class="[
                'cursor-pointer px-4 py-3 font-medium text-sm whitespace-nowrap transition-colors',
                selectedStatus === status.value ? 'text-[#81aacc] border-b-2 border-[#81aacc] bg-blue-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
            ]">
                {{ status.label }}
            </div>
        </div>
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-gray-300 bg-gray-50">
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Mã đơn</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Ngày đặt</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Sản phẩm</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Tổng tiền</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Thanh toán</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="order in orders" :key="order.id">
                        <tr class="border-b border-gray-300 hover:bg-blue-50 cursor-pointer transition duration-200"
                            @click="toggleExpand(order.id)">
                            <td class="px-4 py-4 align-top">
                                <span class="font-medium text-gray-900">#{{ order.id }}</span>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <span class="text-gray-700">{{ formatDate(order.created_at) }}</span>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <div class="flex items-start gap-3">
                                    <img :src="order.order_details[0]?.variant?.product?.main_image?.image_path"
                                        class="w-12 h-12 object-cover rounded-lg flex-shrink-0"
                                        :alt="order.order_details[0]?.variant?.product?.name" />
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-gray-900 text-sm leading-tight mb-1">{{
                                            order.order_details[0]?.variant?.product?.name }}</p>
                                        <p class="text-gray-500 text-xs">
                                            {{ order.order_details.length }} sản phẩm
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <span class="font-medium text-gray-900">{{ formatPrice(order.final_price) }}đ</span>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <span :class="badgeClass(order.payment_status)">
                                    {{ getPaymentStatusLabel(order.payment_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <span :class="badgeClass(order.status)">
                                    {{ getStatusLabel(order.status) }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="expandedOrderId === order.id">
                            <td :colspan="6" class="bg-gray-50 border-b border-gray-300">
                                <!-- Timeline trạng thái đơn hàng -->
                                <div class="border-b border-gray-300 pb-6 px-5 pt-3">
                                    <h4 class="font-semibold mb-4 text-gray-900">Trạng thái đơn hàng</h4>
                                    <div class="flex items-center justify-between">
                                        <!-- Đặt hàng -->
                                        <div class="flex flex-col items-center relative">
                                            <div
                                                class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <span class="text-sm mt-2 text-gray-700">Đặt hàng</span>
                                            <span class="text-xs text-gray-500">{{ formatDate(order.created_at)
                                            }}</span>
                                        </div>
                                        <div class="flex-1 h-0.5 bg-gray-200 mx-4"></div>
                                        <!-- Xác nhận -->
                                        <div class="flex flex-col items-center relative">
                                            <div :class="[
                                                'w-10 h-10 rounded-full flex items-center justify-center text-white',
                                                order.status === 'pending' ? 'bg-yellow-500' : 'bg-green-500'
                                            ]">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                            </div>
                                            <span class="text-sm mt-2 text-gray-700">Xác nhận</span>
                                            <span class="text-xs text-gray-500">
                                                {{ order.status === 'pending' ? 'Đang chờ' :
                                                    formatDate(order.updated_at) }}
                                            </span>
                                        </div>
                                        <div class="flex-1 h-0.5 bg-gray-200 mx-4"></div>
                                        <!-- Giao hàng -->
                                        <div class="flex flex-col items-center relative">
                                            <div :class="[
                                                'w-10 h-10 rounded-full flex items-center justify-center text-white',
                                                ['shipping', 'completed'].includes(order.status) ? 'bg-green-500' : 'bg-gray-300'
                                            ]">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                </svg>
                                            </div>
                                            <span class="text-sm mt-2 text-gray-700">Giao hàng</span>
                                            <span class="text-xs text-gray-500">
                                                {{
                                                    ['shipping', 'completed'].includes(order.status) ? 'Đang giao' :
                                                        'Chờ xử lý'
                                                }}
                                            </span>
                                        </div>
                                        <div class="flex-1 h-0.5 bg-gray-200 mx-4"></div>
                                        <!-- Hoàn thành -->
                                        <div class="flex flex-col items-center relative">
                                            <div
                                                :class="['w-10 h-10 rounded-full flex items-center justify-center text-white', order.status === 'completed' ? 'bg-green-500' : 'bg-gray-300']">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <span class="text-sm mt-2 text-gray-700">Hoàn thành</span>
                                            <span class="text-xs text-gray-500">
                                                {{ order.status === 'completed' ? 'Đã nhận hàng' : 'Chờ xử lý' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Nội dung chi tiết đơn hàng -->
                                <div class="px-5 py-3">
                                    <h4 class="font-semibold mb-2 text-gray-900">Sản phẩm</h4>
                                    <div class="space-y-2">
                                        <div v-for="item in order.order_details" :key="item.id"
                                            class="flex items-center gap-4 p-3 bg-white rounded-lg border border-gray-300">
                                            <!-- Ảnh sản phẩm -->
                                            <router-link :to="`/san-pham/${item.variant?.product?.slug}`"
                                                class="flex-shrink-0">
                                                <img :src="item.variant?.product?.main_image?.image_path"
                                                    class="w-16 h-16 object-cover rounded-lg"
                                                    :alt="item.variant?.product?.name" />
                                            </router-link>

                                            <!-- Tên + thông tin -->
                                            <div class="flex-1 min-w-0">
                                                <h5 class="font-medium text-gray-900 text-sm">
                                                    <router-link :to="`/san-pham/${item.variant?.product?.slug}`"
                                                        class="hover:underline">
                                                        {{ item.variant?.product?.name }}
                                                    </router-link>
                                                </h5>
                                                <p class="text-gray-600 text-xs">Size: {{ item.variant?.size }}</p>
                                                <p class="text-gray-600 text-xs">Số lượng: {{ item.quantity }}</p>
                                            </div>
                                            <!-- Giá -->
                                            <div class="text-right flex-shrink-0">
                                                <p class="font-medium text-gray-900 text-sm">
                                                    {{ formatPrice(item.price) }}đ
                                                </p>
                                                <p class="text-gray-600 text-xs">
                                                    Tổng: {{ formatPrice(item.total_price) }}đ
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                                        <div class="bg-white p-4 rounded-lg border border-gray-300">
                                            <h4 class="font-semibold mb-2 text-gray-900">Thông tin giao hàng</h4>
                                            <div class="space-y-1 text-sm">
                                                <p><span class="font-medium text-gray-700">Người nhận:</span> {{
                                                    order.address?.full_name }}</p>
                                                <p><span class="font-medium text-gray-700">Điện thoại:</span> {{
                                                    order.address?.phone }}</p>
                                                <p><span class="font-medium text-gray-700">Địa chỉ:</span> {{
                                                    getFullAddress(order.address) }}</p>
                                            </div>
                                        </div>
                                        <div class="bg-white p-4 rounded-lg border border-gray-300">
                                            <h4 class="font-semibold mb-2 text-gray-900">Thông tin thanh toán</h4>
                                            <div class="space-y-1 text-sm">
                                                <p><span class="font-medium text-gray-700">Phương thức:</span> {{
                                                    getPaymentMethodLabel(order.payment_method) }}</p>
                                                <p><span class="font-medium text-gray-700">Trạng thái:</span> {{
                                                    getPaymentStatusLabel(order.payment_status) }}</p>
                                                <p><span class="font-medium text-gray-700">Mã tra cứu:</span> {{
                                                    order.tracking_code || 'Chưa có mã' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 bg-white p-4 rounded-lg border border-gray-300">
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between"><span class="text-gray-600">Tổng tiền
                                                    hàng</span><span class="font-medium">{{
                                                        formatPrice(order.total_price) }}đ</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Phí vận
                                                    chuyển</span><span class="font-medium">{{
                                                        formatPrice(calculateShipping(order)) }}đ</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Giảm
                                                    giá</span><span class="font-medium text-red-600">-{{
                                                        formatPrice(order.discount_price) }}đ</span></div>
                                            <div
                                                class="flex justify-between font-bold text-lg border-t border-gray-300 pt-2">
                                                <span class="text-gray-900">Thành tiền</span><span
                                                    class="text-gray-900">{{ formatPrice(order.final_price) }}đ</span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Action Buttons - Horizontal Layout -->
                                    <div class="mt-6 flex flex-wrap gap-3 justify-end">
                                        <button v-if="canCancelOrder(order)"
                                            @click.stop="showCancelReasonModal = true; selectedOrder = order"
                                            class="inline-flex items-center px-4 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300 transition-all duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Huỷ đơn hàng
                                        </button>

                                        <button v-if="canRequestReturn(order)"
                                            @click.stop="showReturnReasonModal = true; selectedOrder = order"
                                            class="inline-flex items-center px-4 py-2.5 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 focus:ring-4 focus:ring-orange-300 transition-all duration-200 shadow-sm hover:shadow-md">
                                            <i class="fa-solid fa-arrow-rotate-left mr-2"></i>
                                            Yêu cầu hoàn hàng
                                        </button>

                                        <button v-if="order.status === 'completed'" @click.stop="openReviewModal(order)"
                                            class="inline-flex items-center px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-all duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                            </svg>
                                            Viết đánh giá
                                        </button>

                                        <button
                                            v-if="order && (order.status === 'completed' || order.status === 'cancelled')"
                                            @click.stop="handleReorder(order.id)"
                                            class="inline-flex items-center px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300 transition-all duration-200 shadow-sm hover:shadow-md">
                                            <i class="fa-solid fa-arrows-rotate mr-2"></i>
                                            Mua lại
                                        </button>
                                    </div>

                                    <!-- Return status display in expanded view -->
                                    <div v-if="order.return_status" class="mt-4 space-y-2">
                                        <div v-if="order.return_status === 'requested'" class="flex items-start mb-2">
                                            <svg class="w-6 h-6 text-yellow-500 mt-1 flex-shrink-0" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                                            </svg>
                                            <div
                                                class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 w-full ml-2 relative">
                                                <div class="text-gray-500 text-xs absolute top-1 right-1">
                                                    {{ order.return_requested_at ?
                                                        formatDateNoPrefix(order.return_requested_at) :
                                                        formatDateNoPrefix(order.updated_at) }}
                                                </div>
                                                <div class="text-yellow-700 font-medium text-left text-sm">
                                                    Bạn đã gửi yêu cầu hoàn hàng. Vui lòng chờ xác nhận.
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else-if="order.return_status === 'approved'"
                                            class="flex items-start mb-2">
                                            <svg class="w-6 h-6 text-green-500 mt-1 flex-shrink-0" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                                            </svg>
                                            <div
                                                class="bg-green-50 border border-green-200 rounded-lg p-3 w-full ml-2 relative">
                                                <div class="text-gray-500 text-xs absolute top-1 right-1">
                                                    {{ order.return_requested_at ?
                                                        formatDateNoPrefix(order.return_requested_at) :
                                                        formatDateNoPrefix(order.updated_at) }}
                                                </div>
                                                <div class="text-green-700 font-bold text-left text-sm">
                                                    Yêu cầu hoàn hàng đã được xác nhận.
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else-if="order.return_status === 'rejected'"
                                            class="flex items-start mb-2">
                                            <svg class="w-6 h-6 text-red-500 mt-1 flex-shrink-0" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                                            </svg>
                                            <div
                                                class="bg-red-50 border border-red-200 rounded-lg p-3 w-full ml-2 relative">
                                                <div class="text-gray-500 text-xs absolute top-1 right-1">
                                                    {{ order.return_requested_at ?
                                                        formatDateNoPrefix(order.return_requested_at) :
                                                        formatDateNoPrefix(order.updated_at) }}
                                                </div>
                                                <div class="text-red-700 font-bold text-left text-sm">
                                                    Yêu cầu hoàn hàng đã bị từ chối
                                                </div>
                                                <div v-if="order.reject_reason"
                                                    class="text-red-600 text-xs break-words whitespace-pre-line text-left mt-1">
                                                    <span class="font-semibold">Lý do từ chối:</span> {{
                                                        order.reject_reason }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Hiển thị lý do hoàn hàng nếu có -->
                                        <div v-if="order.return_reason && order.return_status === 'requested'"
                                            class="flex items-start mb-2">
                                            <svg class="w-5 h-5 text-blue-500 mt-1 flex-shrink-0" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M12 8v.01" />
                                            </svg>
                                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 w-full ml-2">
                                                <div class="text-blue-700 text-sm">
                                                    <span class="font-semibold">Lý do hoàn hàng:</span> {{
                                                        order.return_reason }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Return action buttons -->
                                        <div class="flex gap-2 justify-end">
                                            <button v-if="canRequestReturn(order)"
                                                @click.stop="showReturnReasonModal = true; selectedOrder = order"
                                                class="bg-orange-500 text-white px-3 py-1 rounded text-sm hover:bg-orange-600 transition-colors">
                                                Yêu cầu hoàn hàng
                                            </button>
                                            <!-- <button
                                                v-if="order && (order.status === 'completed' || order.status === 'cancelled')"
                                                @click.stop="handleReorder(order.id)"
                                                class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors">
                                                Mua lại
                                            </button> -->
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-4">
            <div v-for="order in orders" :key="order.id"
                class="bg-white border border-gray-300 rounded-lg p-4 space-y-3">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="font-medium text-gray-900">#{{ order.id }}</span>
                        <p class="text-gray-500 text-sm">{{ formatDate(order.created_at) }}</p>
                    </div>
                    <button @click="toggleExpand(order.id)"
                        class="bg-blue-600 text-white rounded-lg px-3 py-1 text-xs hover:bg-blue-700 transition-colors">
                        {{ expandedOrderId === order.id ? 'Thu gọn' : 'Chi tiết' }}
                    </button>
                </div>

                <div class="flex items-start gap-3">
                    <img :src="order.order_details[0]?.variant?.product?.main_image?.image_path"
                        class="w-12 h-12 object-cover rounded-lg flex-shrink-0"
                        :alt="order.order_details[0]?.variant?.product?.name" />
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 text-sm leading-tight mb-1">{{
                            order.order_details[0]?.variant?.product?.name }}</p>
                        <p class="text-gray-500 text-xs">
                            {{ order.order_details.length }} sản phẩm
                        </p>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <span class="font-medium text-gray-900">{{ formatPrice(order.final_price) }}đ</span>
                    <div class="flex flex-col items-end gap-1">
                        <span :class="badgeClass(order.payment_status)">
                            {{ getPaymentStatusLabel(order.payment_status) }}
                        </span>
                        <span :class="badgeClass(order.status)">
                            {{ getStatusLabel(order.status) }}
                        </span>
                    </div>
                </div>

                <!-- Expanded content for mobile -->
                <div v-if="expandedOrderId === order.id" class="border-t border-gray-200 pt-4 space-y-4">
                    <!-- Timeline trạng thái đơn hàng -->
                    <div class="border-b border-gray-300 pb-4">
                        <h4 class="font-semibold mb-3 text-gray-900 text-sm">Trạng thái đơn hàng</h4>
                        <div class="flex items-center justify-between">
                            <!-- Đặt hàng -->
                            <div class="flex flex-col items-center relative">
                                <div
                                    class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-xs mt-1 text-gray-700">Đặt hàng</span>
                                <span class="text-xs text-gray-500">{{ formatDate(order.created_at) }}</span>
                            </div>
                            <div class="flex-1 h-0.5 bg-gray-200 mx-2"></div>
                            <!-- Xác nhận -->
                            <div class="flex flex-col items-center relative">
                                <div :class="[
                                    'w-8 h-8 rounded-full flex items-center justify-center text-white',
                                    order.status === 'pending' ? 'bg-yellow-500' : 'bg-green-500'
                                ]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <span class="text-xs mt-1 text-gray-700">Xác nhận</span>
                                <span class="text-xs text-gray-500">
                                    {{ order.status === 'pending' ? 'Đang chờ' :
                                        formatDate(order.updated_at) }}
                                </span>
                            </div>
                            <div class="flex-1 h-0.5 bg-gray-200 mx-2"></div>
                            <!-- Giao hàng -->
                            <div class="flex flex-col items-center relative">
                                <div :class="[
                                    'w-8 h-8 rounded-full flex items-center justify-center text-white',
                                    ['shipping', 'completed'].includes(order.status) ? 'bg-green-500' : 'bg-gray-300'
                                ]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                    </svg>
                                </div>
                                <span class="text-xs mt-1 text-gray-700">Giao hàng</span>
                                <span class="text-xs text-gray-500">
                                    {{
                                        ['shipping', 'completed'].includes(order.status) ? 'Đang giao' :
                                            'Chờ xử lý'
                                    }}
                                </span>
                            </div>
                            <div class="flex-1 h-0.5 bg-gray-200 mx-2"></div>
                            <!-- Hoàn thành -->
                            <div class="flex flex-col items-center relative">
                                <div
                                    :class="['w-8 h-8 rounded-full flex items-center justify-center text-white', order.status === 'completed' ? 'bg-green-500' : 'bg-gray-300']">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-xs mt-1 text-gray-700">Hoàn thành</span>
                                <span class="text-xs text-gray-500">
                                    {{ order.status === 'completed' ? 'Đã nhận hàng' : 'Chờ xử lý' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Sản phẩm -->
                    <div>
                        <h4 class="font-semibold mb-2 text-gray-900 text-sm">Sản phẩm</h4>
                        <div class="space-y-2">
                            <div v-for="item in order.order_details" :key="item.id"
                                class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                                <img :src="item.variant?.product?.main_image?.image_path"
                                    class="w-12 h-12 object-cover rounded-lg flex-shrink-0"
                                    :alt="item.variant?.product?.name" />
                                <div class="flex-1 min-w-0">
                                    <h5 class="font-medium text-gray-900 text-sm">{{
                                        item.variant?.product?.name }}</h5>
                                    <p class="text-gray-600 text-xs">Size: {{ item.variant?.size }}</p>
                                    <p class="text-gray-600 text-xs">Số lượng: {{ item.quantity }}</p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="font-medium text-gray-900 text-sm">{{ formatPrice(item.price)
                                        }}đ</p>
                                    <p class="text-gray-600 text-xs">Tổng: {{ formatPrice(item.total_price)
                                        }}đ</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin giao hàng và thanh toán -->
                    <div class="grid grid-cols-1 gap-3">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <h4 class="font-semibold mb-2 text-gray-900 text-sm">Thông tin giao hàng</h4>
                            <div class="space-y-1 text-xs">
                                <p><span class="font-medium text-gray-700">Người nhận:</span> {{
                                    order.address?.full_name }}</p>
                                <p><span class="font-medium text-gray-700">Điện thoại:</span> {{
                                    order.address?.phone }}</p>
                                <p><span class="font-medium text-gray-700">Địa chỉ:</span> {{
                                    getFullAddress(order.address) }}</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <h4 class="font-semibold mb-2 text-gray-900 text-sm">Thông tin thanh toán</h4>
                            <div class="space-y-1 text-xs">
                                <p><span class="font-medium text-gray-700">Phương thức:</span> {{
                                    getPaymentMethodLabel(order.payment_method) }}</p>
                                <p><span class="font-medium text-gray-700">Trạng thái:</span> {{
                                    getPaymentStatusLabel(order.payment_status) }}</p>
                                <p><span class="font-medium text-gray-700">Mã tra cứu:</span> {{
                                    order.tracking_code || 'Chưa có mã' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tổng tiền -->
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-gray-600">Tổng tiền
                                    hàng</span><span class="font-medium">{{
                                        formatPrice(order.total_price) }}đ</span></div>
                            <div class="flex justify-between"><span class="text-gray-600">Phí vận
                                    chuyển</span><span class="font-medium">{{
                                        formatPrice(calculateShipping(order)) }}đ</span></div>
                            <div class="flex justify-between"><span class="text-gray-600">Giảm
                                    giá</span><span class="font-medium text-red-600">-{{
                                        formatPrice(order.discount_price) }}đ</span></div>
                            <div class="flex justify-between font-bold text-base border-t border-gray-300 pt-2">
                                <span class="text-gray-900">Thành tiền</span><span class="text-gray-900">{{
                                    formatPrice(order.final_price) }}đ</span>
                            </div>
                        </div>
                    </div>

                    <!-- Return status display -->
                    <div v-if="order.return_status" class="space-y-2">
                        <div v-if="order.return_status === 'requested'" class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                            </svg>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 w-full ml-2 relative">
                                <div class="text-gray-500 text-xs absolute top-1 right-1">
                                    {{ order.return_requested_at ?
                                        formatDateNoPrefix(order.return_requested_at) :
                                        formatDateNoPrefix(order.updated_at) }}
                                </div>
                                <div class="text-yellow-700 font-medium text-left text-sm">
                                    Bạn đã gửi yêu cầu hoàn hàng. Vui lòng chờ xác nhận.
                                </div>
                            </div>
                        </div>
                        <div v-else-if="order.return_status === 'approved'" class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                            </svg>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 w-full ml-2 relative">
                                <div class="text-gray-500 text-xs absolute top-1 right-1">
                                    {{ order.return_requested_at ?
                                        formatDateNoPrefix(order.return_requested_at) :
                                        formatDateNoPrefix(order.updated_at) }}
                                </div>
                                <div class="text-green-700 font-bold text-left text-sm">
                                    Yêu cầu hoàn hàng đã được xác nhận.
                                </div>
                            </div>
                        </div>
                        <div v-else-if="order.return_status === 'rejected'" class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                            </svg>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3 w-full ml-2 relative">
                                <div class="text-gray-500 text-xs absolute top-1 right-1">
                                    {{ order.return_requested_at ?
                                        formatDateNoPrefix(order.return_requested_at) :
                                        formatDateNoPrefix(order.updated_at) }}
                                </div>
                                <div class="text-red-700 font-bold text-left text-sm">
                                    Yêu cầu hoàn hàng đã bị từ chối
                                </div>
                                <div v-if="order.reject_reason"
                                    class="text-red-600 text-xs break-words whitespace-pre-line text-left mt-1">
                                    <span class="font-semibold">Lý do từ chối:</span> {{
                                        order.reject_reason }}
                                </div>
                            </div>
                        </div>

                        <!-- Hiển thị lý do hoàn hàng nếu có (mobile) -->
                        <div v-if="order.return_reason && order.return_status === 'requested'" class="flex items-start">
                            <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M12 8v.01" />
                            </svg>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-2 w-full ml-2">
                                <div class="text-blue-700 text-xs">
                                    <span class="font-semibold">Lý do hoàn hàng:</span> {{
                                        order.return_reason }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons - Mobile -->
                    <div class="flex flex-wrap gap-2 justify-end">
                        <button v-if="canCancelOrder(order)"
                            @click.stop="showCancelReasonModal = true; selectedOrder = order"
                            class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-300 transition-all duration-200 shadow-sm">
                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Huỷ đơn hàng
                        </button>
                        <button v-if="canRequestReturn(order)"
                            @click.stop="showReturnReasonModal = true; selectedOrder = order"
                            class="inline-flex items-center px-3 py-2 bg-orange-500 text-white text-xs font-medium rounded-lg hover:bg-orange-600 focus:ring-2 focus:ring-orange-300 transition-all duration-200 shadow-sm">
                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 17l4 4 4-4m0-5V3m-8 9v6a2 2 0 002 2h4a2 2 0 002-2v-6" />
                            </svg>
                            Yêu cầu hoàn hàng
                        </button>
                        <button v-if="order.status === 'completed'" @click.stop="openReviewModal(order)"
                            class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 transition-all duration-200 shadow-sm">
                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                            Viết đánh giá
                        </button>
                        <button v-if="order && (order.status === 'completed' || order.status === 'cancelled')"
                            @click.stop="handleReorder(order.id)"
                            class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-300 transition-all duration-200 shadow-sm">
                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m6 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01" />
                            </svg>
                            Mua lại
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="orders.length === 0" class="text-center py-12">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Không có đơn hàng</h3>
            <p class="text-gray-500">Bạn chưa có đơn hàng nào.</p>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-gray-900/50 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-lg">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold">Chi tiết đơn hàng #{{ selectedOrder?.id }}</h3>
                    <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="selectedOrder" class="space-y-8">
                    <div v-if="showCancelWarning(selectedOrder)"
                        class="bg-yellow-100 text-yellow-800 p-4 rounded mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M12 8v.01" />
                        </svg>
                        <span>Đơn hàng đã quá thời hạn hủy (24 giờ), vì vậy bạn vui lòng chờ và nhận hàng.</span>
                    </div>

                    <div v-if="selectedOrder.return_requested_at"
                        class="mt-4 p-4 bg-orange-50 rounded-lg border-l-4 border-orange-400">
                        <div class="flex items-center">
                            <div
                                class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center mr-2 mt-0.5">
                                <svg class="w-5 h-5 text-orange-700" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 17l4 4 4-4m0-5V3m-8 9v6a2 2 0 002 2h4a2 2 0 002-2v-6" />
                                </svg>
                            </div>
                            <div>
                                <p>
                                    <span class="font-medium text-orange-600 text-sm md:text-base">Bạn đã
                                        gửi yêu cầu hoàn hàng vào: </span>
                                    <span class="text-orange-600 text-sm md:text-base">{{
                                        formatDate(selectedOrder.return_requested_at) }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="border-b border-gray-300 pb-6">
                        <h4 class="font-semibold mb-4">Trạng thái đơn hàng</h4>
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col items-center relative">
                                <div
                                    class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-sm mt-2">Đặt hàng</span>
                                <span class="text-xs text-gray-500">{{ formatDate(selectedOrder.created_at)
                                }}</span>
                            </div>
                            <div class="flex-1 h-0.5 bg-gray-200 mx-4"></div>
                            <div class="flex flex-col items-center relative">
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center text-white',
                                    selectedOrder.status === 'pending' ? 'bg-yellow-500' : 'bg-green-500'
                                ]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <span class="text-sm mt-2">Xác nhận</span>
                                <span class="text-xs text-gray-500">
                                    {{ selectedOrder.status === 'pending' ? 'Đang chờ' :
                                        formatDate(selectedOrder.updated_at) }}</span>
                            </div>
                            <div class="flex-1 h-0.5 bg-gray-200 mx-4"></div>
                            <div class="flex flex-col items-center relative">
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center text-white',
                                    ['shipping', 'completed'].includes(selectedOrder.status) ? 'bg-green-500' : 'bg-gray-300'
                                ]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                    </svg>
                                </div>
                                <span class="text-sm mt-2">Giao hàng</span>
                                <span class="text-xs text-gray-500">{{ ['shipping',
                                    'completed'].includes(selectedOrder.status) ? 'Đang giao' : 'Chờ xử lý' }}</span>
                            </div>
                            <div class="flex-1 h-0.5 bg-gray-200 mx-4"></div>
                            <div class="flex flex-col items-center relative">
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center text-white',
                                    selectedOrder.status === 'completed' ? 'bg-green-500' : 'bg-gray-300'
                                ]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-sm mt-2">Hoàn thành</span>
                                <span class="text-xs text-gray-500">
                                    {{ selectedOrder.status === 'completed' ? 'Đã nhậnhàng' : 'Chờ xử lý' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold mb-4">Thông tin giao hàng</h4>
                            <div class="space-y-2">
                                <p class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ selectedOrder.address?.full_name }}
                                </p>
                                <p class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    {{ selectedOrder.address?.phone }}
                                </p>
                                <p class="flex items-start">
                                    <svg class="w-5 h-5 text-gray-500 mr-2 mt-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ getFullAddress(selectedOrder.address) }}
                                </p>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold mb-4">Thông tin thanh toán</h4>
                            <div class="space-y-2">
                                <p class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Phương thức: {{ getPaymentMethodLabel(selectedOrder.payment_method) }}
                                </p>
                                <p class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Trạng thái:
                                    <span :class="badgeClass(selectedOrder.payment_status)" class="ml-2">
                                        {{ getPaymentStatusLabel(selectedOrder.payment_status) }}
                                    </span>
                                </p>
                                <p class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Mã tra cứu:
                                    <span class="ml-2 font-medium text-blue-600">{{
                                        selectedOrder?.tracking_code || 'Chưa có mã' }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-semibold mb-4">Sản phẩm</h4>
                        <div class="space-y-4">
                            <div v-for="item in selectedOrder.order_details" :key="item.id"
                                class="flex items-center gap-4 p-4 bg-gray-50 rounded">
                                <img :src="item.variant?.product?.main_image?.image_path"
                                    class="w-20 h-20 object-cover rounded" :alt="item.variant?.product?.name" />
                                <div class="flex-1">
                                    <h5 class="font-medium">{{ item.variant?.product?.name }}</h5>
                                    <p class="text-gray-600">Size: {{ item.variant?.size }}</p>
                                    <p class="text-gray-600">Số lượng: {{ item.quantity }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium">{{ formatPrice(item.price) }}đ</p>
                                    <p class="text-gray-600">Tổng: {{ formatPrice(item.total_price) }}đ</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-300 pt-4">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tổng tiền hàng</span>
                                <span>{{ formatPrice(selectedOrder.total_price) }}đ</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Phí vận chuyển</span>
                                <span>{{ formatPrice(calculateShipping(selectedOrder)) }}đ</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Giảm giá</span>
                                <span>-{{ formatPrice(selectedOrder.discount_price) }}đ</span>
                            </div>
                            <div class="flex justify-between font-bold text-lg border-t pt-2 border-gray-300">
                                <span>Thành tiền</span>
                                <span>{{ formatPrice(selectedOrder.final_price) }}đ</span>
                            </div>
                        </div>
                        <div class="mt-4 text-right space-x-2">
                            <template v-if="selectedOrder">
                                <div v-if="selectedOrder.return_status === 'requested'" class="flex items-start mb-2">
                                    <svg class="w-7 h-7 text-yellow-500 mt-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                                    </svg>
                                    <div class="bg-yellow-50 border border-yellow-200 rounded p-4 w-full ml-2 relative">
                                        <div class="text-gray-500 text-xs absolute top-2 right-2">
                                            {{ selectedOrder.return_requested_at ?
                                                formatDateNoPrefix(selectedOrder.return_requested_at) :
                                                formatDateNoPrefix(selectedOrder.updated_at) }}
                                        </div>
                                        <div class="text-yellow-700 font-medium text-left">
                                            Bạn đã gửi yêu cầu hoàn hàng. Vui lòng chờ xác nhận.
                                        </div>
                                    </div>
                                </div>
                                <div v-else-if="selectedOrder.return_status === 'approved'"
                                    class="flex items-start mb-2">
                                    <svg class="w-7 h-7 text-green-500 mt-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                                    </svg>
                                    <div class="bg-green-50 border border-green-200 rounded p-4 w-full ml-2 relative">
                                        <div class="text-gray-500 text-xs absolute top-2 right-2">
                                            {{ selectedOrder.return_requested_at ?
                                                formatDateNoPrefix(selectedOrder.return_requested_at) :
                                                formatDateNoPrefix(selectedOrder.updated_at) }}
                                        </div>
                                        <div class="text-green-700 font-bold text-left">
                                            Yêu cầu hoàn hàng đã được xác nhận.
                                        </div>
                                    </div>
                                </div>
                                <div v-else-if="selectedOrder.return_status === 'rejected'"
                                    class="flex items-start mb-2">
                                    <svg class="w-7 h-7 text-red-500 mt-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                                    </svg>
                                    <div class="bg-red-50 border border-red-200 rounded p-4 w-full ml-2 relative">
                                        <div class="text-gray-500 text-xs absolute top-2 right-2">
                                            {{ selectedOrder.return_requested_at ?
                                                formatDateNoPrefix(selectedOrder.return_requested_at) :
                                                formatDateNoPrefix(selectedOrder.updated_at) }}
                                        </div>
                                        <div class="text-red-700 font-bold text-left">
                                            Yêu cầu hoàn hàng đã bị từ chối
                                        </div>
                                        <div v-if="selectedOrder.reject_reason"
                                            class="text-red-600 text-sm break-words whitespace-pre-line text-left mt-1">
                                            <span class="font-semibold">Lý do từ chối:</span> {{
                                                selectedOrder.reject_reason }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Hiển thị lý do hoàn hàng nếu có (modal) -->
                                <div v-if="selectedOrder.return_reason && selectedOrder.return_status === 'requested'"
                                    class="flex items-start mb-2">
                                    <svg class="w-6 h-6 text-blue-500 mt-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M12 8v.01" />
                                    </svg>
                                    <div class="bg-blue-50 border border-blue-200 rounded p-3 w-full ml-2">
                                        <div class="text-blue-700 text-left">
                                            <span class="font-semibold">Lý do hoàn hàng:</span> {{
                                                selectedOrder.return_reason }}
                                        </div>
                                    </div>
                                </div>
                                <div v-if="selectedOrder.status === 'cancelled' && selectedOrder.cancel_reason"
                                    class="mt-4 p-4 bg-red-50 rounded-lg border-l-4 border-red-400 relative">
                                    <div class="text-gray-500 text-xs absolute top-2 right-2">
                                        {{ formatDateNoPrefix(selectedOrder.updated_at) }}
                                    </div>
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center mr-2 mt-0.5">
                                            <svg class="w-5 h-5 text-red-700" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p>
                                                <span class="font-medium text-red-600 text-sm md:text-base">Lý
                                                    do
                                                    hủy đơn hàng: </span>
                                                <span class="text-red-600 text-sm md:text-base">{{
                                                    selectedOrder.cancel_reason }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <button v-if="canCancelOrder(selectedOrder)" @click="showCancelReasonModal = true"
                                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                                    Hủy đơn hàng
                                </button>
                                <button
                                    v-if="selectedOrder && (selectedOrder.status === 'completed' || selectedOrder.status === 'cancelled')"
                                    @click="handleReorder(selectedOrder.id)"
                                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                    Mua lại đơn hàng
                                </button>
                                <button v-if="canRequestReturn(selectedOrder)"
                                    @click="handleRequestReturn(selectedOrder.id)"
                                    class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                                    Yêu cầu hoàn hàng
                                </button>
                                <button v-if="selectedOrder.status === 'completed'"
                                    @click="openReviewModal(selectedOrder)"
                                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                    Viết đánh giá
                                </button>
                            </template>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Chọn lý do hủy đơn hàng -->
        <div v-if="showCancelReasonModal"
            class="fixed inset-0 bg-gray-900/60 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
                <h3 class="text-lg font-bold mb-4">Lý Do Hủy</h3>
                <div class="mb-4 text-sm bg-yellow-50 p-3 rounded text-yellow-800">
                    Bạn có thể cập nhật thông tin nhận hàng một lần trước khi hủy. Nếu xác nhận hủy, toàn bộ đơn hàng sẽ
                    bị hủy.
                    Vui lòng chọn lý do hủy phù hợp nhé!
                </div>
                <div class="space-y-2 mb-4">
                    <div v-for="reason in cancelReasons" :key="reason.value" class="flex items-center">
                        <input type="radio" :id="'cancel-reason-' + reason.value" v-model="cancelReason"
                            :value="reason.value" class="mr-2" />
                        <label :for="'cancel-reason-' + reason.value" class="cursor-pointer">{{ reason.label
                            }}</label>
                    </div>
                    <input v-if="cancelReason === 'other'" v-model="cancelReasonOther" type="text"
                        class="mt-2 w-full p-2 border border-gray-300 rounded" placeholder="Vui lòng ghi rõ lý do..." />
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="showCancelReasonModal = false" class="bg-gray-200 px-4 py-2 rounded">Không phải
                        bây giờ</button>
                    <button @click="confirmCancelOrder"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Hủy đơn
                        hàng</button>
                </div>
            </div>
        </div>

        <!-- Modal đánh giá sản phẩm -->
        <div v-if="showReviewModal"
            class="fixed inset-0 bg-gray-900/60 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-lg">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold">Đánh giá sản phẩm</h3>
                    <button @click="closeReviewModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="selectedOrderForReview" class="space-y-6">
                    <!-- Thông tin sản phẩm -->
                    <div class="border-b border-gray-200 pb-4">
                        <h4 class="font-semibold mb-3 text-gray-900">Sản phẩm cần đánh giá</h4>
                        <div v-if="selectedOrderForReview.order_details[0]"
                            class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                            <img :src="selectedOrderForReview.order_details[0].variant?.product?.main_image?.image_path"
                                class="w-16 h-16 object-cover rounded-lg flex-shrink-0"
                                :alt="selectedOrderForReview.order_details[0].variant?.product?.name" />
                            <div class="flex-1">
                                <h5 class="font-medium text-gray-900">{{
                                    selectedOrderForReview.order_details[0].variant?.product?.name }}</h5>
                                <p class="text-gray-600 text-sm">Size: {{
                                    selectedOrderForReview.order_details[0].variant?.size
                                }}</p>
                                <p class="text-gray-600 text-sm">Số lượng: {{
                                    selectedOrderForReview.order_details[0].quantity
                                }}</p>
                            </div>
                        </div>
                        <div v-if="selectedOrderForReview.order_details.length > 1" class="mt-2 text-sm text-gray-600">
                            <p>Lưu ý: Bạn chỉ có thể đánh giá 1 sản phẩm mỗi lần. Sản phẩm đầu tiên trong đơn hàng sẽ
                                được đánh
                                giá.</p>
                        </div>
                    </div>

                    <!-- Form đánh giá -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Đánh giá của bạn</label>
                            <div class="flex items-center gap-2">
                                <button v-for="star in 5" :key="star" @click="reviewForm.rating = star" :class="[
                                    'text-2xl transition-colors',
                                    star <= reviewForm.rating ? 'text-yellow-400' : 'text-gray-300'
                                ]">
                                    ★
                                </button>
                                <span class="ml-2 text-sm text-gray-600">{{ reviewForm.rating }}/5 sao</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nội dung đánh giá</label>
                            <textarea v-model="reviewForm.content" rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Hãy chia sẻ trải nghiệm của bạn về sản phẩm này..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hình ảnh (tùy chọn)</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                <input type="file" ref="imageInput" @change="handleImageUpload" multiple
                                    accept="image/*" class="hidden" />
                                <button @click="$refs.imageInput.click()"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    Chọn hình ảnh
                                </button>
                                <p class="text-sm text-gray-500 mt-2">Tối đa 5 hình, mỗi hình dưới 2MB</p>
                            </div>

                            <!-- Preview hình ảnh -->
                            <div v-if="previewImages.length > 0" class="mt-4 grid grid-cols-5 gap-2">
                                <div v-for="(image, index) in previewImages" :key="index" class="relative">
                                    <img :src="image.preview" class="w-20 h-20 object-cover rounded-lg" />
                                    <button @click="removeImage(index)"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-600">
                                        ×
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nút gửi -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button @click="closeReviewModal"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                            Hủy
                        </button>
                        <button @click="submitReview" :disabled="isSubmittingReview"
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ isSubmittingReview ? 'Đang gửi...' : 'Gửi đánh giá' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showReturnReasonModal"
            class="fixed inset-0 bg-gray-900/60 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
                <h3 class="text-lg font-bold mb-4">Lý Do Hoàn Hàng</h3>
                <div class="mb-4 text-sm bg-blue-50 p-3 rounded text-blue-800">
                    Vui lòng chọn lý do hoàn hàng phù hợp. Yêu cầu của bạn sẽ được xem xét trong vòng 24 giờ.
                </div>
                <div class="space-y-2 mb-4">
                    <div v-for="reason in returnReasons" :key="reason.value" class="flex items-center">
                        <input type="radio" :id="'return-reason-' + reason.value" v-model="returnReason"
                            :value="reason.value" class="mr-2" />
                        <label :for="'return-reason-' + reason.value" class="cursor-pointer">{{ reason.label
                            }}</label>
                    </div>
                    <input v-if="returnReason === 'other'" v-model="returnReasonOther" type="text"
                        class="mt-2 w-full p-2 border border-gray-300 rounded" placeholder="Vui lòng ghi rõ lý do..." />
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="showReturnReasonModal = false" class="bg-gray-200 px-4 py-2 rounded">Không phải
                        bây giờ</button>
                    <button @click="confirmReturnRequest"
                        class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700">Gửi yêu cầu
                        hoàn hàng</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { push } from 'notivue'
import { useOrder } from '../../composable/useOrder'
import { useReviews } from '../../composable/useReviews'
import { useAuth } from '../../composable/useAuth'
import { useHead } from '@vueuse/head'
useHead({
    title: 'Đơn hàng của tôi | DEVGANG',
    meta: [
        {
            name: 'description',
            content: 'Quản lý đơn hàng của bạn',
        },
    ],
})

const orderService = useOrder()
const reviewService = useReviews()
const { user } = useAuth()
const orders = ref([])
const showModal = ref(false)
const selectedOrder = ref(null)
const selectedStatus = ref('')
const selectedDate = ref('')
const expandedOrderId = ref(null)
const showCancelReasonModal = ref(false)
const cancelReason = ref('')
const cancelReasonOther = ref('')
const showReturnReasonModal = ref(false)
const returnReason = ref('')
const returnReasonOther = ref('')

// Review modal variables
const showReviewModal = ref(false)
const selectedOrderForReview = ref(null)
const isSubmittingReview = ref(false)
const previewImages = ref([])
const reviewForm = ref({
    rating: 0,
    content: '',
    images: []
})

function toggleExpand(orderId) {
    expandedOrderId.value = expandedOrderId.value === orderId ? null : orderId
}

const columns = [
    { key: 'id', label: 'Mã đơn hàng' },
    { key: 'created_at', label: 'Ngày đặt' },
    { key: 'status', label: 'Trạng thái', type: 'status', labelKey: 'statusLabel' },
    { key: 'payment_status', label: 'Thanh toán', type: 'status', labelKey: 'paymentStatusLabel' },
    { key: 'final_price', label: 'Tổng tiền', type: 'price', prefix: '' },
]

const orderStatuses = [
    { value: 'pending', label: 'Chờ xác nhận' },
    { value: 'processing', label: 'Đang xử lý' },
    { value: 'shipping', label: 'Đang giao hàng' },
    { value: 'completed', label: 'Hoàn thành' },
    { value: 'cancelled', label: 'Đã hủy' }
]

const paymentStatuses = [
    { value: 'pending', label: 'Chờ thanh toán' },
    { value: 'paid', label: 'Đã thanh toán' },
    { value: 'failed', label: 'Thanh toán thất bại' },
    { value: 'canceled', label: 'Đã hủy' },
    { value: 'refunded', label: 'Đã hoàn tiền' }
]

const tabOrderStatuses = [
    { value: '', label: 'Tất cả' },
    { value: 'pending', label: 'Chờ thanh toán' },
    { value: 'shipping', label: 'Vận chuyển' },
    { value: 'processing', label: 'Chờ giao hàng' },
    { value: 'completed', label: 'Hoàn thành' },
    { value: 'cancelled', label: 'Đã hủy' },
    { value: 'refunded', label: 'Trả hàng/Hoàn tiền' }
]

const cancelReasons = [
    { value: 'change_address', label: 'Tôi muốn thay đổi địa chỉ hoặc số điện thoại nhận hàng.' },
    { value: 'change_coupon', label: 'Tôi muốn áp dụng hoặc thay đổi mã giảm giá.' },
    { value: 'change_product', label: 'Tôi muốn thay đổi sản phẩm (kích thước, màu sắc, số lượng...).' },
    { value: 'payment_issue', label: 'Tôi gặp khó khăn khi thanh toán.' },
    { value: 'found_better', label: 'Tôi tìm được nơi mua khác tốt hơn.' },
    { value: 'no_need', label: 'Tôi không còn nhu cầu mua sản phẩm này nữa.' },
    { value: 'ordered_by_mistake', label: 'Tôi đặt nhầm đơn hàng.' },
    { value: 'other', label: 'Lý do khác' }
]

const returnReasons = [
    { value: 'quality_issue', label: 'Sản phẩm không đúng chất lượng' },
    { value: 'wrong_item', label: 'Sản phẩm không đúng' },
    { value: 'damaged_item', label: 'Sản phẩm bị hư hỏng' },
    { value: 'missing_item', label: 'Thiếu sản phẩm' },
    { value: 'other', label: 'Lý do khác' }
]

const fetchOrders = async () => {
    try {
        await orderService.getMyOrders()
        let filteredOrders = orderService.orders.value || []

        if (selectedStatus.value) {
            filteredOrders = filteredOrders.filter(order => order.status === selectedStatus.value)
        }

        if (selectedDate.value) {
            const filterDate = new Date(selectedDate.value)
            filteredOrders = filteredOrders.filter(order => {
                const orderDate = new Date(order.created_at)
                return orderDate.toDateString() === filterDate.toDateString()
            })
        }

        orders.value = (Array.isArray(filteredOrders) ? filteredOrders : []).map(order => ({
            ...order,
            statusLabel: getStatusLabel(order.status),
            paymentStatusLabel: getPaymentStatusLabel(determinePaymentStatus(order))
        }))
    } catch (error) {
        console.error('Error fetching orders:', error)
    }
}

const handleFilterChange = (filters) => {
}

const openOrderDetail = (order) => {
    selectedOrder.value = order
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    selectedOrder.value = null
}

const getStatusLabel = (status) => {
    const found = orderStatuses.find(s => s.value === status)
    return found ? found.label : status
}

const getPaymentStatusLabel = (status) => {
    const found = paymentStatuses.find(s => s.value === status)
    return found ? found.label : status
}

const badgeClass = (status) => {
    let base = 'inline-flex items-center px-3 py-1 text-xs font-medium rounded-full ';
    switch (status) {
        case 'pending':
            return base + 'bg-yellow-100 text-yellow-800';
        case 'processing':
            return base + 'bg-blue-100 text-blue-800';
        case 'shipping':
            return base + 'bg-purple-100 text-purple-800';
        case 'completed':
            return base + 'bg-green-100 text-green-800';
        case 'cancelled':
            return base + 'bg-red-100 text-red-800';
        case 'paid':
            return base + 'bg-green-100 text-green-800';
        case 'failed':
            return base + 'bg-red-100 text-red-800';
        case 'canceled':
            return base + 'bg-red-100 text-red-800';
        case 'refunded':
            return base + 'bg-gray-100 text-gray-800';
        default:
            return base + 'bg-gray-100 text-gray-800';
    }
}

const formatPrice = (price) => {
    const numPrice = Number(price) // Convert to number
    if (isNaN(numPrice)) return '0' // Return '0' if not a number
    return new Intl.NumberFormat('vi-VN').format(numPrice)
}

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getPaymentMethodLabel = (method) => {
    const methods = {
        'cod': 'Thanh toán khi nhận hàng (COD)',
        'vnpay': 'VNPay',
        'momo': 'MoMo',
        'paypal': 'PayPal'
    }
    return methods[method] || method
}

const getFullAddress = (address) => {
    if (!address) return ''
    const parts = [
        address.street,
        address.hamlet,
        address.ward,
        address.district,
        address.province
    ].filter(Boolean)
    return parts.join(', ')
}

// Add function to determine payment status based on payment method and order status
const determinePaymentStatus = (order) => {
    if (order.status === 'cancelled') {
        return 'canceled'
    }

    if (order.payment_method === 'cod') {
        return 'pending'
    }

    if (['vnpay', 'momo', 'paypal'].includes(order.payment_method)) {
        return 'paid'
    }

    return order.payment_status
}

// Add watch for filters
watch([selectedStatus, selectedDate], () => {
    fetchOrders()
    handleFilterChange({
        status: selectedStatus.value,
        date: selectedDate.value
    })
})

const canCancelOrder = (order) => {
    if (!order) return false
    if (!['pending', 'processing'].includes(order.status)) return false
    const createdAt = new Date(order.created_at)
    const now = new Date()
    const diffHours = (now - createdAt) / (1000 * 60 * 60)
    return diffHours <= 24
}

const confirmCancelOrder = async () => {
    const reasonLabel = cancelReason.value === 'other'
        ? cancelReasonOther.value.trim()
        : cancelReasons.find(r => r.value === cancelReason.value)?.label || ''

    if (!reasonLabel) {
        push.error('Vui lòng chọn hoặc nhập lý do hủy đơn hàng')
        return
    }

    try {
        await orderService.cancelOrder(selectedOrder.value.id, reasonLabel)
        showCancelReasonModal.value = false
        closeModal()
        fetchOrders()
        push.success('Hủy đơn hàng thành công!')
        cancelReason.value = ''
        cancelReasonOther.value = ''
    } catch (err) {
        push.error(err?.response?.data?.message || err.message || 'Hủy đơn hàng thất bại')
    }
}

const handleReorder = async (orderId) => {
    try {
        const res = await orderService.reorderOrder(orderId)
        alert(res.message || 'Đã thêm vào giỏ hàng')
    } catch (err) {
        alert(err?.response?.data?.message || err.message || 'Mua lại đơn hàng thất bại')
    }
}

const showCancelWarning = (order) => {
    if (!order) return false
    if (!['pending', 'processing'].includes(order.status)) return false
    const createdAt = new Date(order.created_at)
    const now = new Date()
    const diffHours = (now - createdAt) / (1000 * 60 * 60)
    return diffHours > 24
}

const canRequestReturn = (order) => {
    if (!order) return false
    // Chỉ cho phép hoàn hàng đơn hàng đã hoàn thành, không cho phép đơn hàng đã hủy
    if (order.status !== 'completed') return false
    if (order.return_status === 'requested' || order.return_status === 'approved' || order.return_status === 'rejected') return false
    const completedAt = new Date(order.updated_at)
    const now = new Date()
    const diffDays = (now - completedAt) / (1000 * 60 * 60 * 24)
    return diffDays <= 3
}

const handleRequestReturn = async (orderId) => {
    const order = orders.value.find(order => order.id === orderId)
    if (!order) {
        push.error('Không tìm thấy đơn hàng!');
        return;
    }
    if (order.status !== 'completed') {
        push.error('Chỉ có thể hoàn hàng cho đơn hàng đã hoàn thành');
        return;
    }
    if (order.return_requested_at) {
        push.error('Bạn đã gửi yêu cầu hoàn hàng cho đơn này rồi');
        return;
    }
    if (order.payment_method === 'cod') {
        push.error('Đơn thanh toán COD không hỗ trợ hoàn hàng');
        return;
    }
    const completedAt = new Date(order.updated_at);
    const now = new Date();
    const diffDays = (now - completedAt) / (1000 * 60 * 60 * 24);
    if (diffDays > 3) {
        push.error('Chỉ có thể hoàn hàng trong vòng 3 ngày kể từ khi đơn hoàn thành');
        return;
    }

    // Nếu tất cả điều kiện đều hợp lệ, mở modal để chọn lý do
    selectedOrder.value = order
    showReturnReasonModal.value = true
}

const formatDateNoPrefix = (date) => {
    if (!date) return ''
    const d = new Date(date)
    return d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }) + ' ' + d.toLocaleDateString('vi-VN', { day: 'numeric', month: 'long', year: 'numeric' })
}

const calculateShipping = (order) => {
    if (!order) return 0
    const total = Number(order.total_price) || 0
    const final = Number(order.final_price) || 0
    const shipping = final - total
    return shipping > 0 ? shipping : 0
}

// Review functions
const openReviewModal = async (order) => {
    if (!order || !user.value) return

    // Check if user has already reviewed any product in this order
    let hasReviewed = false
    for (const item of order.order_details) {
        const productSlug = item.variant?.product?.slug
        if (!productSlug) continue

        try {
            const reviewCheck = await reviewService.checkUserReview(user.value.id, productSlug)
            if (reviewCheck.hasReviewed) {
                hasReviewed = true
                break
            }
        } catch (error) {
            console.error('Error checking review status:', error)
        }
    }

    if (hasReviewed) {
        push.error('Bạn đã đánh giá sản phẩm trong đơn hàng này rồi')
        return
    }

    selectedOrderForReview.value = order
    showReviewModal.value = true
    // Reset form
    reviewForm.value = {
        rating: 0,
        content: '',
        images: []
    }
    previewImages.value = []
}

const closeReviewModal = () => {
    showReviewModal.value = false
    selectedOrderForReview.value = null
    reviewForm.value = {
        rating: 0,
        content: '',
        images: []
    }
    previewImages.value = []
}

const handleImageUpload = (event) => {
    const files = Array.from(event.target.files)
    if (files.length + previewImages.value.length > 5) {
        push.error('Chỉ được chọn tối đa 5 hình ảnh')
        return
    }

    files.forEach(file => {
        if (file.size > 2 * 1024 * 1024) {
            push.error(`Hình ảnh ${file.name} quá lớn. Kích thước tối đa là 2MB`)
            return
        }

        const reader = new FileReader()
        reader.onload = (e) => {
            previewImages.value.push({
                file: file,
                preview: e.target.result
            })
            reviewForm.value.images.push(file)
        }
        reader.readAsDataURL(file)
    })
}

const removeImage = (index) => {
    previewImages.value.splice(index, 1)
    reviewForm.value.images.splice(index, 1)
}

const submitReview = async () => {
    if (!selectedOrderForReview.value || !user.value) return

    if (reviewForm.value.rating === 0) {
        push.error('Vui lòng chọn số sao đánh giá')
        return
    }

    if (!reviewForm.value.content.trim()) {
        push.error('Vui lòng nhập nội dung đánh giá')
        return
    }

    isSubmittingReview.value = true

    try {
        // For now, we'll review the first product in the order
        // In the future, we can add a product selector
        const firstItem = selectedOrderForReview.value.order_details[0]
        if (!firstItem?.variant?.product?.slug) {
            throw new Error('Không tìm thấy thông tin sản phẩm')
        }

        const reviewData = {
            user_id: user.value.id,
            product_slug: firstItem.variant.product.slug,
            rating: reviewForm.value.rating,
            content: reviewForm.value.content,
            images: reviewForm.value.images
        }

        await reviewService.addReview(reviewData)

        push.success('Đánh giá đã được gửi thành công!')
        closeReviewModal()

        // Refresh orders to update review status
        await fetchOrders()

    } catch (error) {
        console.error('Error submitting review:', error)
        push.error(error?.response?.data?.message || 'Có lỗi xảy ra khi gửi đánh giá')
    } finally {
        isSubmittingReview.value = false
    }
}

const confirmReturnRequest = async () => {
    const reasonLabel = returnReason.value === 'other'
        ? returnReasonOther.value.trim()
        : returnReasons.find(r => r.value === returnReason.value)?.label || ''

    if (!reasonLabel) {
        push.error('Vui lòng chọn hoặc nhập lý do hoàn hàng')
        return
    }

    try {
        await orderService.requestReturn(selectedOrder.value.id, reasonLabel)
        showReturnReasonModal.value = false
        selectedOrder.value = null
        fetchOrders()
        push.success('Yêu cầu hoàn hàng đã được gửi!')
        returnReason.value = ''
        returnReasonOther.value = ''
    } catch (err) {
        push.error(err?.response?.data?.message || err.message || 'Gửi yêu cầu hoàn hàng thất bại')
    }
}

onMounted(() => {
    fetchOrders()
})
</script>