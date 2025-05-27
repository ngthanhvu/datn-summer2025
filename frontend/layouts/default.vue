<template>
  <div class="tw-flex tw-flex-col tw-min-h-screen">
    <HeaderHome />

    <div class="tw-flex-grow">
      <slot />
    </div>

    <FooterHome />
  </div>
</template>

<script setup>
useHead({
  link: [
    {
      rel: 'stylesheet',
      href: 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css'
    }
  ],
  script: [
    {
      src: 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js',
      integrity: 'sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO',
      crossorigin: 'anonymous'
    }
  ]
});
import HeaderHome from '~/components/home/HeaderHome.vue';
import FooterHome from '~/components/home/FooterHome.vue';
</script>
