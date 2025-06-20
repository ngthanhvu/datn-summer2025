<template>
    <div>

    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { createBlog } from '~/composables/useBlog'

const router = useRouter()
const toast = useToast()

const title = ref('')
const description = ref('')
const content = ref('')
const status = ref('draft')
const publishedAt = ref(null)
const image = ref(null)
const errorMessage = ref('')

const handleSubmit = async () => {
  errorMessage.value = ''
  
  const blogData = {
    title: title.value,
    description: description.value,
    content: content.value,
    status: status.value,
    published_at: publishedAt.value,
    image: image.value
  }

  try {
    await createBlog(blogData)
    toast.success('Blog đã được tạo thành công!')
    router.push('/admin/blogs')
  } catch (err) {
    if (
      err?.response?._data?.message?.includes('Duplicate entry') ||
      (err?.message && err.message.includes('Duplicate entry'))
    ) {
      errorMessage.value = 'Slug đã tồn tại, vui lòng chọn slug khác.'
    } else {
      errorMessage.value = err?.response?._data?.message || 'Đã có lỗi xảy ra khi tạo blog.'
    }
  }
}
</script>

<style></style>