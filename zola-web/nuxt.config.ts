// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },

  modules: ['@pinia/nuxt', '@vite-pwa/nuxt'],

  css: ['~/assets/css/main.css'],

  app: {
    head: {
      title: 'Zola',
      link: [
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
        },
      ],
    },
  },

  runtimeConfig: {
    public: {
      apiUrl: process.env.NUXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1',
    },
  },

  pwa: {
    registerType: 'autoUpdate',
    manifest: {
      name: 'Zola',
      short_name: 'Zola',
      description: 'Gestion des points Orange Money et MTN MoMo',
      theme_color: '#F56001',
      background_color: '#F4F4F5',
      display: 'standalone',
      lang: 'fr',
      icons: [
        {
          src: 'pwa-192x192.png',
          sizes: '192x192',
          type: 'image/png',
        },
        {
          src: 'pwa-512x512.png',
          sizes: '512x512',
          type: 'image/png',
        },
        {
          src: 'pwa-512x512.png',
          sizes: '512x512',
          type: 'image/png',
          purpose: 'any maskable',
        },
      ],
    },
    workbox: {
      navigateFallback: '/',
    },
    devOptions: {
      enabled: true,
      type: 'module',
    },
  },
})
