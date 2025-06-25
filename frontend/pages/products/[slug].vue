<template>
  <ClientOnly>
    <div class="tw-container tw-mx-auto tw-px-4 tw-py-8">
      <!-- Loading state -->
      <div v-if="pending" class="tw-flex tw-justify-center tw-items-center tw-min-h-[400px]">
        <div class="tw-text-xl">Đang tải...</div>
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="tw-flex tw-justify-center tw-items-center tw-min-h-[400px]">
        <div class="tw-text-red-500">{{ error }}</div>
      </div>

      <!-- Product content -->
      <div v-else-if="data">
        <!-- Breadcrumb -->
        <div class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-text-gray-500 tw-mb-6">
          <NuxtLink to="/" class="hover:tw-text-[#81AACC]">Trang chủ</NuxtLink>
          <span>/</span>
          <NuxtLink to="/products" class="hover:tw-text-[#81AACC]">Sản phẩm</NuxtLink>
          <span>/</span>
          <span class="tw-text-gray-900">{{ data.name }}</span>
        </div>

        <!-- Product Section -->
        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-12">
          <!-- Product Images -->
          <div class="tw-space-y-4">
            <!-- Main Image -->
            <div class="tw-aspect-square tw-rounded-lg tw-overflow-hidden tw-bg-gray-100 tw-cursor-zoom-in"
              @click="showZoomModal = true">
              <img :src="mainImage" :alt="data.name" class="tw-w-full tw-h-full tw-object-cover" />
            </div>
            <!-- Thumbnails -->
            <div class="tw-grid tw-grid-cols-4 tw-gap-4">
              <button v-for="(image, index) in productImages" :key="index" @click="mainImage = image"
                class="tw-aspect-square tw-rounded-lg tw-overflow-hidden tw-bg-gray-100 hover:tw-ring-2 hover:tw-ring-[#81AACC]">
                <img :src="image" :alt="data.name" class="tw-w-full tw-h-full tw-object-cover" />
              </button>
            </div>
          </div>

          <!-- Product Info -->
          <div class="tw-space-y-6">
            <div>
              <h1 class="tw-text-2xl tw-font-bold tw-mb-2">{{ data.name }}</h1>
              <p class="tw-text-gray-500">Danh mục: <NuxtLink :to="'/category/' + data.category?.slug"
                  v-if="data.category" class="tw-text-[#81AACC] hover:tw-underline">{{ data.category.name }}</NuxtLink>
              </p>
            </div>

            <!-- Price -->
            <div class="tw-space-y-2">
              <div class="tw-flex tw-items-center tw-gap-4">
                <span class="tw-text-2xl tw-font-bold tw-text-[#81AACC]">{{ formatPrice(data.discount_price ||
                  data.price) }}</span>
                <span v-if="data.discount_price && data.discount_price < data.price"
                  class="tw-text-lg tw-text-gray-400 tw-line-through">
                  {{ formatPrice(data.price) }}
                </span>
                <span v-if="data.discount_percentage"
                  class="tw-bg-red-500 tw-text-white tw-px-2 tw-py-1 tw-rounded-full tw-text-sm">
                  -{{ data.discount_percentage }}%
                </span>
              </div>
              <p class="tw-text-sm tw-text-gray-500">Giá đã bao gồm VAT</p>
            </div>

            <!-- Variants -->
            <div class="tw-space-y-4" v-if="data.variants && data.variants.length > 0">
              <!-- Size -->
              <div v-if="sizes.length > 0">
                <h3 class="tw-font-medium tw-mb-2">Kích thước</h3>
                <div class="tw-flex tw-gap-2">
                  <button v-for="size in sizes" :key="size" @click="selectedSize = size" :class="[
                    'tw-px-4 tw-py-2 tw-border tw-rounded-md tw-transition-colors',
                    selectedSize === size
                      ? 'tw-bg-[#81AACC] tw-text-white tw-border-[#81AACC]'
                      : 'tw-border-gray-300 hover:tw-border-[#81AACC]'
                  ]">
                    {{ size }}
                  </button>
                </div>
              </div>

              <!-- Color -->
              <div v-if="colors.length > 0">
                <h3 class="tw-font-medium tw-mb-2">Màu sắc</h3>
                <div class="tw-flex tw-gap-2">
                  <button v-for="color in colors" :key="color.name" @click="selectedColor = color" :class="[
                    'tw-w-10 tw-h-10 tw-rounded-full tw-border-2 tw-transition-colors',
                    selectedColor === color
                      ? 'tw-border-[#81AACC]'
                      : 'tw-border-gray-300 hover:tw-border-[#81AACC]'
                  ]" :style="{ backgroundColor: color.code }" :title="color.name">
                  </button>
                </div>
              </div>
            </div>

            <!-- Quantity -->
            <div>
              <h3 class="tw-font-medium tw-mb-2">Số lượng</h3>
              <div class="tw-flex tw-items-center tw-gap-4">
                <div class="tw-flex tw-items-center tw-border tw-rounded-md">
                  <button @click="quantity > 1 && quantity--" class="tw-px-3 tw-py-2 hover:tw-bg-gray-100">-</button>
                  <input type="number" v-model="quantity" min="1" :max="selectedVariantStock"
                    class="tw-w-16 tw-text-center tw-border-x tw-py-2" />
                  <button @click="quantity < selectedVariantStock && quantity++"
                    class="tw-px-3 tw-py-2 hover:tw-bg-gray-100">+</button>
                </div>
                <span class="tw-text-sm tw-text-gray-500">Còn lại: {{ selectedVariantStock }} sản phẩm</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="tw-flex tw-gap-4">
              <button
                class="tw-flex-1 tw-bg-[#81AACC] tw-text-white tw-py-3 tw-rounded-md hover:tw-bg-[#6B8BA3] tw-transition-colors"
                @click="addToCart">
                Thêm vào giỏ hàng
              </button>
            </div>

            <!-- Status -->
            <div class="tw-flex tw-items-center tw-gap-2 tw-text-sm">
              <span :class="[
                'tw-font-medium',
                selectedVariantStock > 0 ? 'tw-text-green-600' : 'tw-text-red-600'
              ]">
                {{ selectedVariantStock > 0 ? 'Còn hàng' : 'Hết hàng' }}
              </span>
              <span class="tw-text-gray-500">|</span>
              <span class="tw-text-gray-500">Giao hàng trong 1-3 ngày</span>
            </div>
          </div>
        </div>

        <!-- Description & Reviews -->
        <div class="tw-border-t tw-pt-8">
          <div class="tw-flex tw-gap-8 tw-mb-8">
            <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
              'tw-px-4 tw-py-2 tw-font-medium tw-border-b-2 tw-transition-colors',
              activeTab === tab.id
                ? 'tw-border-[#81AACC] tw-text-[#81AACC]'
                : 'tw-border-transparent hover:tw-border-gray-300'
            ]">
              {{ tab.name }}
            </button>
          </div>

          <!-- Description -->
          <div v-if="activeTab === 'description'" class="tw-prose tw-max-w-none">
            <h3 class="tw-text-xl tw-font-bold tw-mb-4">Mô tả sản phẩm</h3>
            <div v-html="data.description"></div>
          </div>

          <!-- Reviews -->
          <div v-if="activeTab === 'reviews'" class="tw-space-y-8">
            <!-- Review stats section (improved) -->
            <div class="tw-bg-gray-50 tw-rounded-lg tw-p-6 tw-shadow-sm tw-transition-all hover:tw-shadow-md">
              <div class="tw-flex tw-flex-col md:tw-flex-row tw-items-center tw-gap-8">
                <div class="tw-text-center tw-rounded-lg tw-p-4 tw-min-w-[150px]">
                  <div class="tw-text-5xl tw-font-bold tw-text-[#81AACC] tw-mb-2">{{ reviewStats.average }}</div>
                  <div class="tw-text-yellow-400 tw-flex tw-gap-1 tw-justify-center tw-mb-2">
                    <i v-for="n in 5" :key="n"
                      :class="n <= Math.round(reviewStats.average) ? 'bi bi-star-fill' : (n <= reviewStats.average + 0.5 ? 'bi bi-star-half' : 'bi bi-star')"
                      class="tw-text-xl"></i>
                  </div>
                  <div class="tw-text-sm tw-text-gray-500 tw-font-medium">{{ reviewStats.total }} đánh giá</div>
                </div>
                <div class="tw-flex-1">
                  <h3 class="tw-text-lg tw-font-medium tw-mb-4 tw-text-center md:tw-text-left">Phân bối đánh giá</h3>
                  <div v-for="rating in reviewStats.distribution" :key="rating.stars"
                    class="tw-flex tw-items-center tw-gap-3 tw-mb-3 tw-group">
                    <span class="tw-w-16 tw-font-medium tw-flex tw-items-center tw-gap-1">
                      {{ rating.stars }} <i class="bi bi-star-fill tw-text-yellow-400"></i>
                    </span>
                    <div class="tw-flex-1 tw-h-3 tw-bg-gray-200 tw-rounded-full tw-overflow-hidden">
                      <div
                        class="tw-h-full tw-bg-yellow-400 tw-rounded-full tw-transition-all tw-duration-500 group-hover:tw-bg-yellow-500"
                        :style="{ width: rating.percentage + '%' }">
                      </div>
                    </div>
                    <span class="tw-w-16 tw-text-right tw-font-medium">{{ rating.percentage }}%</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Review Form -->
            <div id="review-form" v-if="showReviewForm"
              class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-100 tw-mb-8 tw-transition-all hover:tw-shadow-md">
              <h3 class="tw-text-xl tw-font-semibold tw-mb-6 tw-flex tw-items-center tw-gap-2">
                <i class="bi bi-pencil-square tw-text-[#81AACC]"></i>
                {{ editingReviewId ? 'Chỉnh sửa đánh giá' : 'Viết đánh giá' }}
              </h3>

              <div v-if="!isAuthenticated" class="tw-text-center tw-py-6 tw-bg-gray-50 tw-rounded-lg">
                <i class="bi bi-person-lock tw-text-3xl tw-text-gray-400 tw-mb-2 tw-block"></i>
                <p class="tw-mb-4 tw-text-gray-600">Vui lòng đăng nhập để đánh giá sản phẩm</p>
                <NuxtLink to="/login"
                  class="tw-bg-[#81AACC] tw-text-white tw-px-6 tw-py-2 tw-rounded-md tw-inline-block tw-font-medium tw-transition-colors hover:tw-bg-[#6B8BA3]">
                  <i class="bi bi-box-arrow-in-right tw-mr-1"></i> Đăng nhập
                </NuxtLink>
              </div>

              <form v-else @submit.prevent="submitReview" class="tw-space-y-6">
                <!-- Rating -->
                <div>
                  <label class="tw-block tw-mb-3 tw-font-medium tw-text-gray-700">Đánh giá của bạn</label>
                  <div class="tw-flex tw-text-3xl tw-text-gray-300 tw-mb-2">
                    <button v-for="star in 5" :key="star" type="button" @click="reviewForm.rating = star"
                      class="tw-focus:outline-none tw-transition-colors tw-duration-200 hover:tw-text-yellow-400"
                      :class="star <= reviewForm.rating ? 'tw-text-yellow-400' : ''">
                      <i class="bi bi-star-fill"></i>
                    </button>
                  </div>
                  <div class="tw-text-sm tw-text-gray-500">
                    {{ ['Chọn đánh giá', 'Rất tệ', 'Tệ', 'Bình thường', 'Tốt', 'Rất tốt'][reviewForm.rating] }}
                  </div>
                </div>

                <!-- Content -->
                <div>
                  <label for="review-content" class="tw-block tw-mb-3 tw-font-medium tw-text-gray-700">Nội dung đánh
                    giá</label>
                  <textarea id="review-content" v-model="reviewForm.content" rows="4"
                    class="tw-w-full tw-border tw-border-gray-300 tw-rounded-lg tw-p-3 tw-transition-colors focus:tw-border-[#81AACC] focus:tw-ring-2 focus:tw-ring-[#81AACC]/20 focus:tw-outline-none"
                    placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này" required></textarea>
                </div>

                <!-- Image Upload -->
                <div>
                  <label class="tw-block tw-mb-3 tw-font-medium tw-text-gray-700">Hình ảnh (tùy chọn)</label>
                  <div
                    class="tw-border-2 tw-border-dashed tw-border-gray-300 tw-rounded-lg tw-p-4 tw-text-center tw-transition-colors hover:tw-border-[#81AACC] tw-cursor-pointer tw-relative">
                    <input type="file" @change="handleImageUpload" accept="image/*" multiple
                      class="tw-absolute tw-inset-0 tw-opacity-0 tw-cursor-pointer">
                    <i class="bi bi-cloud-arrow-up tw-text-3xl tw-text-gray-400 tw-mb-2"></i>
                    <p class="tw-text-gray-500">Kéo thả hoặc nhấp để tải lên hình ảnh</p>
                    <p class="tw-text-xs tw-text-gray-400 tw-mt-1">Hỗ trợ JPG, PNG, GIF</p>
                  </div>

                  <!-- Image Previews -->
                  <div v-if="previewImages.length > 0" class="tw-flex tw-flex-wrap tw-gap-3 tw-mt-4">
                    <div v-for="(image, index) in previewImages" :key="index"
                      class="tw-relative tw-w-24 tw-h-24 tw-group tw-overflow-hidden tw-rounded-lg tw-shadow-sm">
                      <img :src="image.url"
                        class="tw-w-full tw-h-full tw-object-cover tw-transition-transform tw-duration-300 group-hover:tw-scale-110">
                      <div
                        class="tw-absolute tw-inset-0 tw-bg-black tw-bg-opacity-0 group-hover:tw-bg-opacity-30 tw-transition-all tw-duration-300">
                      </div>
                      <button type="button" @click="removeImage(index)"
                        class="tw-absolute tw-top-1 tw-right-1 tw-bg-red-500 tw-text-white tw-rounded-full tw-w-6 tw-h-6 tw-flex tw-items-center tw-justify-center tw-opacity-0 group-hover:tw-opacity-100 tw-transition-opacity tw-duration-300">
                        <i class="bi bi-x"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Submit Buttons -->
                <div class="tw-flex tw-gap-3 tw-pt-2">
                  <button type="submit"
                    class="tw-bg-[#81AACC] tw-text-white tw-px-6 tw-py-3 tw-rounded-md tw-font-medium tw-transition-colors hover:tw-bg-[#6B8BA3] tw-flex tw-items-center tw-justify-center tw-min-w-[150px]"
                    :disabled="isSubmitting">
                    <span v-if="isSubmitting">
                      <i class="bi bi-arrow-repeat tw-animate-spin tw-inline-block tw-mr-2"></i> Đang xử lý...
                    </span>
                    <span v-else>
                      <i class="bi bi-send tw-mr-2"></i> {{ editingReviewId ? 'Cập nhật đánh giá' : 'Gửi đánh giá' }}
                    </span>
                  </button>

                  <button type="button" @click="cancelEdit"
                    class="tw-bg-gray-200 tw-text-gray-700 tw-px-6 tw-py-3 tw-rounded-md tw-font-medium tw-transition-colors hover:tw-bg-gray-300">
                    <i class="bi bi-x-lg tw-mr-2"></i> {{ editingReviewId ? 'Hủy' : 'Đóng' }}
                  </button>
                </div>
              </form>
            </div>

            <!-- Review Form Toggle Button -->
            <div v-if="!showReviewForm && isAuthenticated && !userHasReviewed"
              class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-100 tw-mb-8 tw-text-center">
              <i class="bi bi-chat-square-text tw-text-3xl tw-text-gray-400 tw-mb-3 tw-block"></i>
              <p class="tw-mb-4 tw-text-gray-600">Bạn chưa đánh giá sản phẩm này</p>
              <button @click="showReviewForm = true"
                class="tw-bg-[#81AACC] tw-text-white tw-px-4 tw-py-1.5 tw-rounded-md tw-font-medium tw-transition-colors hover:tw-bg-[#6B8BA3] tw-inline-flex tw-items-center tw-gap-2 tw-text-sm">
                <i class="bi bi-pencil-square"></i> Viết đánh giá
              </button>
            </div>

            <!-- Edit Review Button for users who have already reviewed -->
            <div v-if="!showReviewForm && isAuthenticated && userHasReviewed"
              class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-100 tw-mb-8 tw-text-center">
              <i class="bi bi-check-circle tw-text-3xl tw-text-green-500 tw-mb-3 tw-block"></i>
              <p class="tw-mb-4 tw-text-gray-600">Bạn đã đánh giá sản phẩm này rồi</p>
              <button @click="editReview(userReview)"
                class="tw-bg-[#81AACC] tw-text-white tw-px-4 tw-py-1.5 tw-rounded-md tw-font-medium tw-transition-colors hover:tw-bg-[#6B8BA3] tw-inline-flex tw-items-center tw-gap-2 tw-text-sm">
                <i class="bi bi-pencil"></i> Chỉnh sửa đánh giá của bạn
              </button>
            </div>

            <!-- Review List -->
            <div class="tw-space-y-6">
              <h3 class="tw-text-xl tw-font-semibold tw-mb-6 tw-flex tw-items-center tw-gap-2">
                <i class="bi bi-chat-square-text tw-text-[#81AACC]"></i> Đánh giá từ khách hàng
              </h3>

              <!-- Loading State -->
              <div v-if="reviewsLoading" class="tw-text-center tw-py-10 tw-bg-gray-50 tw-rounded-lg">
                <div
                  class="tw-inline-block tw-animate-spin tw-rounded-full tw-h-8 tw-w-8 tw-border-b-2 tw-border-[#81AACC] tw-mb-4">
                </div>
                <p class="tw-text-gray-500">Đang tải đánh giá...</p>
              </div>

              <!-- Empty State -->
              <div v-else-if="reviews.length === 0" class="tw-text-center tw-py-10 tw-bg-gray-50 tw-rounded-lg">
                <i class="bi bi-chat-square tw-text-4xl tw-text-gray-300 tw-mb-3 tw-block"></i>
                <p class="tw-text-gray-500">Chưa có đánh giá nào cho sản phẩm này</p>
              </div>

              <!-- Reviews Content -->
              <div v-else class="tw-space-y-6">
                <div v-for="review in reviews" :key="review.id"
                  class="tw-bg-white tw-rounded-lg tw-p-6 tw-border tw-border-gray-100 tw-shadow-sm tw-transition-all hover:tw-shadow-md">
                  <div class="tw-flex tw-justify-between tw-mb-4">
                    <div class="tw-flex tw-items-center tw-gap-3">
                      <img
                        :src="review.user?.avatar ? (review.user.avatar.startsWith('http') ? review.user.avatar : runtimeConfig.public.apiBaseUrl +
                          '/storage/avatars/' + review.user.avatar.split('/').pop()) : 'https://img.freepik.com/premium-vector/user-icons-includes-user-icons-people-icons-symbols-premiumquality-graphic-design-elements_981536-526.jpg'"
                        :alt="review.user?.name"
                        class="tw-w-12 tw-h-12 tw-rounded-full tw-object-cover tw-border-2 tw-border-gray-200" />
                      <div>
                        <div class="tw-font-semibold tw-text-gray-800">{{ review.user?.username || review.user?.name }}
                        </div>
                        <div class="tw-text-sm tw-text-gray-500 tw-flex tw-items-center tw-gap-1">
                          <i class="bi bi-calendar3"></i> {{ new Date(review.created_at).toLocaleDateString() }}
                        </div>
                      </div>
                    </div>
                    <div class="tw-flex tw-items-center tw-gap-3">
                      <div class="tw-px-3 tw-py-1 tw-rounded-full tw-flex tw-items-center tw-gap-1">
                        <!-- <span class="tw-font-medium">{{ review.rating }}</span> -->
                        <div class="tw-text-yellow-400">
                          <i v-for="n in 5" :key="n" :class="n <= review.rating ? 'bi bi-star-fill' : 'bi bi-star'"
                            class="tw-text-sm"></i>
                        </div>
                      </div>
                      <!-- Nút sửa và xóa đánh giá -->
                      <div v-if="canModifyReview(review)" class="tw-flex tw-gap-2">
                        <button @click="editReview(review)"
                          class="tw-text-[#81AACC] hover:tw-text-[#6B8BA3] tw-bg-[#81AACC]/10 hover:tw-bg-[#81AACC]/20 tw-rounded-full tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-transition-colors"
                          title="Chỉnh sửa đánh giá">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button @click="removeReview(review.id)"
                          class="tw-text-red-600 hover:tw-text-red-800 tw-bg-red-50 hover:tw-bg-red-100 tw-rounded-full tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-transition-colors"
                          title="Xóa đánh giá">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                  <p class="tw-text-gray-700 tw-my-4 tw-leading-relaxed">{{ review.content }}</p>

                  <!-- Hiển thị hình ảnh đánh giá -->
                  <div v-if="review.images && review.images.length > 0" class="tw-mt-4 tw-flex tw-flex-wrap tw-gap-3">
                    <div v-for="image in review.images" :key="image.id"
                      class="tw-relative tw-group tw-overflow-hidden tw-rounded-lg tw-shadow-sm">
                      <img :src="runtimeConfig.public.apiBaseUrl + '/storage/' + image.image_path"
                        :alt="'Hình ảnh đánh giá'"
                        class="tw-w-24 tw-h-24 tw-object-cover tw-cursor-pointer tw-transition-transform tw-duration-300 group-hover:tw-scale-110"
                        @click="openImageModal(runtimeConfig.public.apiBaseUrl + '/storage/' + image.image_path)" />
                      <div
                        class="tw-absolute tw-inset-0 tw-bg-black tw-bg-opacity-0 group-hover:tw-bg-opacity-20 tw-transition-all tw-duration-300 tw-flex tw-items-center tw-justify-center">
                        <i
                          class="bi bi-zoom-in tw-text-white tw-opacity-0 group-hover:tw-opacity-100 tw-text-xl tw-transition-opacity tw-duration-300"></i>
                      </div>
                    </div>
                  </div>

                  <!-- Hiển thị phản hồi của đánh giá -->
                  <div v-if="review.replies && review.replies.length > 0"
                    class="tw-mt-6 tw-border-t tw-border-gray-100 tw-pt-4">
                    <h4 class="tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-3">Phản hồi:</h4>
                    <div v-for="reply in review.replies" :key="reply.id"
                      class="tw-bg-gray-50 tw-rounded-lg tw-p-4 tw-mb-3">
                      <div class="tw-flex tw-items-start tw-gap-3">
                        <img
                          :src="reply.user?.avatar ? (reply.user.avatar.startsWith('http') ? reply.user.avatar : runtimeConfig.public.apiBaseUrl + '/storage/avatars/' + reply.user.avatar.split('/').pop()) : 'https://cdn-img.upanhlaylink.com/img/image_202505261a100993dadd1e94d860ec123578e3cf.jpg'"
                          :alt="reply.user?.name"
                          class="tw-w-8 tw-h-8 tw-rounded-full tw-object-cover tw-border tw-border-gray-200" />
                        <div class="tw-flex-1">
                          <div class="tw-flex tw-justify-between tw-items-center tw-mb-1">
                            <div class="tw-font-medium tw-text-gray-800">
                              {{ reply.user?.username || reply.user?.name }}
                              <span v-if="reply.is_admin_reply"
                                class="tw-bg-[#81AACC]/10 tw-text-[#81AACC] tw-text-xs tw-px-2 tw-py-0.5 tw-rounded-full tw-ml-2">Admin</span>
                            </div>
                            <div class="tw-text-xs tw-text-gray-500">
                              {{ new Date(reply.created_at).toLocaleDateString() }}
                            </div>
                          </div>
                          <p class="tw-text-gray-700 tw-text-sm">{{ reply.content }}</p>

                          <div v-if="reply.images && reply.images.length > 0"
                            class="tw-mt-2 tw-flex tw-flex-wrap tw-gap-2">
                            <div v-for="image in reply.images" :key="image.id"
                              class="tw-relative tw-group tw-overflow-hidden tw-rounded-lg tw-shadow-sm">
                              <img :src="runtimeConfig.public.apiBaseUrl + '/storage/' + image.image_path"
                                :alt="'Hình ảnh phản hồi'"
                                class="tw-w-16 tw-h-16 tw-object-cover tw-cursor-pointer tw-transition-transform tw-duration-300 group-hover:tw-scale-110"
                                @click="openImageModal(runtimeConfig.public.apiBaseUrl + '/storage/' + image.image_path)" />
                              <div
                                class="tw-absolute tw-inset-0 tw-bg-black tw-bg-opacity-0 group-hover:tw-bg-opacity-20 tw-transition-all tw-duration-300 tw-flex tw-items-center tw-justify-center">
                                <i
                                  class="bi bi-zoom-in tw-text-white tw-opacity-0 group-hover:tw-opacity-100 tw-text-xl tw-transition-opacity tw-duration-300"></i>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Review Pagination -->
              <div v-if="reviewPaginationData && totalReviewPages > 1"
                class="tw-flex tw-justify-between tw-items-center tw-bg-white tw-rounded-lg tw-shadow tw-p-4 tw-mt-6">
                <div class="tw-text-sm tw-text-gray-600">
                  <span v-if="reviewsLoading">Đang tải...</span>
                  <span v-else>Hiển thị {{ reviewPaginationData.from }} - {{ reviewPaginationData.to }} trong tổng số {{
                    totalReviews }} đánh giá ({{ reviewsPerPage }} đánh giá/trang)</span>
                </div>
                <div class="tw-flex tw-gap-2">
                  <button @click="handleReviewPageChange(currentReviewPage - 1)"
                    :disabled="currentReviewPage === 1 || reviewsLoading"
                    class="tw-px-3 tw-py-1 tw-border tw-rounded tw-text-sm disabled:tw-opacity-50 disabled:tw-cursor-not-allowed hover:tw-bg-gray-50">
                    <i class="bi bi-chevron-left tw-mr-1"></i>Trước
                  </button>
                  <div class="tw-flex tw-gap-1">
                    <button v-for="page in getVisibleReviewPages()" :key="page" @click="handleReviewPageChange(page)"
                      :disabled="reviewsLoading" :class="[
                        'tw-px-3 tw-py-1 tw-border tw-rounded tw-text-sm disabled:tw-opacity-50 disabled:tw-cursor-not-allowed',
                        page === currentReviewPage
                          ? 'tw-bg-[#81AACC] tw-text-white tw-border-[#81AACC]'
                          : 'tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50'
                      ]">
                      {{ page }}
                    </button>
                  </div>
                  <button @click="handleReviewPageChange(currentReviewPage + 1)"
                    :disabled="currentReviewPage === totalReviewPages || reviewsLoading"
                    class="tw-px-3 tw-py-1 tw-border tw-rounded tw-text-sm disabled:tw-opacity-50 disabled:tw-cursor-not-allowed hover:tw-bg-gray-50">
                    Sau<i class="bi bi-chevron-right tw-ml-1"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Related Products -->
        <div class="tw-mt-12">
          <h2 class="tw-text-2xl tw-font-bold tw-mb-6">Sản phẩm liên quan</h2>
          <!-- Mobile Slider -->
          <div class="lg:tw-hidden">
            <swiper :slides-per-view="1.2" :space-between="16" :breakpoints="{
              '640': {
                slidesPerView: 2.2,
              },
              '768': {
                slidesPerView: 3.2,
              }
            }" class="tw-w-full">
              <swiper-slide v-for="product in relatedProducts" :key="product.id">
                <Card :product="product" />
              </swiper-slide>
            </swiper>
          </div>
          <!-- Desktop Grid -->
          <div class="tw-hidden lg:tw-grid lg:tw-grid-cols-5 tw-gap-6">
            <Card v-for="product in relatedProducts" :key="product.id" :product="product" />
          </div>
        </div>
      </div>
    </div>

    <!-- Zoom Modal -->
    <div v-if="showZoomModal"
      class="tw-fixed tw-inset-0 tw-z-50 tw-bg-black/90 tw-flex tw-items-center tw-justify-center"
      @click="showZoomModal = false">
      <div class="tw-relative tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center">
        <button class="tw-absolute tw-top-4 tw-right-4 tw-text-white tw-text-2xl" @click="showZoomModal = false">
          <i class="bi bi-x-lg"></i>
        </button>
        <img :src="mainImage" :alt="data?.name" class="tw-max-w-[90%] tw-max-h-[90vh] tw-object-contain" />
      </div>
    </div>
  </ClientOnly>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import Card from '~/components/home/Card.vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import useCarts from '~/composables/useCarts'
