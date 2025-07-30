<template>
    <div class="relative mb-8">
        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
        <div class="space-y-6">
            <div v-for="(step, index) in timeline" :key="index" class="relative pl-10">
                <div :class="[
                    'absolute left-0 w-8 h-8 rounded-full flex items-center justify-center',
                    step.completed ? 'bg-green-500' : 'bg-gray-200'
                ]">
                    <i :class="[step.icon, step.completed ? 'text-white' : 'text-gray-400']"></i>
                </div>
                <div>
                    <h3 :class="['font-medium', step.completed ? '' : 'text-gray-400']">
                        {{ step.title }}
                    </h3>
                    <p :class="['text-sm', step.completed ? 'text-gray-500' : 'text-gray-400']">
                        {{ step.time }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    timeline: {
        type: Array,
        required: true
    }
})
</script>
