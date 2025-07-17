export default defineNuxtConfig({
  ssr: true,
  compatibilityDate: '2024-11-01',

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

  css: [
    'bootstrap/dist/css/bootstrap.min.css',
    '~/assets/css/tailwind.css',
    '~/assets/css/notyf.css',
  ],

  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },

  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt'
  ],

  runtimeConfig: {
    public: {
      turnstileSiteKey: process.env.NUXT_TURNSTILE_SITE_KEY,
      apiBaseUrl: process.env.NUXT_API_BASE_URL,
    },
  },

  build: {
    transpile: ['apexcharts']
  },

  vite: {
    server: {
      watch: {
        usePolling: true,
        interval: 100
      },
      proxy: {
        '/api': {
          target: process.env.NUXT_API_BASE_URL,
          changeOrigin: true,
        }
      }
    },
    optimizeDeps: {
      include: ['apexcharts']
    },
    build: {
      rollupOptions: {
        output: {
          manualChunks: {
            'vendor': ['vue', 'vue-router'],
            'ui': ['bootstrap', 'swiper'],
            'charts': ['apexcharts']
          }
        }
      }
    }
  },

  devtools: {
    enabled: true,
    timeline: {
      enabled: true
    }
  },

  experimental: {
    payloadExtraction: false
  },

  nitro: {
    experimental: {
      wasm: true
    },
    storage: {
      redis: {
        driver: 'redis',
      }
    }
  }
})