import { useCookie } from '#app'
import { useReviews } from '~/composables/useReviews'
import { useAuth } from '~/composables/useAuth'
const notyf = useNuxtApp().$notyf

const runtimeConfig = useRuntimeConfig()
const route = useRoute()
const { getProducts, getProductBySlug } = useProducts()
const { getInventories } = useInventories()
const { addToCart: addToCartComposable, getUserId, transferCartFromSessionToUser, fetchCart } = useCarts()
const { getReviewsByProductSlug, addReview, updateReview, deleteReview, checkUserReview } = useReviews()
const { user, isAuthenticated } = useAuth()

const userHasReviewed = ref(false)
const userReview = ref(null)

const { data, pending, error, refresh } = await useAsyncData(
  'product',
  async () => {
    const product = await getProductBySlug(route.params.slug)

    if (product?.variants?.length) {
      const inventories = await getInventories({ product_id: product.id })

      product.variants = product.variants.map(variant => {
        const inventory = inventories.find(inv => inv.variant_id === variant.id)
        return {
          ...variant,
          stock: inventory?.quantity || 0
        }
      })
    }

    return product
  },
  {
    watch: [() => route.params.slug]
  }
)

const showZoomModal = ref(false)

const mainImage = ref('')
const productImages = computed(() => {
  if (!data.value?.images?.length) return ['/images/placeholder.jpg']
  return data.value.images.map(img => img.image_path)
})

