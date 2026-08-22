import inertia from '@inertiajs/vite'
import { wayfinder } from '@laravel/vite-plugin-wayfinder'
import ui from '@nuxt/ui/vite'
import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import { google } from 'laravel-vite-plugin/fonts'
import { fileURLToPath, URL } from 'node:url'
import { defineConfig, loadEnv } from 'vite'
import { stripCss } from './resources/js/plugins/build/strip-css'

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
        ignored: [
          '**/storage/framework/views/**',
          '**/storage/logs/**',
          '**/vendor/**',
          '**/node_modules/**',
          '**/docs/**',
        ],
      },
    },
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        '~': fileURLToPath(new URL('./node_modules', import.meta.url)),
        '!': fileURLToPath(new URL('./vendor', import.meta.url)),
      },
    },
    ssr: {
      // @nuxt/ui relies on Vite-only virtual modules (e.g. `#imports`) that
      // only resolve while bundling, so it must never be externalized for SSR.
      noExternal: ['@nuxt/ui'],
    },
    plugins: [
      laravel({
        input: ['resources/css/app.css', 'resources/js/app.ts'],
        refresh: true,
        fonts: [
          google('Geist', { weights: ['400', '500', '600', '700'], alias: 'geist' }),
          google('Geist Mono', { weights: ['400', '500', '600', '700'], alias: 'geist-mono' }),
        ],
      }),
      inertia({
        ssr: {
          port: 13714,
          cluster: true,
        },
      }),
      vue({
        template: {
          transformAssetUrls: {
            base: null,
            includeAbsolute: false,
          },
        },
      }),
      tailwindcss(),
      stripCss(
        // Remove Roboto @font-face from shaka-player's controls.css — violates CSP font-src 'self'
        /@font-face\{[^}]*font-family:Roboto[^}]*fonts\.gstatic\.com[^}]*\}/g,
      ),
      wayfinder({
        formVariants: true,
      }),
      ui({
        router: 'inertia',
        ui: {
          colors: {
            primary: 'purple',
            secondary: 'neutral',
            neutral: 'neutral',
          },
          input: {
            slots: {
              root: 'w-full',
            },
          },
          inputDate: {
            slots: {
              base: 'w-full',
            },
          },
          textarea: {
            slots: {
              root: 'w-full',
            },
          },
        },
      }),
    ],
    build: {
      chunkSizeWarningLimit: 1000,
      rollupOptions: {
        output: {
          manualChunks(id) {
            const chunk = (name: string, packages: string[]) =>
              packages.some((pkg) => id.includes(`node_modules/${pkg}`)) ? name : undefined

            return (
              chunk('player', ['shaka-player']) ??
              chunk('icons', ['@iconify']) ??
              chunk('ui', ['@nuxt/ui', '@nuxt/icon', 'reka-ui', '@internationalized']) ??
              chunk('core', ['vue', '@inertiajs', '@vueuse']) ??
              chunk('broadcasting', ['pusher-js', 'laravel-echo'])
            )
          },
        },
      },
    },
  }
})
