<template>
    <div class="tw-text-[#1a1a1a]">
        <div class="tw-max-w-7xl tw-mx-auto tw-px-6 md:tw-px-10 tw-pt-10 md:tw-pt-20">
            <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-gap-20">
                <div class="md:tw-w-1/2">
                    <h2 class="tw-font-semibold tw-text-[24px] md:tw-text-[28px] tw-leading-tight tw-mb-4">
                        DEV GANG - Phong cách
                        <br />
                        thời trang đẳng cấp
                    </h2>
                    <p class="tw-text-[14px] md:tw-text-[15px] tw-leading-[20px] tw-mb-3">
                        DEV GANG được thành lập với tâm huyết mang đến cho phái mạnh những sản phẩm thời trang chất
                        lượng
                        cao,
                        thiết kế tinh tế và phong cách hiện đại. Mỗi sản phẩm đều được chúng tôi chăm chút tỉ mỉ từ khâu
                        thiết kế đến sản xuất.
                    </p>
                    <p class="tw-text-[14px] md:tw-text-[15px] tw-leading-[20px] tw-mb-3">
                        Với tiêu chí "Chất lượng tạo nên thương hiệu", chúng tôi luôn nỗ lực mang đến cho khách hàng
                        những trải nghiệm mua sắm
                        tuyệt vời cùng các sản phẩm thời trang đẳng cấp, giúp bạn tự tin tỏa sáng trong mọi hoàn
                        cảnh.
                    </p>
                    <p class="tw-text-[14px] md:tw-text-[15px] tw-leading-[20px]">
                        Đội ngũ thiết kế của DEV GANG luôn cập nhật xu hướng thời trang mới nhất, kết hợp với tinh hoa
                        thời trang truyền thống
                        để tạo nên những bộ sưu tập độc đáo, phù hợp với phong cách sống hiện đại của giới Việt.
                    </p>
                </div>
                <div class="md:tw-w-1/2 tw-mt-8 md:tw-mt-0 tw-flex tw-justify-center">
                    <img alt="Mẫu mặc trang phục thời trang cao cấp"
                        class="tw-w-[320px] md:tw-w-[400px] tw-object-cover tw-rounded" height="400"
                        src="../../assets/logo-header.png" width="400" />
                </div>
            </div>
            <div class="tw-mt-14 tw-grid tw-grid-cols-1 sm:tw-grid-cols-3 tw-gap-6 md:tw-gap-8">
                <div class="tw-relative tw-group tw-cursor-pointer">
                    <img alt="Bộ sưu tập áo sơ mi cao cấp"
                        class="tw-w-full tw-h-[240px] sm:tw-h-[240px] tw-object-cover" height="400"
                        src="../../assets/about1.png" width="600" />
                    <div
                        class="tw-absolute tw-inset-0 tw-bg-black tw-bg-opacity-60 tw-flex tw-flex-col tw-items-center tw-justify-center tw-text-white tw-opacity-100 group-hover:tw-opacity-100 tw-transition-opacity">
                        <i class="fas fa-tshirt tw-text-4xl tw-mb-3">
                        </i>
                        <span class="tw-text-[16px] tw-font-semibold">
                            Thiết kế độc đáo
                        </span>
                    </div>
                </div>
                <div class="tw-relative tw-group tw-cursor-pointer">
                    <img alt="Chất liệu vải cao cấp" class="tw-w-full tw-h-[220px] sm:tw-h-[240px] tw-object-cover"
                        height="400" src="../../assets/about2.png" width="600" />
                    <div
                        class="tw-absolute tw-inset-0 tw-bg-black tw-bg-opacity-60 tw-flex tw-flex-col tw-items-center tw-justify-center tw-text-white tw-opacity-100 group-hover:tw-opacity-100 tw-transition-opacity">
                        <i class="fas fa-certificate tw-text-4xl tw-mb-3">
                        </i>
                        <span class="tw-text-[16px] tw-font-semibold">
                            Chất lượng đảm bảo
                        </span>
                    </div>
                </div>
                <div class="tw-relative tw-group tw-cursor-pointer">
                    <img alt="Dịch vụ khách hàng chuyên nghiệp"
                        class="tw-w-full tw-h-[220px] sm:tw-h-[240px] tw-object-cover" height="400"
                        src="../../assets/about3.png" width="600" />
                    <div
                        class="tw-absolute tw-inset-0 tw-bg-black tw-bg-opacity-60 tw-flex tw-flex-col tw-items-center tw-justify-center tw-text-white tw-opacity-100 group-hover:tw-opacity-100 tw-transition-opacity">
                        <i class="fas fa-headset tw-text-4xl tw-mb-3">
                        </i>
                        <span class="tw-text-[16px] tw-font-semibold">
                            Dịch vụ tận tâm
                        </span>
                    </div>
                </div>
            </div>
            <div class="tw-mt-14 tw-text-center tw-max-w-3xl tw-mx-auto tw-px-4">
                <h3 class="tw-font-extrabold tw-text-[22px] md:tw-text-[24px] tw-mb-3">
                    KHẲNG ĐỊNH PHONG CÁCH RIÊNG
                </h3>
                <p class="tw-text-[12px] md:tw-text-[13px] tw-text-[#4a4a4a] tw-leading-[18px] tw-mb-6">
                    DEV GANG tự hào là thương hiệu thời trang hàng đầu tại Việt Nam, mang đến những sản phẩm chất
                    lượng cao
                    với thiết kế hiện đại và tinh tế. Chúng tôi không ngừng phát triển và cải tiến để đáp ứng nhu cầu
                    ngày càng
                    cao của khách hàng, đồng thời duy trì cam kết về chất lượng và dịch vụ xuất sắc.
                </p>
                <NuxtLink to="/product"
                    class="tw-inline-block tw-text-[13px] tw-font-semibold tw-border tw-border-black tw-px-5 tw-py-2 hover:tw-bg-black hover:tw-text-white tw-transition-colors">
                    KHÁM PHÁ BỘ SƯU TẬP
                </NuxtLink>
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