watch(data, () => {
  if (data.value?.images?.length) {
    const mainImg = data.value.images.find(img => img.is_main === 1) || data.value.images[0]
    mainImage.value = mainImg.image_path
  } else {
    mainImage.value = '/images/placeholder.jpg'
  }
}, { immediate: true })

const sizes = computed(() => {
  if (!data.value?.variants?.length) return []
  const uniqueSizes = new Set()
  data.value.variants.forEach(variant => {
    if (variant.size) uniqueSizes.add(variant.size)
  })
  return Array.from(uniqueSizes)
})

const colors = computed(() => {
  if (!data.value?.variants?.length) return []
  const uniqueColors = new Set()
  data.value.variants.forEach(variant => {
    if (variant.color) uniqueColors.add(variant.color)
  })
  return Array.from(uniqueColors).map(color => ({
    name: color,
    code: color
  }))
})

const selectedSize = ref('')
const selectedColor = ref(null)

const selectedVariantStock = computed(() => {
  if (!data.value?.variants?.length) return 0

  const variant = data.value.variants.find(v =>
    v.size === selectedSize.value &&
    v.color === selectedColor.value?.name
  )

  return variant?.stock || 0
})

watch(data, () => {
  if (sizes.value.length > 0) selectedSize.value = sizes.value[0]
  if (colors.value.length > 0) selectedColor.value = colors.value[0]
}, { immediate: true })

