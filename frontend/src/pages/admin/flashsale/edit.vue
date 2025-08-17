<template>
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        <span class="ml-3">Đang tải dữ liệu flash sale...</span>
    </div>
    <div v-else-if="error" class="flex items-center justify-center min-h-screen">
        <div class="text-red-500 text-center">
            <p class="text-lg font-semibold mb-2">Lỗi tải dữ liệu</p>
            <p>{{ error }}</p>
            <button @click="retryLoad" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Thử lại
            </button>
        </div>
    </div>
    <FlashsaleForm v-else :editData="flashSaleData" />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import FlashsaleForm from '../../../components/admin/flashsale/FlashsaleForm.vue'
import { useFlashsale } from '../../../composable/useFlashsale'
import { useHead } from '@vueuse/head'

useHead({
    title: "Sửa flash sale | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Sửa flash sale"
        }
    ]
})

const route = useRoute()
const { getFlashSaleById } = useFlashsale()
const flashSaleData = ref(null)
const loading = ref(true)
const error = ref('')

const loadFlashSale = async () => {
    try {
        loading.value = true
        error.value = ''
        const flashSaleId = route.params.id
        const data = await getFlashSaleById(flashSaleId)
        flashSaleData.value = data
    } catch (err) {
        error.value = err.message || 'Không thể tải dữ liệu flash sale'
        console.error('Error loading flash sale:', err)
    } finally {
        loading.value = false
    }
}

const retryLoad = () => {
    loadFlashSale()
}

onMounted(loadFlashSale)
</script>
