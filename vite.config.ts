import { wayfinder } from '@laravel/vite-plugin-wayfinder'
import ui from '@nuxt/ui/vite'
import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import laravel from 'laravel-vite-plugin'
import { fileURLToPath, URL } from 'node:url'
import { vite as vidstack } from 'vidstack/plugins'
import { defineConfig, loadEnv } from 'vite'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const host = new URL(env.VITE_APP_URL)?.hostname || 'stry.test'

  return {
    server: {
      host: '0.0.0.0',
      port: 5173,
      strictPort: true,
      hmr: { host: `vite.${host}`, clientPort: 443, protocol: 'wss' },
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
      wayfinder(),
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
      vueJsx(),
      vueDevTools(),
      tailwindcss(),
      vidstack(),
      ui({
        inertia: true,
        ui: {
          colors: {
            primary: 'purple',
            secondary: 'gray',
            neutral: 'zinc',
          },
          container: {
            base: 'max-w-6xl',
          },
          input: {
            slots: {
              root: 'w-full',
            },
          },
        },
      }),
    ],
    build: {
      rollupOptions: {
        output: {
          manualChunks: {
            http: ['axios'],
            broadcast: ['pusher-js', 'laravel-echo'],
            play: ['hls.js', 'vidstack'],
          },
        },
      },
    },
  }
})