const quantity = ref(1)

const tabs = [
  { id: 'description', name: 'Mô tả' },
  { id: 'reviews', name: 'Đánh giá' },
]
const activeTab = ref('description')

const reviews = ref([])
const reviewStats = ref({
  average: 0,
  total: 0,
  distribution: [
    { stars: 5, percentage: 0 },
    { stars: 4, percentage: 0 },
    { stars: 3, percentage: 0 },
    { stars: 2, percentage: 0 },
    { stars: 1, percentage: 0 }
  ]
})

const currentReviewPage = ref(1)
const reviewsPerPage = ref(3)
const totalReviewPages = ref(1)
const totalReviews = ref(0)
const reviewPaginationData = ref(null)
const reviewsLoading = ref(false)

const fetchReviews = async (page = 1) => {
  try {
    reviewsLoading.value = true
    const response = await getReviewsByProductSlug(data.value.slug, page, reviewsPerPage.value)

    reviewPaginationData.value = {
      current_page: response.current_page,
      last_page: response.last_page,
      per_page: response.per_page,
      total: response.total,
      from: response.from,
      to: response.to
    }

    currentReviewPage.value = response.current_page
    totalReviewPages.value = response.last_page
    totalReviews.value = response.total

    reviews.value = response.data

    if (reviews.value.length > 0) {
      const total = response.total
      const sum = reviews.value.reduce((acc, review) => acc + review.rating, 0)
      const average = sum / total

      const distribution = [5, 4, 3, 2, 1].map(stars => {
        const count = reviews.value.filter(r => r.rating === stars).length
        return {
          stars,
          percentage: Math.round((count / total) * 100)
        }
      })

      reviewStats.value = {
        average: parseFloat(average.toFixed(1)),
        total,
        distribution
      }
    }

    if (isAuthenticated.value && user.value) {
      await checkUserHasReviewed()
    }
  } catch (error) {
    console.error('Error fetching reviews:', error)
  } finally {
    reviewsLoading.value = false
  }
}

