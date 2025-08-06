<template>
  <!-- COMPONENT FOOTER PAGES UPDATED - VERSION 2 -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <!-- Chính sách -->
    <div>
      <h3 class="text-lg font-semibold text-yellow-300 mb-4">CHÍNH SÁCH</h3>
      <ul class="space-y-2 text-gray-100">
        <li v-for="page in policyPages" :key="page.id">
          <RouterLink :to="`/trang/${page.slug}`" class="hover:text-yellow-300 transition-colors">
            {{ page.title }}
          </RouterLink>
        </li>
        <li v-if="policyPages.length === 0" class="text-gray-400 text-sm">
          Đang tải...
        </li>
      </ul>
    </div>

    <!-- Hỗ trợ khách hàng -->
    <div>
      <h3 class="text-lg font-semibold text-yellow-300 mb-4">HỖ TRỢ KHÁCH HÀNG</h3>
      <ul class="space-y-2 text-gray-100">
        <li v-for="page in supportPages" :key="page.id">
          <RouterLink :to="`/trang/${page.slug}`" class="hover:text-yellow-300 transition-colors">
            {{ page.title }}
          </RouterLink>
        </li>
        <li v-if="supportPages.length === 0" class="text-gray-400 text-sm">
          Đang tải...
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { usePages } from '../../composable/usePages'

const { getPagesByType } = usePages()

const policyPages = ref([])
const supportPages = ref([])

const loadFooterPages = async () => {
  try {
    // Load policy pages
    const policyResponse = await getPagesByType('policy')
    if (policyResponse && policyResponse.data) {
      policyPages.value = policyResponse.data.filter(page => page.status)
    }

    // Load support pages
    const supportResponse = await getPagesByType('support')
    if (supportResponse && supportResponse.data) {
      supportPages.value = supportResponse.data.filter(page => page.status)
    }
  } catch (error) {
    console.error('Error loading footer pages:', error)
  }
}

onMounted(() => {
  loadFooterPages()
})
</script> 