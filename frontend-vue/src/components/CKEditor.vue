<!-- components/CKEditor.vue -->
<template>
    <div ref="editorRef"></div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'

const props = defineProps({
    modelValue: String
})
const emit = defineEmits(['update:modelValue'])

const editorRef = ref(null)
let editorInstance = null
const isEditorReady = ref(false)

onMounted(async () => {
    const ClassicEditor = (await import('@ckeditor/ckeditor5-build-classic')).default

    ClassicEditor.create(editorRef.value, {
        placeholder: 'Nhập nội dung tại đây...'
    })
        .then(editor => {
            editorInstance = editor
            isEditorReady.value = true
            editor.setData(props.modelValue || '')

            editor.model.document.on('change:data', () => {
                emit('update:modelValue', editor.getData())
            })
        })
        .catch(e => {
            console.error('CKEditor init error:', e)
        })
})

watch(() => props.modelValue, async (newVal) => {
    await nextTick()
    try {
        if (
            isEditorReady.value &&
            editorInstance &&
            typeof editorInstance.getData === 'function'
        ) {
            if (newVal !== editorInstance.getData()) {
                editorInstance.setData(newVal || '')
            }
        }
    } catch (e) {
        console.warn('CKEditor setData error:', e)
    }
})

onBeforeUnmount(() => {
    if (editorInstance) {
        try {
            editorInstance.destroy()
        } catch (e) {
            console.warn('CKEditor destroy error:', e)
        }
        editorInstance = null
        isEditorReady.value = false
    }
})
</script>