onMounted(() => {
  if (data.value) {
    fetchReviews()
  }
})

watch(data, () => {
  if (data.value) {
    fetchReviews()
  }
}, { immediate: true })

const relatedProducts = ref([])

watch(data, async () => {
  if (data.value?.categories_id) {
    try {
      const products = await getProducts()
      relatedProducts.value = products
        .filter(p => p.categories_id === data.value.categories_id && p.id !== data.value.id)
        .slice(0, 5)
    } catch (error) {
      console.error('Error fetching related products:', error)
    }
  }
}, { immediate: true })

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price)
}

const addToCart = async () => {
  try {
    const selectedVariant = data.value.variants.find(v =>
      v.size === selectedSize.value &&
      v.color === selectedColor.value?.name
    )
    if (!selectedVariant) {
      alert('Vui lòng chọn size và màu sắc')
      return
    }
    if (quantity.value > selectedVariant.stock) {
      alert('Số lượng vượt quá số lượng trong kho')
      return
    }
    await addToCartComposable(selectedVariant.id, quantity.value)
    notyf.success('Đã thêm vào giỏ hàng')
  } catch (error) {
    console.error('Error adding to cart:', error)
    alert('Có lỗi xảy ra khi thêm vào giỏ hàng')
  }
}

const mergeCartAfterLogin = async () => {
  await transferCartFromSessionToUser()
  await fetchCart()
  alert('Đã hợp nhất giỏ hàng từ session sang tài khoản!')
}

