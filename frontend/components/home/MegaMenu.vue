<template>
    <div class="megamenu" role="menu">
        <div class="container">
            <div class="tw-grid tw-grid-cols-4 tw-gap-8 tw-py-6 tw-px-4">
                <!-- Bộ sưu tập -->
                <div>
                    <h6 class="tw-font-bold tw-text-gray-800 tw-mb-4">Bộ sưu tập</h6>
                    <ul class="tw-space-y-2">
                        <li>
                            <NuxtLink to="/collections/ao-khoac" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Áo
                                khoác</NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/collections/ao-so-mi" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Áo sơ
                                mi</NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/collections/ao-thun" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Áo thun
                            </NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/collections/quan-dai" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Quần
                                dài</NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/collections/quan-jeans" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Quần
                                jeans</NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/collections/quan-short" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Quần
                                short</NuxtLink>
                        </li>
                    </ul>
                </div>
                <!-- Mục theo dịp -->
                <div>
                    <h6 class="tw-font-bold tw-text-gray-800 tw-mb-4">Mục theo dịp</h6>
                    <ul class="tw-space-y-2">
                        <li>
                            <NuxtLink to="/occasions/dao-pho" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Dạo phố
                            </NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/occasions/di-bien" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Đi biển
                            </NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/occasions/du-lich" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Du lịch
                            </NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/occasions/di-choi" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Đi chơi
                            </NuxtLink>
                        </li>
                    </ul>
                </div>
                <!-- Áo nam thời trang -->
                <div>
                    <h6 class="tw-font-bold tw-text-gray-800 tw-mb-4">Áo nam thời trang</h6>
                    <ul class="tw-space-y-2">
                        <li>
                            <NuxtLink to="/fashion/ao-the-thao" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Áo thể
                                thao</NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/fashion/ao-so-mi" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Áo sơ mi
                            </NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/fashion/ao-khoac" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Áo khoác
                            </NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/fashion/ao-thun" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Áo thun
                            </NuxtLink>
                        </li>
                    </ul>
                </div>
                <!-- Đồ mặc nhà -->
                <div>
                    <h6 class="tw-font-bold tw-text-gray-800 tw-mb-4">Đồ mặc nhà</h6>
                    <ul class="tw-space-y-2">
                        <li>
                            <NuxtLink to="/homewear/do-dai" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Đồ dài
                            </NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/homewear/do-ngan" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Đồ ngắn
                            </NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/homewear/do-xuan-he" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Đồ xuân
                                - hè</NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/homewear/do-thu-dong" class="tw-text-gray-600 hover:tw-text-[#81AACC]">Đồ thu
                                - đông</NuxtLink>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.megamenu {
    visibility: hidden;
    opacity: 0;
    position: absolute;
    left: 50%;
    transform: translateX(-50%) translateY(10px);
    width: 100%;
    max-width: 1200px;
    margin-top: 10px;
    padding: 1.5rem 2rem;
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
    z-index: 2000;
    transition: all 0.3s ease;
}

.megamenu::before {
    content: '';
    position: absolute;
    top: -10px;
    left: 0;
    right: 0;
    height: 10px;
}

.megamenu::after {
    content: '';
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translateX(-50%) rotate(45deg);
    width: 16px;
    height: 16px;
    background-color: white;
    box-shadow: -2px -2px 5px rgba(0, 0, 0, 0.04);
}

.megamenu h6 {
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.megamenu ul li a {
    display: block;
    padding: 0.375rem 0;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.megamenu ul li a:hover {
    padding-left: 0.5rem;
    color: #2563eb;
}
</style>