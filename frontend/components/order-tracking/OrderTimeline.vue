<template>
    <div class="tw-relative tw-mb-8">
        <div class="tw-absolute tw-left-4 tw-top-0 tw-bottom-0 tw-w-0.5 tw-bg-gray-200"></div>
        <div class="tw-space-y-6">
            <div v-for="(step, index) in timeline" :key="index" class="tw-relative tw-pl-10">
                <div :class="[
                    'tw-absolute tw-left-0 tw-w-8 tw-h-8 tw-rounded-full tw-flex tw-items-center tw-justify-center',
                    step.completed ? 'tw-bg-green-500' : 'tw-bg-gray-200'
                ]">
                    <i :class="[step.icon, step.completed ? 'tw-text-white' : 'tw-text-gray-400']"></i>
                </div>
                <div>
                    <h3 :class="['tw-font-medium', step.completed ? '' : 'tw-text-gray-400']">
                        {{ step.title }}
                    </h3>
                    <p :class="['tw-text-sm', step.completed ? 'tw-text-gray-500' : 'tw-text-gray-400']">
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