useHead(() => ({
  title: data.value ? `${data.value.name} - DEVGANG` : 'Đang tải sản phẩm...',
  meta: [
    {
      name: 'description',
      content: data.value?.description || data.value?.name || 'Sản phẩm nổi bật từ DEVGANG'
    },
    { property: 'og:title', content: data.value?.name || '' },
    { property: 'og:description', content: data.value?.description || '' },
    { property: 'og:image', content: data.value?.images?.[0]?.image_path || '/default-og.jpg' },
    { property: 'og:type', content: 'product' },
    { property: 'og:url', content: typeof window !== 'undefined' ? window.location.href : '' }
  ]
}))

const reviewForm = ref({
  rating: 5,
  content: '',
  images: []
})

const editingReviewId = ref(null)
const isSubmitting = ref(false)
const previewImages = ref([])
const deleteImageIds = ref([])

const showReviewForm = ref(false)

const handleImageUpload = (event) => {
  const files = event.target.files
  if (!files.length) return

  for (let i = 0; i < files.length; i++) {
    const file = files[i]
    reviewForm.value.images.push(file)

    const reader = new FileReader()
    reader.onload = (e) => {
      previewImages.value.push({
        url: e.target.result,
        file: file
      })
    }
    reader.readAsDataURL(file)
  }
}

