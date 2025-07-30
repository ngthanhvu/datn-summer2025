<template>
    <div class="text-[#1a1a1a] bg-white pb-5">
        <div class="max-w-7xl mx-auto px-6 md:px-10 pt-10 md:pt-20">
            <div class="flex flex-col md:flex-row md:items-center md:gap-20">
                <div class="md:w-1/2">
                    <h2 class="font-semibold text-[24px] md:text-[28px] leading-tight mb-4">
                        DEV GANG - Phong cách
                        <br />
                        thời trang đẳng cấp
                    </h2>
                    <p class="text-[14px] md:text-[15px] leading-[20px] mb-3">
                        DEV GANG được thành lập với tâm huyết mang đến cho phái mạnh những sản phẩm thời trang chất
                        lượng cao, thiết kế tinh tế và phong cách hiện đại. Mỗi sản phẩm đều được chúng tôi chăm chút tỉ
                        mỉ từ khâu thiết kế đến sản xuất.
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
                        height="400" src="/logo-header.png" width="400" />
                </div>
            </div>

            <div class="mt-14 grid grid-cols-1 sm:grid-cols-3 gap-6 md:gap-8">
                <div class="relative group cursor-pointer">
                    <img alt="Bộ sưu tập áo sơ mi cao cấp" class="w-full h-[240px] sm:h-[240px] object-cover"
                        height="400" src="/about1.png" width="600" />
                    <div
                        class="absolute inset-0 bg-black bg-opacity-60 flex flex-col items-center justify-center text-white opacity-30 group-hover:opacity-50 transition-opacity">
                        <i class="fas fa-tshirt text-4xl mb-3"></i>
                        <span class="text-[16px] font-semibold">Thiết kế độc đáo</span>
                    </div>
                </div>

                <div class="relative group cursor-pointer">
                    <img alt="Chất liệu vải cao cấp" class="w-full h-[220px] sm:h-[240px] object-cover" height="400"
                        src="/about2.png" width="600" />
                    <div
                        class="absolute inset-0 bg-black bg-opacity-60 flex flex-col items-center justify-center text-white opacity-30 group-hover:opacity-50 transition-opacity">
                        <i class="fas fa-certificate text-4xl mb-3"></i>
                        <span class="text-[16px] font-semibold">Chất lượng đảm bảo</span>
                    </div>
                </div>

                <div class="relative group cursor-pointer">
                    <img alt="Dịch vụ khách hàng chuyên nghiệp" class="w-full h-[220px] sm:h-[240px] object-cover"
                        height="400" src="/about3.png" width="600" />
                    <div
                        class="absolute inset-0 bg-black bg-opacity-60 flex flex-col items-center justify-center text-white opacity-30 group-hover:opacity-50 transition-opacity">
                        <i class="fas fa-headset text-4xl mb-3"></i>
                        <span class="text-[16px] font-semibold">Dịch vụ tận tâm</span>
                    </div>
                </div>
            </div>

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
        </div>
    </div>
</template>

<script setup>
// Component logic here if needed
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
</style>
