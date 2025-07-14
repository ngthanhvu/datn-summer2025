<template>
    <div class="megamenu" role="menu">
        <div class="container">
            <div class="tw-grid tw-grid-cols-4 tw-gap-8 tw-py-6 tw-px-4">
                <!-- Danh mục -->
                <div>
                    <h6 class="tw-font-bold tw-text-gray-800 tw-mb-4">Danh mục</h6>
                    <ul class="tw-space-y-2">
                        <li v-for="cat in categories.filter(c => !c.parent_id)" :key="cat.id">
                            <a href="#" @click.prevent="goToCategory(cat)"
                                class="tw-text-gray-600 hover:tw-text-[#81AACC]">{{ cat.name }}</a>
                        </li>
                    </ul>
                </div>
                <!-- Thương hiệu -->
                <div>
                    <h6 class="tw-font-bold tw-text-gray-800 tw-mb-4">Thương hiệu</h6>
                    <ul class="tw-space-y-2">
                        <li v-for="brand in brands" :key="brand.id">
                            <a href="#" @click.prevent="goToBrand(brand)"
                                class="tw-text-gray-600 hover:tw-text-[#81AACC]">{{ brand.name }}</a>
                        </li>
                    </ul>
                </div>
                <!-- Mục theo dịp (danh mục cha ngẫu nhiên 1) -->
                <div v-if="randomParentCategories[0]">
                    <h6 class="tw-font-bold tw-text-gray-800 tw-mb-4">{{ randomParentCategories[0].name }}</h6>
                    <ul class="tw-space-y-2">
                        <li v-for="cat in childCategories[0]" :key="cat.id">
                            <a href="#" @click.prevent="goToCategory(cat)"
                                class="tw-text-gray-600 hover:tw-text-[#81AACC]">{{ cat.name }}</a>
                        </li>
                    </ul>
                </div>
                <!-- Đồ mặc nhà (danh mục cha ngẫu nhiên 2) -->
                <div v-if="randomParentCategories[1]">
                    <h6 class="tw-font-bold tw-text-gray-800 tw-mb-4">{{ randomParentCategories[1].name }}</h6>
                    <ul class="tw-space-y-2">
                        <li v-for="cat in childCategories[1]" :key="cat.id">
                            <a href="#" @click.prevent="goToCategory(cat)"
                                class="tw-text-gray-600 hover:tw-text-[#81AACC]">{{ cat.name }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const categories = ref([])
const brands = ref([])
let router
if (process.client) {
    router = useRouter()
}

const randomParentCategories = ref([])
const childCategories = ref([[], []])

const fetchCategories = async () => {
    try {
        const res = await fetch('/api/categories')
        const data = await res.json()
        categories.value = data
        const parents = data.filter(cat => !cat.parent_id)
        const shuffled = parents.sort(() => 0.5 - Math.random())
        randomParentCategories.value = shuffled.slice(0, 2)
        childCategories.value = randomParentCategories.value.map(parent =>
            data.filter(cat => cat.parent_id === parent.id)
        )
    } catch (e) {
        categories.value = []
        randomParentCategories.value = []
        childCategories.value = [[], []]
    }
}
const fetchBrands = async () => {
    try {
        const res = await fetch('/api/brands')
        brands.value = await res.json()
    } catch (e) {
        brands.value = []
    }
}

const goToCategory = (cat) => {
    if (router) {
        router.push({ path: '/product', query: { category: cat.slug } })
    }
}

const goToBrand = (brand) => {
    if (router) {
        router.push({ path: '/product', query: { brand: brand.slug } })
    }
}


onMounted(() => {
    fetchCategories()
    fetchBrands()
})
</script>

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
    color: #81aacc;
}
</style>