const removeImage = (index) => {
  if (previewImages.value[index].existing && previewImages.value[index].id) {
    deleteImageIds.value.push(previewImages.value[index].id)
  }

  previewImages.value.splice(index, 1)
  reviewForm.value.images.splice(index, 1)
}

const submitReview = async () => {
  if (!isAuthenticated.value) {
    alert('Vui lòng đăng nhập để đánh giá sản phẩm')
    return
  }

  if (!reviewForm.value.content.trim()) {
    alert('Vui lòng nhập nội dung đánh giá')
    return
  }

  try {
    isSubmitting.value = true

    const reviewData = {
      user_id: user.value.id,
      product_slug: data.value.slug,
      rating: reviewForm.value.rating,
      content: reviewForm.value.content,
      images: reviewForm.value.images
    }

    if (editingReviewId.value && deleteImageIds.value.length > 0) {
      reviewData.delete_image_ids = deleteImageIds.value
    }

    if (editingReviewId.value) {
      await updateReview(editingReviewId.value, reviewData)
      alert('Đã cập nhật đánh giá thành công')
    } else {
      await addReview(reviewData)
      alert('Đã gửi đánh giá thành công')
    }

    reviewForm.value = {
      rating: 5,
      content: '',
      images: []
    }
    previewImages.value = []
    deleteImageIds.value = []
    editingReviewId.value = null
    showReviewForm.value = false

    await fetchReviews(1)
  } catch (error) {
    console.error('Lỗi khi gửi đánh giá:', error)

    if (error.response && error.response.status === 422) {
      alert('Bạn đã đánh giá sản phẩm này rồi. Vui lòng chỉnh sửa đánh giá hiện có thay vì tạo mới.')
      await checkUserHasReviewed()
    } else {
      alert('Có lỗi xảy ra khi gửi đánh giá')
    }
  } finally {
    isSubmitting.value = false
  }
}

