<template>
    <section class="tw-max-w-7xl tw-mx-auto tw-px-6 tw-py-12">
        <div class="tw-text-center tw-max-w-xl tw-mx-auto tw-mb-12">
            <h2 class="tw-text-gray-900 tw-font-semibold tw-text-2xl sm:tw-text-3xl tw-mb-2">
                Our latest blogs
            </h2>
            <p class="tw-text-gray-500 tw-text-sm sm:tw-text-base">
                A daily dose of knowledge will keep build you to your next stage
            </p>
        </div>
        <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-3 tw-gap-8">
            <article v-for="(blog, index) in blogs" :key="index">
                <img :alt="blog.alt" class="tw-w-full tw-h-48 sm:tw-h-56 tw-object-cover tw-rounded" loading="lazy"
                    :src="blog.image" width="600" />
                <time class="tw-block tw-mt-4 tw-mb-1 tw-text-xs tw-text-gray-500" :datetime="blog.date">
                    {{ formatDate(blog.date) }}
                </time>
                <a class="tw-text-blue-700 tw-font-semibold tw-text-sm sm:tw-text-base tw-leading-snug hover:tw-underline tw-inline-block tw-mb-1"
                    :href="blog.link">
                    {{ blog.title }}
                </a>
                <p class="tw-text-gray-500 tw-text-sm sm:tw-text-base tw-leading-relaxed">
                    {{ blog.excerpt }}
                </p>
            </article>
        </div>
    </section>
</template>

<script setup>
const blogs = [
    {
        title: '10 Tips for Capturing Stunning Landscape Photos',
        date: '2023-05-25',
        image: 'https://storage.googleapis.com/a1aa/image/0937c7ad-97ec-45f0-c69c-cdba4f61b684.jpg',
        alt: 'Landscape with rolling hills and trees under soft pink sky',
        excerpt: 'Landscape photography is one of the most popular genres among photographers.',
        link: '#'
    },
    {
        title: 'How Technology is Changing the Field of Architecture',
        date: '2023-06-25',
        image: 'https://storage.googleapis.com/a1aa/image/43aedf66-1620-4716-0b93-d93fec7c29d9.jpg',
        alt: 'Minimalist 3D render of arched doorways in neutral tones',
        excerpt: 'Macro photography is a fascinating genre that allows you to capture the intricate details.',
        link: '#'
    },
    {
        title: 'Preserving History: The Importance of Architectural Conservation and Restoration',
        date: '2023-12-25',
        image: 'https://storage.googleapis.com/a1aa/image/a6b704ba-4a4e-4794-5753-e7e477f9a1de.jpg',
        alt: 'Ornate archway with colorful tile mosaic patterns',
        excerpt: 'Preserving historic buildings and sites is an important consideration in the field of architecture',
        link: '#'
    },
    {
        title: 'Exploring the Beauty of Nature Photography',
        date: '2024-01-15',
        image: 'https://storage.googleapis.com/a1aa/image/12fb8a47-07cd-4e18-ef1a-e5c8ca90c806.jpg',
        alt: 'Sunset over a calm lake with mountains in the background',
        excerpt: 'Discover tips and techniques to capture the stunning beauty of natural landscapes and wildlife.',
        link: '#'
    },
    {
        title: 'Innovations in Sustainable Architecture',
        date: '2024-02-10',
        image: 'https://storage.googleapis.com/a1aa/image/4530f9e6-43ba-4f0f-3f85-d59f15aedcd3.jpg',
        alt: 'Modern office building with glass facade reflecting the sky',
        excerpt: 'Learn about the latest trends and innovations driving sustainability in modern architecture.',
        link: '#'
    },
    {
        title: 'The Art of Vintage Photography: Tips and Tricks',
        date: '2024-03-05',
        image: 'https://storage.googleapis.com/a1aa/image/f34a288c-b3e0-4585-bac4-cb25a220aa98.jpg',
        alt: 'Close-up of a vintage camera on a wooden table',
        excerpt: 'Explore how to create timeless vintage-style photos with simple techniques and equipment.',
        link: '#'
    }
];

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<style scoped>
/* Add any custom styles here if needed */
</style>