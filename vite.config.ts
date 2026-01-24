import { wayfinder } from '@laravel/vite-plugin-wayfinder'
import ui from '@nuxt/ui/vite'
import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import { fileURLToPath, URL } from 'node:url'
import { defineConfig, loadEnv } from 'vite'
import { VitePWA } from 'vite-plugin-pwa'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')

  return {
    server: {
      host: '0.0.0.0',
      port: 5173,
      strictPort: true,
      hmr: { host: env.VITE_HMR_HOST, clientPort: 443, protocol: 'wss' },
      watch: {
        ignored: ['**/storage/framework/views/**'],
      },
    },
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        '~': fileURLToPath(new URL('./node_modules', import.meta.url)),
        '!': fileURLToPath(new URL('./vendor', import.meta.url)),
      },
    },
    plugins: [
      laravel({
        input: ['resources/js/app.ts'],
        ssr: 'resources/js/ssr.ts',
        refresh: true,
      }),
      vue({
        template: {
          compilerOptions: {
            isCustomElement: (tag) => tag.startsWith('media-'),
          },
          transformAssetUrls: {
            base: null,
            includeAbsolute: false,
          },
        },
      }),
      vueDevTools(),
      tailwindcss(),
      wayfinder(),
      ui({
        router: 'inertia',
        ui: {
          colors: {
            primary: 'purple',
            secondary: 'gray',
            neutral: 'zinc',
          },
          input: {
            slots: {
              root: 'w-full',
            },
          },
          dashboardNavbar: {
            slots: {
              root: 'bg-default sticky top-0 z-50 w-full',
            },
          },
        },
      }),
      VitePWA({
        registerType: 'autoUpdate',
        injectRegister: false,
        buildBase: '/build/',
        scope: '/',
        base: '/',
        srcDir: 'resources/js',
        outDir: 'public/build',
        manifest: {
          name: 'stry',
          short_name: 'stry',
          description: 'A streaming platform built with Laravel and Inertia.js',
          theme_color: '#ad46ff',
          background_color: '#1b1718',
          categories: ['video', 'streaming', 'series', 'movies', 'entertainment'],
          display_override: ['fullscreen', 'minimal-ui'],
          display: 'standalone',
          orientation: 'natural',
          scope: '/',
          start_url: '/',
          id: '/',
          icons: [
            {
              src: '/storage/images/android-chrome-192x192.png',
              sizes: '192x192',
              type: 'image/png',
            },
            {
              src: '/storage/images/android-chrome-512x512.png',
              sizes: '512x512',
              type: 'image/png',
            },
          ],
          screenshots: [
            {
              src: '/storage/images/android-chrome-512x512.png',
              sizes: '512x512',
              type: 'image/png',
              form_factor: 'narrow',
            },
            {
              src: '/storage/images/android-chrome-512x512.png',
              sizes: '512x512',
              type: 'image/png',
              form_factor: 'wide',
            },
          ],
        },
        workbox: {
          globPatterns: ['**/*.{js,css,html,svg,png,ico,woff2}'],
          cleanupOutdatedCaches: true,
          clientsClaim: true,
          navigateFallback: null,
          navigateFallbackDenylist: [/^\/api/],
        },
        devOptions: {
          enabled: false,
          navigateFallback: undefined,
          suppressWarnings: true,
          type: 'module',
        },
      }),
    ],
  }
})
