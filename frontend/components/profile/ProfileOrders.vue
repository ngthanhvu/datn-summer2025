<template>
    <div class="tw-bg-white tw-p-6 tw-rounded tw-shadow">
        <h2 class="tw-font-bold tw-text-lg tw-mb-6">Đơn hàng của tôi</h2>

        <!-- Tabs filter order status -->
        <div class="tw-flex tw-items-center tw-bg-white tw-rounded tw-mb-6 tw-border-b tw-overflow-x-auto">
            <div v-for="status in tabOrderStatuses" :key="status.value" @click="selectedStatus = status.value" :class="[
                'tw-cursor-pointer tw-px-4 tw-py-3 tw-font-medium tw-text-base',
                selectedStatus === status.value ? 'tw-text-[#81aacc] tw-border-b-2 tw-border-[#81aacc]' : 'tw-text-gray-800'
            ]">
                {{ status.label }}
            </div>
        </div>
        <!-- Desktop Table -->
        <div class="tw-hidden md:tw-block tw-overflow-x-auto">
            <table class="table table-striped">
                <thead>
                    <tr class="tw-border-b tw-bg-gray-50">
                        <th class="tw-px-4 tw-py-3">Mã đơn</th>
                        <th class="tw-px-4 tw-py-3">Ngày đặt</th>
                        <th class="tw-px-4 tw-py-3">Sản phẩm</th>
                        <th class="tw-px-4 tw-py-3">Tổng tiền</th>
                        <th class="tw-px-4 tw-py-3">Thanh toán</th>
                        <th class="tw-px-4 tw-py-3">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="order in orders" :key="order.id">
                        <tr class="tw-border-b hover:tw-bg-blue-50 tw-cursor-pointer tw-transition tw-duration-200"
                            @click="toggleExpand(order.id)">
                            <td class="tw-px-4 tw-py-3">
                                <span class="tw-font-medium">#{{ order.id }}</span>
                            </td>
                            <td class="tw-px-4 tw-py-3">
                                {{ formatDate(order.created_at) }}
                            </td>
                            <td class="tw-px-4 tw-py-3">
                                <div class="tw-flex tw-items-center tw-gap-3">
                                    <img :src="order.order_details[0]?.variant?.product?.main_image?.image_path"
                                        class="tw-w-8 tw-h-8 tw-object-cover tw-rounded"
                                        :alt="order.order_details[0]?.variant?.product?.name" />
                                    <div>
                                        <p class="tw-font-medium tw-text-base">{{
                                            order.order_details[0]?.variant?.product?.name }}</p>
                                        <p class="tw-text-gray-500 tw-text-sm">
                                            {{ order.order_details.length }} sản phẩm
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="tw-px-4 tw-py-3">
                                <span class="tw-font-medium">{{ formatPrice(order.final_price) }}đ</span>
                            </td>
                            <td class="tw-px-4 tw-py-3">
                                <div class="tw-flex tw-flex-col tw-gap-1">
                                    <span :class="badgeClass(order.payment_status)">
                                        {{ getPaymentStatusLabel(order.payment_status) }}
                                    </span>
                                </div>
                            </td>
                            <td class="tw-px-4 tw-py-3">
                                <span :class="badgeClass(order.status)">
                                    {{ getStatusLabel(order.status) }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="expandedOrderId === order.id">
                            <td :colspan="7" class="tw-bg-white tw-border-b">
                                <!-- Timeline trạng thái đơn hàng -->
                                <div class="tw-border-b tw-pb-6 tw-px-5 tw-pt-3">
                                    <h4 class="tw-font-semibold tw-mb-4">Trạng thái đơn hàng</h4>
                                    <div class="tw-flex tw-items-center tw-justify-between">
                                        <!-- Đặt hàng -->
                                        <div class="tw-flex tw-flex-col tw-items-center tw-relative">
                                            <div
                                                class="tw-w-10 tw-h-10 tw-rounded-full tw-bg-green-500 tw-flex tw-items-center tw-justify-center tw-text-white">
                                                <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <span class="tw-text-sm tw-mt-2">Đặt hàng</span>
                                            <span class="tw-text-xs tw-text-gray-500">{{ formatDate(order.created_at)
                                                }}</span>
                                        </div>
                                        <div class="tw-flex-1 tw-h-0.5 tw-bg-gray-200 tw-mx-4"></div>
                                        <!-- Xác nhận -->
                                        <div class="tw-flex tw-flex-col tw-items-center tw-relative">
                                            <div :class="[
                                                'tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white',
                                                order.status === 'pending' ? 'tw-bg-yellow-500' : 'tw-bg-green-500'
                                            ]">
                                                <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                            </div>
                                            <span class="tw-text-sm tw-mt-2">Xác nhận</span>
                                            <span class="tw-text-xs tw-text-gray-500">
                                                {{ order.status === 'pending' ? 'Đang chờ' :
                                                    formatDate(order.updated_at) }}
                                            </span>
                                        </div>
                                        <div class="tw-flex-1 tw-h-0.5 tw-bg-gray-200 tw-mx-4"></div>
                                        <!-- Giao hàng -->
                                        <div class="tw-flex tw-flex-col tw-items-center tw-relative">
                                            <div :class="[
                                                'tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white',
                                                ['shipping', 'completed'].includes(order.status) ? 'tw-bg-green-500' : 'tw-bg-gray-300'
                                            ]">
                                                <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                </svg>
                                            </div>
                                            <span class="tw-text-sm tw-mt-2">Giao hàng</span>
                                            <span class="tw-text-xs tw-text-gray-500">
                                                {{
                                                    ['shipping', 'completed'].includes(order.status) ? 'Đang giao' :
                                                        'Chờ xử lý'
                                                }}
                                            </span>
                                        </div>
                                        <div class="tw-flex-1 tw-h-0.5 tw-bg-gray-200 tw-mx-4"></div>
                                        <!-- Hoàn thành -->
                                        <div class="tw-flex tw-flex-col tw-items-center tw-relative">
                                            <div
                                                :class="['tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white', order.status === 'completed' ? 'tw-bg-green-500' : 'tw-bg-gray-300']">
                                                <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <span class="tw-text-sm tw-mt-2">Hoàn thành</span>
                                            <span class="tw-text-xs tw-text-gray-500">
                                                {{ order.status === 'completed' ? 'Đã nhận hàng' : 'Chờ xử lý' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Nội dung chi tiết đơn hàng, căn chỉnh lại cho đồng bộ -->
                                <div class="tw-px-5 tw-py-3">
                                    <h4 class="tw-font-semibold tw-mb-2">Sản phẩm</h4>
                                    <div class="tw-space-y-2">
                                        <div v-for="item in order.order_details" :key="item.id"
                                            class="tw-flex tw-items-center tw-gap-4 tw-p-2 tw-bg-gray-50 tw-rounded">
                                            <img :src="item.variant?.product?.main_image?.image_path"
                                                class="tw-w-16 tw-h-16 tw-object-cover tw-rounded"
                                                :alt="item.variant?.product?.name" />
                                            <div class="tw-flex-1">
                                                <h5 class="tw-font-medium">{{ item.variant?.product?.name }}</h5>
                                                <p class="tw-text-gray-600">Size: {{ item.variant?.size }}</p>
                                                <p class="tw-text-gray-600">Số lượng: {{ item.quantity }}</p>
                                            </div>
                                            <div class="tw-text-right">
                                                <p class="tw-font-medium">{{ formatPrice(item.price) }}đ</p>
                                                <p class="tw-text-gray-600">Tổng: {{ formatPrice(item.total_price) }}đ
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tw-mt-4 tw-grid tw-grid-cols-2 tw-gap-4">
                                        <div>
                                            <h4 class="tw-font-semibold tw-mb-2">Thông tin giao hàng</h4>
                                            <p><b>Người nhận:</b> {{ order.address?.full_name }}</p>
                                            <p><b>Điện thoại:</b> {{ order.address?.phone }}</p>
                                            <p><b>Địa chỉ:</b> {{ getFullAddress(order.address) }}</p>
                                        </div>
                                        <div>
                                            <h4 class="tw-font-semibold tw-mb-2">Thông tin thanh toán</h4>
                                            <p><b>Phương thức:</b> {{ getPaymentMethodLabel(order.payment_method) }}</p>
                                            <p><b>Trạng thái:</b> {{ getPaymentStatusLabel(order.payment_status) }}</p>
                                            <p><b>Mã tra cứu:</b> {{ order.tracking_code || 'Chưa có mã' }}</p>
                                        </div>
                                    </div>
                                    <div class="tw-mt-4 tw-space-y-1">
                                        <div class="tw-flex tw-justify-between"><span>Tổng tiền hàng</span><span>{{
                                            formatPrice(order.total_price) }}đ</span></div>
                                        <div class="tw-flex tw-justify-between"><span>Phí vận chuyển</span><span>{{
                                            formatPrice(order.shipping_fee) }}đ</span></div>
                                        <div class="tw-flex tw-justify-between"><span>Giảm giá</span><span>-{{
                                            formatPrice(order.discount_price) }}đ</span></div>
                                        <div class="tw-flex tw-justify-between tw-font-bold tw-text-lg"><span>Thành
                                                tiền</span><span>{{ formatPrice(order.final_price) }}đ</span></div>
                                    </div>
                                    <div v-if="canCancelOrder(order)" class="tw-mt-4 tw-text-right">
                                        <button @click.stop="showCancelReasonModal = true; selectedOrder = order"
                                            class="tw-bg-red-600 tw-text-white tw-px-4 tw-py-2 tw-rounded hover:tw-bg-red-700">
                                            Huỷ đơn hàng
                                        </button>
                                    </div>
                                    <div v-if="canRequestReturn(order)" class="tw-mt-4 tw-text-right">
                                        <button @click.stop="handleRequestReturn(order.id)"
                                            class="tw-bg-orange-500 tw-text-white tw-px-4 tw-py-2 tw-rounded tw-text-sm hover:tw-bg-orange-600">
                                            Yêu cầu hoàn hàng
                                        </button>
                                    </div>

                                    <!-- Return status display in expanded view -->
                                    <div v-if="order.return_status" class="tw-mt-4 tw-space-y-2">
                                        <div v-if="order.return_status === 'requested'"
                                            class="tw-flex tw-items-start tw-mb-2">
                                            <svg class="tw-w-6 tw-h-6 tw-text-yellow-500 tw-mt-1" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                                            </svg>
                                            <div
                                                class="tw-bg-yellow-50 tw-border tw-border-yellow-200 tw-rounded tw-p-3 tw-w-full tw-ml-2 tw-relative">
                                                <div
                                                    class="tw-text-gray-500 tw-text-xs tw-absolute tw-top-1 tw-right-1">
                                                    {{ order.return_requested_at ?
                                                        formatDateNoPrefix(order.return_requested_at) :
                                                        formatDateNoPrefix(order.updated_at) }}
                                                </div>
                                                <div class="tw-text-yellow-700 tw-font-medium tw-text-left tw-text-sm">
                                                    Bạn đã gửi yêu cầu hoàn hàng. Vui lòng chờ xác nhận.
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else-if="order.return_status === 'approved'"
                                            class="tw-flex tw-items-start tw-mb-2">
                                            <svg class="tw-w-6 tw-h-6 tw-text-green-500 tw-mt-1" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                                            </svg>
                                            <div
                                                class="tw-bg-green-50 tw-border tw-border-green-200 tw-rounded tw-p-3 tw-w-full tw-ml-2 tw-relative">
                                                <div
                                                    class="tw-text-gray-500 tw-text-xs tw-absolute tw-top-1 tw-right-1">
                                                    {{ order.return_requested_at ?
                                                        formatDateNoPrefix(order.return_requested_at) :
                                                        formatDateNoPrefix(order.updated_at) }}
                                                </div>
                                                <div class="tw-text-green-700 tw-font-bold tw-text-left tw-text-sm">
                                                    Yêu cầu hoàn hàng đã được xác nhận.
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else-if="order.return_status === 'rejected'"
                                            class="tw-flex tw-items-start tw-mb-2">
                                            <svg class="tw-w-6 tw-h-6 tw-text-red-500 tw-mt-1" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                                            </svg>
                                            <div
                                                class="tw-bg-red-50 tw-border tw-border-red-200 tw-rounded tw-p-3 tw-w-full tw-ml-2 tw-relative">
                                                <div
                                                    class="tw-text-gray-500 tw-text-xs tw-absolute tw-top-1 tw-right-1">
                                                    {{ order.return_requested_at ?
                                                        formatDateNoPrefix(order.return_requested_at) :
                                                        formatDateNoPrefix(order.updated_at) }}
                                                </div>
                                                <div class="tw-text-red-700 tw-font-bold tw-text-left tw-text-sm">
                                                    Yêu cầu hoàn hàng đã bị từ chối
                                                </div>
                                                <div v-if="order.reject_reason"
                                                    class="tw-text-red-600 tw-text-xs tw-break-words tw-whitespace-pre-line tw-text-left tw-mt-1">
                                                    <span class="tw-font-semibold">Lý do từ chối:</span> {{
                                                        order.reject_reason }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Return action buttons -->
                                        <div class="tw-flex tw-gap-2 tw-justify-end">
                                            <button v-if="canRequestReturn(order)"
                                                @click.stop="handleRequestReturn(order.id)"
                                                class="tw-bg-orange-500 tw-text-white tw-px-3 tw-py-1 tw-rounded tw-text-sm hover:tw-bg-orange-600">
                                                Yêu cầu hoàn hàng
                                            </button>
                                            <button
                                                v-if="order && (order.status === 'completed' || order.status === 'cancelled')"
                                                @click.stop="handleReorder(order.id)"
                                                class="tw-bg-blue-600 tw-text-white tw-px-3 tw-py-1 tw-rounded tw-text-sm hover:tw-bg-blue-700">
                                                Mua lại
                                            </button>
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
        <div class="md:tw-hidden tw-space-y-4">
            <div v-for="order in orders" :key="order.id"
                class="tw-bg-white tw-border tw-rounded-lg tw-p-4 tw-space-y-3">
                <div class="tw-flex tw-justify-between tw-items-start">
                    <div>
                        <span class="tw-font-medium">#{{ order.id }}</span>
                        <p class="tw-text-gray-500 tw-text-sm">{{ formatDate(order.created_at) }}</p>
                    </div>
                    <button @click="openOrderDetail(order)"
                        class="tw-bg-blue-600 tw-text-white tw-rounded tw-px-2 tw-py-1 tw-text-xs hover:tw-bg-blue-700">
                        Chi tiết
                    </button>
                </div>

                <div class="tw-flex tw-items-center tw-gap-2">
                    <img :src="order.order_details[0]?.variant?.product?.main_image?.image_path"
                        class="tw-w-12 tw-h-12 tw-object-cover tw-rounded"
                        :alt="order.order_details[0]?.variant?.product?.name" />
                    <div>
                        <p class="tw-font-medium tw-text-sm">{{ order.order_details[0]?.variant?.product?.name }}</p>
                        <p class="tw-text-gray-500 tw-text-xs">
                            {{ order.order_details.length }} sản phẩm
                        </p>
                    </div>
                </div>

                <div class="tw-flex tw-justify-between tw-items-center">
                    <span class="tw-font-medium">{{ formatPrice(order.final_price) }}đ</span>
                    <div class="tw-flex tw-flex-col tw-items-end tw-gap-1">
                        <span :class="badgeClass(order.payment_status)">
                            {{ getPaymentStatusLabel(order.payment_status) }}
                        </span>
                        <span :class="badgeClass(order.status)">
                            {{ getStatusLabel(order.status) }}
                        </span>
                    </div>
                </div>
                <!-- Thêm nút Yêu cầu hoàn hàng ở mobile -->
                <div class="tw-mt-2 tw-text-right">
                    <button v-if="canRequestReturn(order)" @click.stop="handleRequestReturn(order.id)"
                        class="tw-bg-orange-500 tw-text-white tw-px-3 tw-py-1 tw-rounded tw-text-sm hover:tw-bg-orange-600">
                        Yêu cầu hoàn hàng
                    </button>
                </div>
            </div>
        </div>

        <div v-if="orders.length === 0" class="tw-text-center tw-py-12">
            <svg class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="tw-mt-2 tw-text-sm tw-font-medium tw-text-gray-900">Không có đơn hàng</h3>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">Bạn chưa có đơn hàng nào.</p>
        </div>

        <div v-if="showModal"
            class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50">
            <div
                class="tw-bg-white tw-rounded-lg tw-p-6 tw-w-full tw-max-w-2xl tw-max-h-[90vh] tw-overflow-y-auto tw-shadow-lg">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
                    <h3 class="tw-text-xl tw-font-bold">Chi tiết đơn hàng #{{ selectedOrder?.id }}</h3>
                    <button @click="closeModal" class="tw-text-gray-500 hover:tw-text-gray-700">
                        <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="selectedOrder" class="tw-space-y-8">
                    <div v-if="showCancelWarning(selectedOrder)"
                        class="tw-bg-yellow-100 tw-text-yellow-800 tw-p-4 tw-rounded tw-mb-4 tw-flex tw-items-center tw-gap-2">
                        <svg class="tw-w-6 tw-h-6 tw-text-yellow-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M12 8v.01" />
                        </svg>
                        <span>Đơn hàng đã quá thời hạn hủy (24 giờ), vì vậy bạn vui lòng chờ và nhận hàng.</span>
                    </div>

                    <div v-if="selectedOrder.return_requested_at"
                        class="tw-mt-4 tw-p-4 tw-bg-orange-50 tw-rounded-lg tw-border-l-4 tw-border-orange-400">
                        <div class="tw-flex tw-items-center">
                            <div
                                class="tw-w-8 tw-h-8 tw-rounded-full tw-bg-orange-100 tw-flex tw-items-center tw-justify-center tw-mr-2 tw-mt-0.5">
                                <svg class="tw-w-5 tw-h-5 tw-text-orange-700" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 17l4 4 4-4m0-5V3m-8 9v6a2 2 0 002 2h4a2 2 0 002-2v-6" />
                                </svg>
                            </div>
                            <div>
                                <p>
                                    <span class="tw-font-medium tw-text-orange-600 tw-text-sm md:tw-text-base">Bạn đã
                                        gửi yêu cầu hoàn hàng vào: </span>
                                    <span class="tw-text-orange-600 tw-text-sm md:tw-text-base">{{
                                        formatDate(selectedOrder.return_requested_at) }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="tw-border-b tw-pb-6">
                        <h4 class="tw-font-semibold tw-mb-4">Trạng thái đơn hàng</h4>
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <div class="tw-flex tw-flex-col tw-items-center tw-relative">
                                <div
                                    class="tw-w-10 tw-h-10 tw-rounded-full tw-bg-green-500 tw-flex tw-items-center tw-justify-center tw-text-white">
                                    <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="tw-text-sm tw-mt-2">Đặt hàng</span>
                                <span class="tw-text-xs tw-text-gray-500">{{ formatDate(selectedOrder.created_at)
                                    }}</span>
                            </div>
                            <div class="tw-flex-1 tw-h-0.5 tw-bg-gray-200 tw-mx-4"></div>
                            <div class="tw-flex tw-flex-col tw-items-center tw-relative">
                                <div :class="[
                                    'tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white',
                                    selectedOrder.status === 'pending' ? 'tw-bg-yellow-500' : 'tw-bg-green-500'
                                ]">
                                    <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <span class="tw-text-sm tw-mt-2">Xác nhận</span>
                                <span class="tw-text-xs tw-text-gray-500">
                                    {{ selectedOrder.status === 'pending' ? 'Đang chờ' :
                                        formatDate(selectedOrder.updated_at) }}</span>
                            </div>
                            <div class="tw-flex-1 tw-h-0.5 tw-bg-gray-200 tw-mx-4"></div>
                            <div class="tw-flex tw-flex-col tw-items-center tw-relative">
                                <div :class="[
                                    'tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white',
                                    ['shipping', 'completed'].includes(selectedOrder.status) ? 'tw-bg-green-500' : 'tw-bg-gray-300'
                                ]">
                                    <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                    </svg>
                                </div>
                                <span class="tw-text-sm tw-mt-2">Giao hàng</span>
                                <span class="tw-text-xs tw-text-gray-500">{{ ['shipping',
                                    'completed'].includes(selectedOrder.status) ? 'Đang giao' : 'Chờ xử lý' }}</span>
                            </div>
                            <div class="tw-flex-1 tw-h-0.5 tw-bg-gray-200 tw-mx-4"></div>
                            <div class="tw-flex tw-flex-col tw-items-center tw-relative">
                                <div :class="[
                                    'tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white',
                                    selectedOrder.status === 'completed' ? 'tw-bg-green-500' : 'tw-bg-gray-300'
                                ]">
                                    <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="tw-text-sm tw-mt-2">Hoàn thành</span>
                                <span class="tw-text-xs tw-text-gray-500">
                                    {{ selectedOrder.status === 'completed' ? 'Đã nhậnhàng' : 'Chờ xử lý' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="tw-grid tw-grid-cols-2 tw-gap-6">
                        <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                            <h4 class="tw-font-semibold tw-mb-4">Thông tin giao hàng</h4>
                            <div class="tw-space-y-2">
                                <p class="tw-flex tw-items-center">
                                    <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ selectedOrder.address?.full_name }}
                                </p>
                                <p class="tw-flex tw-items-center">
                                    <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    {{ selectedOrder.address?.phone }}
                                </p>
                                <p class="tw-flex tw-items-start">
                                    <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2 tw-mt-1" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ getFullAddress(selectedOrder.address) }}
                                </p>
                            </div>
                        </div>

                        <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                            <h4 class="tw-font-semibold tw-mb-4">Thông tin thanh toán</h4>
                            <div class="tw-space-y-2">
                                <p class="tw-flex tw-items-center">
                                    <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Phương thức: {{ getPaymentMethodLabel(selectedOrder.payment_method) }}
                                </p>
                                <p class="tw-flex tw-items-center">
                                    <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Trạng thái:
                                    <span :class="badgeClass(selectedOrder.payment_status)" class="tw-ml-2">
                                        {{ getPaymentStatusLabel(selectedOrder.payment_status) }}
                                    </span>
                                </p>
                                <p class="tw-flex tw-items-center">
                                    <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Mã tra cứu:
                                    <span class="tw-ml-2 tw-font-medium tw-text-blue-600">{{
                                        selectedOrder?.tracking_code || 'Chưa có mã' }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="tw-font-semibold tw-mb-4">Sản phẩm</h4>
                        <div class="tw-space-y-4">
                            <div v-for="item in selectedOrder.order_details" :key="item.id"
                                class="tw-flex tw-items-center tw-gap-4 tw-p-4 tw-bg-gray-50 tw-rounded">
                                <img :src="item.variant?.product?.main_image?.image_path"
                                    class="tw-w-20 tw-h-20 tw-object-cover tw-rounded"
                                    :alt="item.variant?.product?.name" />
                                <div class="tw-flex-1">
                                    <h5 class="tw-font-medium">{{ item.variant?.product?.name }}</h5>
                                    <p class="tw-text-gray-600">Size: {{ item.variant?.size }}</p>
                                    <p class="tw-text-gray-600">Số lượng: {{ item.quantity }}</p>
                                </div>
                                <div class="tw-text-right">
                                    <p class="tw-font-medium">{{ formatPrice(item.price) }}đ</p>
                                    <p class="tw-text-gray-600">Tổng: {{ formatPrice(item.total_price) }}đ</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tw-border-t tw-pt-4">
                        <div class="tw-space-y-2">
                            <div class="tw-flex tw-justify-between">
                                <span class="tw-text-gray-600">Tổng tiền hàng</span>
                                <span>{{ formatPrice(selectedOrder.total_price) }}đ</span>
                            </div>
                            <div class="tw-flex tw-justify-between">
                                <span class="tw-text-gray-600">Phí vận chuyển</span>
                                <span>{{ formatPrice(selectedOrder.shipping_fee) }}đ</span>
                            </div>
                            <div class="tw-flex tw-justify-between">
                                <span class="tw-text-gray-600">Giảm giá</span>
                                <span>-{{ formatPrice(selectedOrder.discount_price) }}đ</span>
                            </div>
                            <div class="tw-flex tw-justify-between tw-font-bold tw-text-lg tw-border-t tw-pt-2">
                                <span>Thành tiền</span>
                                <span>{{ formatPrice(selectedOrder.final_price) }}đ</span>
                            </div>
                        </div>
                        <div class="tw-mt-4 tw-text-right tw-space-x-2">
                            <template v-if="selectedOrder">
                                <div v-if="selectedOrder.return_status === 'requested'"
                                    class="tw-flex tw-items-start tw-mb-2">
                                    <svg class="tw-w-7 tw-h-7 tw-text-yellow-500 tw-mt-1" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                                    </svg>
                                    <div
                                        class="tw-bg-yellow-50 tw-border tw-border-yellow-200 tw-rounded tw-p-4 tw-w-full tw-ml-2 tw-relative">
                                        <div class="tw-text-gray-500 tw-text-xs tw-absolute tw-top-2 tw-right-2">
                                            {{ selectedOrder.return_requested_at ?
                                                formatDateNoPrefix(selectedOrder.return_requested_at) :
                                                formatDateNoPrefix(selectedOrder.updated_at) }}
                                        </div>
                                        <div class="tw-text-yellow-700 tw-font-medium tw-text-left">
                                            Bạn đã gửi yêu cầu hoàn hàng. Vui lòng chờ xác nhận.
                                        </div>
                                    </div>
                                </div>
                                <div v-else-if="selectedOrder.return_status === 'approved'"
                                    class="tw-flex tw-items-start tw-mb-2">
                                    <svg class="tw-w-7 tw-h-7 tw-text-green-500 tw-mt-1" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                                    </svg>
                                    <div
                                        class="tw-bg-green-50 tw-border tw-border-green-200 tw-rounded tw-p-4 tw-w-full tw-ml-2 tw-relative">
                                        <div class="tw-text-gray-500 tw-text-xs tw-absolute tw-top-2 tw-right-2">
                                            {{ selectedOrder.return_requested_at ?
                                                formatDateNoPrefix(selectedOrder.return_requested_at) :
                                                formatDateNoPrefix(selectedOrder.updated_at) }}
                                        </div>
                                        <div class="tw-text-green-700 tw-font-bold tw-text-left">
                                            Yêu cầu hoàn hàng đã được xác nhận.
                                        </div>
                                    </div>
                                </div>
                                <div v-else-if="selectedOrder.return_status === 'rejected'"
                                    class="tw-flex tw-items-start tw-mb-2">
                                    <svg class="tw-w-7 tw-h-7 tw-text-red-500 tw-mt-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                                    </svg>
                                    <div
                                        class="tw-bg-red-50 tw-border tw-border-red-200 tw-rounded tw-p-4 tw-w-full tw-ml-2 tw-relative">
                                        <div class="tw-text-gray-500 tw-text-xs tw-absolute tw-top-2 tw-right-2">
                                            {{ selectedOrder.return_requested_at ?
                                                formatDateNoPrefix(selectedOrder.return_requested_at) :
                                                formatDateNoPrefix(selectedOrder.updated_at) }}
                                        </div>
                                        <div class="tw-text-red-700 tw-font-bold tw-text-left">
                                            Yêu cầu hoàn hàng đã bị từ chối
                                        </div>
                                        <div v-if="selectedOrder.reject_reason"
                                            class="tw-text-red-600 tw-text-sm tw-break-words tw-whitespace-pre-line tw-text-left tw-mt-1">
                                            <span class="tw-font-semibold">Lý do từ chối:</span> {{
                                                selectedOrder.reject_reason }}
                                        </div>
                                    </div>
                                </div>
                                <div v-if="selectedOrder.status === 'cancelled' && selectedOrder.cancel_reason"
                                    class="tw-mt-4 tw-p-4 tw-bg-red-50 tw-rounded-lg tw-border-l-4 tw-border-red-400 tw-relative">
                                    <div class="tw-text-gray-500 tw-text-xs tw-absolute tw-top-2 tw-right-2">
                                        {{ formatDateNoPrefix(selectedOrder.updated_at) }}
                                    </div>
                                    <div class="tw-flex tw-items-center">
                                        <div
                                            class="tw-w-8 tw-h-8 tw-rounded-full tw-bg-red-100 tw-flex tw-items-center tw-justify-center tw-mr-2 tw-mt-0.5">
                                            <svg class="tw-w-5 tw-h-5 tw-text-red-700" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p>
                                                <span
                                                    class="tw-font-medium tw-text-red-600 tw-text-sm md:tw-text-base">Lý
                                                    do
                                                    hủy đơn hàng: </span>
                                                <span class="tw-text-red-600 tw-text-sm md:tw-text-base">{{
                                                    selectedOrder.cancel_reason }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <button v-if="canCancelOrder(selectedOrder)" @click="showCancelReasonModal = true"
                                    class="tw-bg-red-600 tw-text-white tw-px-4 tw-py-2 tw-rounded hover:tw-bg-red-700">
                                    Hủy đơn hàng
                                </button>
                                <button
                                    v-if="selectedOrder && (selectedOrder.status === 'completed' || selectedOrder.status === 'cancelled')"
                                    @click="handleReorder(selectedOrder.id)"
                                    class="tw-bg-blue-600 tw-text-white tw-px-4 tw-py-2 tw-rounded hover:tw-bg-blue-700">
                                    Mua lại đơn hàng
                                </button>
                                <button v-if="canRequestReturn(selectedOrder)"
                                    @click="handleRequestReturn(selectedOrder.id)"
                                    class="tw-bg-orange-500 tw-text-white tw-px-4 tw-py-2 tw-rounded hover:tw-bg-orange-600">
                                    Yêu cầu hoàn hàng
                                </button>
                            </template>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Chọn lý do hủy đơn hàng -->
        <div v-if="showCancelReasonModal"
            class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50">
            <div class="tw-bg-white tw-rounded-lg tw-p-6 tw-w-full tw-max-w-md tw-shadow-lg">
                <h3 class="tw-text-lg tw-font-bold tw-mb-4">Lý Do Hủy</h3>
                <div class="tw-mb-4 tw-text-sm tw-bg-yellow-50 tw-p-3 tw-rounded tw-text-yellow-800">
                    Bạn có thể cập nhật thông tin nhận hàng một lần trước khi hủy. Nếu xác nhận hủy, toàn bộ đơn hàng sẽ
                    bị hủy.
                    Vui lòng chọn lý do hủy phù hợp nhé!
                </div>
                <div class="tw-space-y-2 tw-mb-4">
                    <div v-for="reason in cancelReasons" :key="reason.value" class="tw-flex tw-items-center">
                        <input type="radio" :id="'cancel-reason-' + reason.value" v-model="cancelReason"
                            :value="reason.value" class="tw-mr-2" />
                        <label :for="'cancel-reason-' + reason.value" class="tw-cursor-pointer">{{ reason.label
                        }}</label>
                    </div>
                    <input v-if="cancelReason === 'other'" v-model="cancelReasonOther" type="text"
                        class="tw-mt-2 tw-w-full tw-p-2 tw-border tw-rounded" placeholder="Vui lòng ghi rõ lý do..." />
                </div>
                <div class="tw-flex tw-justify-end tw-gap-2">
                    <button @click="showCancelReasonModal = false"
                        class="tw-bg-gray-200 tw-px-4 tw-py-2 tw-rounded">Không phải
                        bây giờ</button>
                    <button @click="confirmCancelOrder"
                        class="tw-bg-red-600 tw-text-white tw-px-4 tw-py-2 tw-rounded hover:tw-bg-red-700">Hủy đơn
                        hàng</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useOrder } from '~/composables/useOrder'

useHead({
    title: 'Đơn hàng của tôi',
    meta: [
        {
            name: 'description',
            content: 'Quản lý đơn hàng của bạn',
        },
    ],
})

const orderService = useOrder()
const orders = ref([])
const showModal = ref(false)
const selectedOrder = ref(null)
const selectedStatus = ref('')
const selectedDate = ref('')
const expandedOrderId = ref(null)
const showCancelReasonModal = ref(false)
const cancelReason = ref('')
const cancelReasonOther = ref('')

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
    // console.log('Filters changed:', filters)
}

const openOrderDetail = (order) => {
    selectedOrder.value = order
    console.log('Selected Order:', order)
    console.log('Tracking Code:', order.tracking_code)
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
    let base = 'tw-inline-flex tw-items-center tw-h-5 tw-p-2 tw-mt-2 tw-text-sm tw-align-middle ';
    switch (status) {
        case 'pending':
            return base + 'tw-bg-yellow-100 tw-text-yellow-700 tw-rounded-full tw-text-[5px]';
        case 'processing':
            return base + 'tw-bg-blue-100 tw-text-blue-700 tw-rounded-full tw-text-[5px]';
        case 'shipping':
            return base + 'tw-bg-purple-100 tw-text-purple-700 tw-rounded-full tw-text-[5px]';
        case 'completed':
            return base + 'tw-bg-green-100 tw-text-green-700 tw-rounded-full tw-text-[5px]';
        case 'cancelled':
            return base + 'tw-bg-red-100 tw-text-red-700 tw-rounded-full tw-text-[5px]';
        case 'paid':
            return base + 'tw-bg-green-100 tw-text-green-700 tw-rounded-full tw-text-[5px]';
        case 'failed':
            return base + 'tw-bg-red-100 tw-text-red-700 tw-rounded-full tw-text-[5px]';
        case 'canceled':
            return base + 'tw-bg-red-100 tw-text-red-700 tw-rounded-full tw-text-[5px]';
        case 'refunded':
            return base + 'tw-bg-gray-100 tw-text-gray-700 tw-rounded-full tw-text-[5px]';
        default:
            return base + 'tw-bg-gray-100 tw-text-gray-700 tw-rounded-full tw-text-[5px]';
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
    const notyf = useNuxtApp().$notyf
    const reasonLabel = cancelReason.value === 'other'
        ? cancelReasonOther.value.trim()
        : cancelReasons.find(r => r.value === cancelReason.value)?.label || ''

    if (!reasonLabel) {
        notyf.error('Vui lòng chọn hoặc nhập lý do hủy đơn hàng')
        return
    }

    try {
        await orderService.cancelOrder(selectedOrder.value.id, reasonLabel)
        showCancelReasonModal.value = false
        closeModal()
        fetchOrders()
        notyf.success('Hủy đơn hàng thành công!')
        cancelReason.value = ''
        cancelReasonOther.value = ''
    } catch (err) {
        notyf.error(err?.response?.data?.message || err.message || 'Hủy đơn hàng thất bại')
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
    if (!['cancelled', 'completed'].includes(order.status)) return false
    if (order.return_status === 'requested' || order.return_status === 'approved' || order.return_status === 'rejected') return false
    if (!['vnpay', 'momo', 'paypal'].includes(order.payment_method)) return false
    const completedOrCancelledAt = new Date(order.updated_at)
    const now = new Date()
    const diffDays = (now - completedOrCancelledAt) / (1000 * 60 * 60 * 24)
    return diffDays <= 3
}

const handleRequestReturn = async (orderId) => {
    const notyf = useNuxtApp().$notyf
    const order = orders.value.find(order => order.id === orderId)
    if (!order) {
        notyf.error('Không tìm thấy đơn hàng!');
        return;
    }
    if (!['cancelled', 'completed'].includes(order.status)) {
        notyf.error('Chỉ có thể hoàn hàng cho đơn đã hủy hoặc đã hoàn thành');
        return;
    }
    if (order.return_requested_at) {
        notyf.error('Bạn đã gửi yêu cầu hoàn hàng cho đơn này rồi');
        return;
    }
    if (order.payment_method === 'cod') {
        notyf.error('Đơn thanh toán COD không hỗ trợ hoàn hàng');
        return;
    }
    const completedOrCancelledAt = new Date(order.updated_at);
    const now = new Date();
    const diffDays = (now - completedOrCancelledAt) / (1000 * 60 * 60 * 24);
    if (diffDays > 3) {
        notyf.error('Chỉ có thể hoàn hàng trong vòng 3 ngày kể từ khi đơn hoàn thành hoặc bị hủy');
        return;
    }

    try {
        await orderService.requestReturn(orderId)
        const orderIndex = orders.value.findIndex(order => order.id === orderId)
        if (orderIndex !== -1) {
            orders.value[orderIndex].return_status = 'requested'
            orders.value[orderIndex].return_requested_at = new Date().toISOString()
        }
        notyf.success('Yêu cầu hoàn hàng đã được gửi!')
        fetchOrders()
    } catch (err) {
        notyf.error(err?.response?.data?.message || err.message || 'Gửi yêu cầu hoàn hàng thất bại')
    }
}

const formatDateNoPrefix = (date) => {
    if (!date) return ''
    const d = new Date(date)
    return d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }) + ' ' + d.toLocaleDateString('vi-VN', { day: 'numeric', month: 'long', year: 'numeric' })
}

onMounted(() => {
    fetchOrders()
})
</script>