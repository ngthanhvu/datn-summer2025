export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },
  css: [
    'bootstrap/dist/css/bootstrap.min.css',
    '~/assets/css/tailwind.css',
  ],
  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },
  vite: {
    server: {
      watch: {
        usePolling: true,
        interval: 100
      },
      allowedHosts: [
        '6e6b-14-236-154-186.ngrok-free.app'
      ],
      proxy: {
        '/api': {
          target: 'http://localhost:8000',
          changeOrigin: true,
        }
      }
    }
  },
  app: {
    head: {
      link: [
        {
          rel: 'stylesheet',
          href: 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
        },
        {
          rel: 'icon',
          type: 'image/x-icon',
          href: 'https://cdn-img.upanhlaylink.com/img/image_202505261a100993dadd1e94d860ec123578e3cf.jpg'
        }
      ]
    }
  },
  runtimeConfig: {
    public: {
      turnstileSiteKey: process.env.NUXT_TURNSTILE_SITE_KEY,
      apiBaseUrl: process.env.NUXT_API_BASE_URL,
    },
  }
})
