<template>
    <div class="text-[#1a1a1a] bg-white pb-5">
        <div class="max-w-7xl mx-auto px-6 md:px-10 pt-10 md:pt-20">
            <!-- Giới thiệu -->
            <div class="flex flex-col md:flex-row md:items-center md:gap-20">
                <div class="md:w-1/2">
                    <h2 class="font-bold text-[24px] md:text-[28px] leading-tight mb-4">
                        DEV GANG - Phong cách
                        <br />
                        thời trang đẳng cấp
                    </h2>
                    <p class="text-[14px] md:text-[15px] leading-[20px] mb-3">
                        DEV GANG được thành lập với tâm huyết mang đến cho phái mạnh những sản phẩm thời trang chất
                        lượng cao, thiết kế tinh tế và phong cách hiện đại. Mỗi sản phẩm đều được chúng tôi chăm chút
                        tỉ mỉ từ khâu thiết kế đến sản xuất.
                    </p>
                    <p class="text-[14px] md:text-[15px] leading-[20px] mb-3">
                        Với tiêu chí "Chất lượng tạo nên thương hiệu", chúng tôi luôn nỗ lực mang đến cho khách hàng
                        những trải nghiệm mua sắm tuyệt vời cùng các sản phẩm thời trang đẳng cấp, giúp bạn tự tin tỏa
                        sáng trong mọi hoàn cảnh.
                    </p>
                    <p class="text-[14px] md:text-[15px] leading-[20px]">
                        Đội ngũ thiết kế của DEV GANG luôn cập nhật xu hướng thời trang mới nhất, kết hợp với tinh hoa
                        thời trang truyền thống để tạo nên những bộ sưu tập độc đáo, phù hợp với phong cách sống hiện
                        đại của giới Việt.
                    </p>
                </div>
                <div class="md:w-1/2 mt-8 md:mt-0 flex justify-center">
                    <img alt="Mẫu mặc trang phục thời trang cao cấp" class="w-[320px] md:w-[400px] object-cover rounded"
                        height="400" src="/logo2.png" width="400" />
                </div>
            </div>

            <!-- 3 cột dịch vụ -->
            <div class="mt-14 grid grid-cols-1 sm:grid-cols-3 gap-6 md:gap-8">
                <div class="relative group cursor-pointer h-[240px]">
                    <img alt="Bộ sưu tập áo sơ mi cao cấp" class="w-full h-full object-cover rounded-lg"
                        src="/about1.png" />
                    <div
                        class="absolute inset-0 bg-black bg-opacity-60 flex flex-col items-center justify-center text-white opacity-30 group-hover:opacity-50 transition-opacity rounded-lg">
                        <i class="fas fa-tshirt text-4xl mb-3"></i>
                        <span class="text-[16px] font-semibold">Thiết kế độc đáo</span>
                    </div>
                </div>

                <div class="relative group cursor-pointer h-[240px]">
                    <img alt="Chất liệu vải cao cấp" class="w-full h-full object-cover rounded-lg" src="/about2.png" />
                    <div
                        class="absolute inset-0 bg-black bg-opacity-60 flex flex-col items-center justify-center text-white opacity-30 group-hover:opacity-50 transition-opacity rounded-lg">
                        <i class="fas fa-certificate text-4xl mb-3"></i>
                        <span class="text-[16px] font-semibold">Chất lượng đảm bảo</span>
                    </div>
                </div>

                <div class="relative group cursor-pointer h-[240px]">
                    <img alt="Dịch vụ khách hàng chuyên nghiệp" class="w-full h-full object-cover rounded-lg"
                        src="/about3.png" />
                    <div
                        class="absolute inset-0 bg-black bg-opacity-60 flex flex-col items-center justify-center text-white opacity-30 group-hover:opacity-50 transition-opacity rounded-lg">
                        <i class="fas fa-headset text-4xl mb-3"></i>
                        <span class="text-[16px] font-semibold">Dịch vụ tận tâm</span>
                    </div>
                </div>
            </div>

            <!-- Mô tả thương hiệu -->
            <div class="mt-14 text-center max-w-3xl mx-auto px-4">
                <h3 class="font-extrabold text-[22px] md:text-[24px] mb-3">
                    KHẲNG ĐỊNH PHONG CÁCH RIÊNG
                </h3>
                <p class="text-[12px] md:text-[13px] text-[#4a4a4a] leading-[18px] mb-6">
                    DEV GANG tự hào là thương hiệu thời trang hàng đầu tại Việt Nam, mang đến những sản phẩm chất
                    lượng cao với thiết kế hiện đại và tinh tế. Chúng tôi không ngừng phát triển và cải tiến để đáp ứng
                    nhu cầu ngày càng cao của khách hàng, đồng thời duy trì cam kết về chất lượng và dịch vụ xuất sắc.
                </p>
                <router-link to="/san-pham"
                    class="inline-block text-[13px] font-semibold border border-black px-5 py-2 hover:bg-black hover:text-white transition-colors">
                    KHÁM PHÁ BỘ SƯU TẬP
                </router-link>
            </div>

            <!-- Rating khách hàng -->
            <div class="mt-16">
                <h3 class="text-center font-extrabold text-[22px] md:text-[24px] mb-8">
                    KHÁCH HÀNG NÓI GÌ VỀ CHÚNG TÔI
                </h3>

                <swiper :modules="[Autoplay]" :slides-per-view="1" :space-between="20" :loop="true"
                    :autoplay="{ delay: 3000, disableOnInteraction: false }" :breakpoints="{
                        640: { slidesPerView: 1 },
                        768: { slidesPerView: 2 },
                        1024: { slidesPerView: 3 }
                    }">
                    <swiper-slide v-for="(review, index) in reviews" :key="index"
                        class="bg-gray-50 p-6 rounded-lg shadow-md flex flex-col items-center text-center h-full">
                        <div class="flex flex-col items-center flex-1 justify-between">
                            <div class="flex flex-col items-center">
                                <img :src="review.avatar || 'https://i.pravatar.cc/100?u=' + review.name" alt="Avatar"
                                    class="w-16 h-16 rounded-full object-cover mb-4" />
                                <p class="text-sm text-gray-600 italic mb-3">
                                    "{{ review.comment }}"
                                </p>
                            </div>
                            <div>
                                <div class="flex mb-2 justify-center">
                                    <i v-for="i in 5" :key="i" class="fas"
                                        :class="i <= review.rating ? 'fa-star text-yellow-500' : 'fa-star text-gray-300'"></i>
                                </div>
                                <h4 class="font-semibold text-gray-800">{{ review.name }}</h4>
                                <span class="text-xs text-gray-500">{{ review.position }}</span>
                            </div>
                        </div>
                    </swiper-slide>
                </swiper>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Swiper, SwiperSlide } from "swiper/vue"
