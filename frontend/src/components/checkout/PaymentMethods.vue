<template>
  <div class="mt-8">
    <h2 class="text-lg font-semibold mb-4">Phương thức thanh toán</h2>
    <div class="space-y-4">
      <div v-for="(method, index) in methods" :key="index"
        class="flex items-center p-3 border rounded-md cursor-pointer hover:bg-gray-50" :class="[
          !method.enabled ? 'opacity-50 cursor-not-allowed' : ''
        ]" :style="{
          borderColor: selectedMethod === index ? '#81AACC' : '#D1D5DB',
          borderWidth: '1px',
          backgroundColor: selectedMethod === index ? '#EFF6FF' : 'transparent'
        }" @click="method.enabled && $emit('select', index)">
        <input v-if="method.enabled" type="radio" :name="name" class="mr-3" :checked="selectedMethod === index">
        <img v-if="method.image" :src="method.image" :alt="method.title"
          class="w-[25px] h-[25px] object-contain mr-3" />
        <div>
          <div class="font-medium">{{ method.title }}</div>
          <div class="text-sm text-gray-500">
            {{ method.description }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  methods: {
    type: Array,
    required: true
  },
  selectedMethod: {
    type: Number,
    required: true
  },
  name: {
    type: String,
    default: 'payment'
  }
})

defineEmits(['select'])
</script>
