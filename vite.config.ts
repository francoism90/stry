import { wayfinder } from '@laravel/vite-plugin-wayfinder'
import ui from '@nuxt/ui/vite'
import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import { fileURLToPath, URL } from 'node:url'
import { defineConfig, loadEnv } from 'vite'
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
          dashboardPanel: {
            slots: {
              body: 'overflow-y-clip',
            },
          },
          dashboardNavbar: {
            slots: {
              root: 'bg-default sticky top-0 z-50 w-full',
            },
          },
        },
      }),
    ],
  }
})