import { Autoplay } from "swiper/modules"
import "swiper/css"

const reviews = [
    {
        name: "Lê Hoàng Nam",
        position: "Doanh nhân",
        comment: "Sản phẩm chất lượng, phong cách sang trọng.",
        rating: 5,
        avatar: "https://i.imgur.com/8WG43KO.jpeg"
    },
    {
        name: "Phạm Thị Mai Anh",
        position: "Nhân viên văn phòng",
        comment: "Dịch vụ chăm sóc khách hàng tuyệt vời.",
        rating: 4,
        avatar: "https://i.imgur.com/JxUeLUz.png"
    },
    {
        name: "Nguyễn Văn Khánh",
        position: "Freelancer",
        comment: "Quần áo đẹp, thoải mái, hợp xu hướng.",
        rating: 5,
        avatar: "https://i.imgur.com/ks0gE5o.png"
    },
    {
        name: "Trần Ngọc Bảo Châu",
        position: "Chuyên viên",
        comment: "Sản phẩm rất tinh tế, dịch vụ nhanh chóng.",
        rating: 5,
        avatar: "https://i.imgur.com/dyi1ONw.png"
    },
    {
        name: "Đỗ Minh Quân",
        position: "Designer",
        comment: "Mẫu mã đa dạng, rất ưng ý!",
        rating: 5,
        avatar: "https://i.imgur.com/VoN8Nb1.png"
    }
]
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
</style>
