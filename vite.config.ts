import inertia from '@inertiajs/vite'
import { wayfinder } from '@laravel/vite-plugin-wayfinder'
import ui from '@nuxt/ui/vite'
import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import { google } from 'laravel-vite-plugin/fonts'
import { fileURLToPath, URL } from 'node:url'
import { defineConfig, lazyPlugins, loadEnv } from 'vite-plus'
import { stripCss } from './resources/js/plugins/build/strip-css'

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')

  return {
    lint: {
      plugins: ['eslint', 'typescript', 'unicorn', 'oxc', 'vue', 'vitest'],
      jsPlugins: [
        {
          name: 'vite-plus',
          specifier: 'vite-plus/oxlint-plugin',
        },
      ],
      ignorePatterns: ['website/**'],
      rules: {
        'vite-plus/prefer-vite-plus-imports': 'error',
      },
      overrides: [
        {
          files: ['resources/js/**/__tests__/**'],
          rules: {
            'typescript/unbound-method': 'off',
          },
        },
      ],
      options: {
        denyWarnings: true,
        typeAware: true,
      },
    },
    fmt: {
      semi: false,
      singleQuote: true,
      singleAttributePerLine: true,
      printWidth: 120,
      sortPackageJson: false,
      sortTailwindcss: {
        stylesheet: './resources/css/app.css',
        functions: ['cva', 'clsx', 'ui', ':ui'],
        attributes: ['class', 'className', 'ui', ':ui'],
      },
      overrides: [
        {
          files: ['**/compose.{yml,yaml}', '**/docker-compose.{yml,yaml}'],
          options: { tabWidth: 4 },
        },
      ],
      ignorePatterns: [
        '.agents',
        '.ai',
        '.claude',
        '.github',
        '.mcp.json',
        '/AGENTS.md',
        '/CLAUDE.md',
        'boost.json',
        'skills-lock.json',
        '/public',
      ],
    },
    test: {
      include: ['resources/js/**/*.{test,spec}.ts'],
    },
    server: {
      host: '0.0.0.0',
      port: 5173,
      strictPort: true,
      hmr: { host: env.VITE_HMR_HOST, clientPort: 443, protocol: 'wss' },
      watch: {
        ignored: [
          '**/.agents/**',
          '**/.junie/**',
          '**/.cursor/**',
          '**/.claude/**',
          '**/vendor/**',
          '**/storage/framework/views/**',
          '**/storage/logs/**',
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
    plugins: lazyPlugins(() => [
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
    ]),
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
