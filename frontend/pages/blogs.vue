<template>
    <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 tw-py-6 sm:tw-py-10">
        <h1 class="tw-text-center tw-text-[1.5rem] sm:tw-text-[2rem] tw-font-normal tw-mb-6 sm:tw-mb-8">Tin tức</h1>
        <div class="tw-flex tw-flex-col lg:tw-flex-row tw-gap-6 sm:tw-gap-10">
            <main class="tw-flex-1 tw-max-w-4xl tw-space-y-6 sm:tw-space-y-10">
                <BlogArticle v-for="(article, index) in articles" :key="index" :article="article" />
            </main>

            <aside class="tw-w-full lg:tw-max-w-xs tw-flex-shrink-0 tw-space-y-6 sm:tw-space-y-8">
                <!-- Danh mục tin tức -->
                <section class="tw-bg-gray-50 tw-p-4 sm:tw-p-0 sm:tw-bg-transparent">
                    <h3 class="tw-text-[14px] sm:tw-text-[16px] tw-font-semibold tw-mb-3 tw-uppercase tw-tracking-wide">
                        DANH MỤC TIN TỨC
                    </h3>
                    <ul class="tw-text-[0.875rem] sm:tw-text-[1rem] tw-text-gray-700 tw-space-y-1">
                        <li class="tw-cursor-pointer tw-select-none hover:tw-text-[#81AACC]">Trang chủ</li>
                        <li
                            class="tw-flex tw-justify-between tw-items-center tw-cursor-pointer tw-select-none hover:tw-text-[#81AACC]">
                            Sản phẩm <i class="fas fa-chevron-down tw-text-sm"></i>
                        </li>
                        <li
                            class="tw-flex tw-justify-between tw-items-center tw-cursor-pointer tw-select-none hover:tw-text-[#81AACC]">
                            Chương trình khuyến mãi <i class="fas fa-chevron-down tw-text-sm"></i>
                        </li>
                        <li class="tw-cursor-pointer tw-select-none hover:tw-text-[#81AACC]">Hệ thống cửa hàng</li>
                        <li class="tw-cursor-pointer tw-select-none hover:tw-text-[#81AACC]">Giới thiệu</li>
                        <li class="tw-cursor-pointer tw-select-none hover:tw-text-[#81AACC]">Tin tức</li>
                        <li class="tw-cursor-pointer tw-select-none hover:tw-text-[#81AACC]">Liên hệ</li>
                        <li class="tw-cursor-pointer tw-select-none hover:tw-text-[#81AACC]">Kiểm tra đơn hàng</li>
                    </ul>
                </section>

                <!-- Tin nổi bật -->
                <section class="tw-bg-gray-50 tw-p-4 sm:tw-p-0 sm:tw-bg-transparent">
                    <h3 class="tw-text-[14px] sm:tw-text-[16px] tw-font-semibold tw-mb-3 tw-uppercase tw-tracking-wide">
                        TIN NỔI BẬT
                    </h3>
                    <ul class="tw-space-y-3 tw-text-[14px] sm:tw-text-[16px] tw-text-gray-800">
                        <HighlightItem v-for="(item, index) in highlights" :key="index" :item="item" />
                    </ul>
                </section>
            </aside>
        </div>
    </div>
</template>

<script setup>
useHead({
    title: 'Tin tức - DEVGANG',
    meta: [
        { name: 'description', content: 'Tin tức - DEVGANG' },
    ]
})
import HighlightItem from '@/components/blogs/HighlightItem.vue';
import BlogArticle from '@/components/blogs/BlogArticle.vue';

const articles = [
    {
        title: 'Quần short nam và 5 nguyên tắc bạn cần biết',
        date: '12/04/2023',
        readTime: '3 phút',
        image: 'https://storage.googleapis.com/a1aa/image/89f0f8f6-ae74-4202-9623-2e41987189df.jpg',
        excerpt: 'Những mẫu quần short luôn là một trong những items thời trang chiếm số lượng cực lớn trong tủ đồ của mọi chàng trai. Hãy cùng tìm hiểu 5 nguyên tắc quan trọng khi chọn quần short nam...'
    },
    {
        title: '9 Cách phối màu quần áo nam cho chàng thêm phong cách và thời trang',
        date: '11/04/2023',
        readTime: '6 phút',
        image: 'https://storage.googleapis.com/a1aa/image/fe6ca910-a815-41b4-0b3b-8d4e1d896c5f.jpg',
        excerpt: 'Chọn trang phục có thiết kế đẹp và hợp với dáng thôi vẫn chưa đủ đâu nhé các chàng trai. Để có một set đồ ấn tượng, bạn cần biết cách phối màu...'
    },
    {
        title: 'Bí mật 6+ cách phối đồ ăn gian chiều cao cho nam đơn giản, hiệu quả cao',
        date: '11/04/2023',
        readTime: '8 phút',
        image: 'https://storage.googleapis.com/a1aa/image/ba3f8873-a8fd-423e-432c-f0eea310516f.jpg',
        excerpt: 'Cách phối đồ ăn gian chiều cao cho nam giới là vấn đề được phái mạnh quan tâm nhiều nhất. Xã hội luôn thích những người có chiều cao...'
    },
    {
        title: 'TOP 4 ITEM QUẦN ÁO NAM MÙA HÈ GÂY BÃO CỰC CHẤT',
        date: '11/04/2023',
        readTime: '4 phút',
        image: 'https://storage.googleapis.com/a1aa/image/988ba650-809b-4be8-d119-741de4b94ab8.jpg',
        excerpt: 'Thời trang là khẳng định phong cách, là yếu tố tồn lên vẻ đẹp tiềm ẩn của mỗi cá nhân. Chính vì vậy, làng thời trang luôn...'
    },
    {
        title: '8 Xu hướng thời trang nam mùa hè mới nhất',
        date: '10/04/2023',
        readTime: '5 phút',
        image: 'https://storage.googleapis.com/a1aa/image/49820d58-90d5-4da2-e15a-c8fbff260c9b.jpg',
        excerpt: 'Mùa hè đang đến gần, đây là thời điểm hoàn hảo để cập nhật tủ đồ của bạn với những xu hướng thời trang nam mới nhất...'
    }
];

const highlights = [
    {
        img: 'https://storage.googleapis.com/a1aa/image/49820d58-90d5-4da2-e15a-c8fbff260c9b.jpg',
        text: '8 Xu hướng thời trang nam mùa hè mới nhất'
    },
    {
        img: 'https://storage.googleapis.com/a1aa/image/4643301c-219c-44df-4f0b-fcfb5ba83c90.jpg',
        text: 'Quần short nam và 5 nguyên tắc nam giới cần phải biết'
    },
    {
        img: 'https://storage.googleapis.com/a1aa/image/f61eee0b-c33e-4fcf-01dd-2d859d2a0680.jpg',
        text: '9 Cách phối màu quần áo nam cho chàng thêm phong cách và thời trang'
    },
    {
        img: 'https://storage.googleapis.com/a1aa/image/8eedc17f-2463-4138-0484-f85120c15be8.jpg',
        text: 'Bí mật 6+ cách phối đồ ăn gian chiều cao cho nam đơn giản, hiệu quả cao'
    }
];
</script>

<!-- BlogArticle.vue và HighlightItem.vue sẽ chứa template tái sử dụng -->