const editReview = (review) => {
  editingReviewId.value = review.id
  reviewForm.value.rating = review.rating
  reviewForm.value.content = review.content
  reviewForm.value.images = []

  previewImages.value = []
  if (review.images && review.images.length > 0) {
    review.images.forEach(image => {
      previewImages.value.push({
        url: runtimeConfig.public.apiBaseUrl + '/storage/' + image.image_path,
        id: image.id,
        existing: true
      })
    })
  }

  showReviewForm.value = true
  document.getElementById('review-form').scrollIntoView({ behavior: 'smooth' })
}

const cancelEdit = () => {
  editingReviewId.value = null
  reviewForm.value = {
    rating: 5,
    content: '',
    images: []
  }
  previewImages.value = []
  deleteImageIds.value = []
  showReviewForm.value = false
}

const removeReview = async (reviewId) => {
  if (!confirm('Bạn có chắc chắn muốn xóa đánh giá này?')) return

  try {
    await deleteReview(reviewId)
    alert('Đã xóa đánh giá thành công')

    const currentPageReviews = reviews.value.length
    if (currentPageReviews === 1 && currentReviewPage.value > 1) {
      await fetchReviews(currentReviewPage.value - 1)
    } else {
      await fetchReviews(currentReviewPage.value)
    }
  } catch (error) {
    console.error('Lỗi khi xóa Đánh giá:', error)
    alert('Có lỗi xảy ra khi xóa đánh giá')
  }
}

const canModifyReview = (review) => {
  return isAuthenticated.value && user.value && review.user_id === user.value.id
}

const checkUserHasReviewed = async () => {
  if (!isAuthenticated.value || !user.value || !data.value) return

  try {
    const response = await checkUserReview(user.value.id, data.value.slug)
    userHasReviewed.value = response.hasReviewed
    userReview.value = response.review || null

  } catch (error) {
    console.error('Lỗi khi kiểm tra đánh giá của người dùng:', error)
  }
}

const handleReviewPageChange = (page) => {
  currentReviewPage.value = page
  fetchReviews(page)
}

const getVisibleReviewPages = () => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, currentReviewPage.value - Math.floor(maxVisible / 2))
  let end = Math.min(totalReviewPages.value, start + maxVisible - 1)

  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
}
</script>

<style scoped>
.tw-cursor-zoom-in {
  cursor: zoom-in;
}
</style>
