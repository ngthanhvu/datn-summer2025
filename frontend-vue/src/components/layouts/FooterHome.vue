<template>
    <footer class="bg-[#81AACC] text-white pt-10 pb-4">
        <div class="container mx-auto px-4 py-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Cột 1: Thông tin liên hệ -->
                <div>
                    <div class="mb-4 font-bold text-2xl flex items-center gap-2">
                        <img src="https://placehold.co/150x50?text=LOGO" alt="Logo" class="w-32" />
                    </div>
                    <div class="flex items-start gap-2 mb-2">
                        <span class="text-2xl font-semibold">Tên cửa hàng</span>
                    </div>

                    <div class="flex items-start gap-2 mb-2">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>Địa chỉ: Chưa cập nhật</span>
                    </div>
                    <div class="flex items-start gap-2 mb-2">
                        <i class="bi bi-telephone-fill"></i>
                        <span>Số điện thoại: Chưa có</span>
                    </div>
                    <div class="flex items-start gap-2 mb-2">
                        <i class="bi bi-envelope-fill"></i>
                        <span>Email: Chưa có</span>
                    </div>
                    <div class="mt-4 text-xs text-gray-200">
                        © Bản quyền thuộc về <span class="font-bold text-white">DEVGANG</span> | Cung cấp bởi
                        <span class="text-white">DEVGANG</span>
                    </div>
                </div>

                <!-- Cột 2: Chính sách -->
                <div>
                    <div class="font-bold mb-4">CHÍNH SÁCH</div>
                    <ul class="space-y-2 text-gray-100">
                        <li><a href="/about" class="hover:text-[#2563eb]">Giới thiệu</a></li>
                        <li><a href="/store-system" class="hover:text-[#2563eb]">Hệ thống cửa hàng</a></li>
                        <li><a href="/faq" class="hover:text-[#2563eb]">Câu hỏi thường gặp</a></li>
                        <li><a href="/order-call" class="hover:text-[#2563eb]">Gọi điện đặt hàng</a></li>
                    </ul>
                </div>

                <!-- Cột 3: Hỗ trợ khách hàng -->
                <div>
                    <div class="font-bold mb-4">HỖ TRỢ KHÁCH HÀNG</div>
                    <ul class="space-y-2 text-gray-100">
                        <li><a href="/contact" class="hover:text-[#2563eb]">Liên hệ</a></li>
                        <li><a href="/policy/sale" class="hover:text-[#2563eb]">Chính sách bán hàng</a></li>
                        <li><a href="/policy/shipping" class="hover:text-[#2563eb]">Chính sách giao hàng</a></li>
                        <li><a href="/policy/return" class="hover:text-[#2563eb]">Chính sách đổi trả</a></li>
                    </ul>
                </div>

                <!-- Cột 4: Đăng ký nhận tin -->
                <div>
                    <div class="font-bold mb-4">ĐĂNG KÝ NHẬN TIN</div>
                    <form class="relative mb-4">
                        <input type="email" placeholder="Nhập địa chỉ email"
                            class="rounded-full bg-white px-3 py-3 w-full border border-[#5A82AB] text-gray-700 focus:outline-none pr-16" />
                        <button type="submit"
                            class="absolute top-1/2 right-[5px] -translate-y-1/2 bg-[#81aacc] text-white px-4 py-2 rounded-full hover:bg-[#5A82AB] flex items-center gap-1 cursor-pointer">
                            Đăng ký
                        </button>
                    </form>
                    <div class="flex gap-4 mb-4">
                        <a href="#" class="text-white text-2xl"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white text-2xl"><i class="bi bi-chat-dots"></i></a>
                        <a href="#" class="text-white text-2xl"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white text-2xl"><i class="bi bi-youtube"></i></a>
                        <a href="#" class="text-white text-2xl"><i class="bi bi-tiktok"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>
