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
      plugins: ['eslint', 'typescript', 'unicorn', 'oxc', 'vue'],
      categories: {
        correctness: 'error',
      },
      env: {
        browser: true,
        builtin: true,
      },
      ignorePatterns: [
        '**/node_modules/**',
        '**/vendor/**',
        '**/src/**',
        '**/public/**',
        '**/storage/**',
        '**/bootstrap/**',
        '**/config/**',
        '**/database/**',
        '**/routes/**',
        '**/tests/**',
        '**/resources/js/actions/**',
        '**/resources/js/routes/**',
        '**/resources/js/wayfinder/**',
        '**/website/**',
      ],
      rules: {
        'no-array-constructor': 'error',
        'typescript/ban-ts-comment': 'error',
        'typescript/no-empty-object-type': 'error',
        'typescript/no-explicit-any': 'error',
        'typescript/no-namespace': 'error',
        'typescript/no-require-imports': 'error',
        'typescript/no-unnecessary-type-constraint': 'error',
        'typescript/no-unsafe-function-type': 'error',
        'vite-plus/prefer-vite-plus-imports': 'error',
      },
      overrides: [
        {
          files: ['**/*.ts', '**/*.tsx', '**/*.mts', '**/*.cts', '**/*.vue'],
          rules: {
            'constructor-super': 'off',
            'getter-return': 'off',
            'no-class-assign': 'off',
            'no-const-assign': 'off',
            'no-dupe-class-members': 'off',
            'no-dupe-keys': 'off',
            'no-func-assign': 'off',
            'no-import-assign': 'off',
            'no-new-native-nonconstructor': 'off',
            'no-obj-calls': 'off',
            'no-redeclare': 'off',
            'no-setter-return': 'off',
            'no-this-before-super': 'off',
            'no-undef': 'off',
            'no-unreachable': 'off',
            'no-unsafe-negation': 'off',
            'no-var': 'error',
            'no-with': 'off',
            'prefer-const': 'error',
            'prefer-rest-params': 'error',
            'prefer-spread': 'error',
          },
        },
        {
          files: ['resources/**/__tests__/**'],
          rules: {
            'typescript/unbound-method': 'off',
            'vitest/expect-expect': 'error',
            'vitest/no-commented-out-tests': 'error',
            'vitest/no-conditional-expect': 'error',
            'vitest/no-disabled-tests': 'error',
            'vitest/no-focused-tests': 'error',
            'vitest/no-identical-title': 'error',
            'vitest/no-import-node-test': 'error',
            'vitest/no-interpolation-in-snapshots': 'error',
            'vitest/no-mocks-import': 'error',
            'vitest/no-standalone-expect': 'error',
            'vitest/no-unneeded-async-expect-function': 'error',
            'vitest/prefer-called-exactly-once-with': 'error',
            'vitest/require-local-test-context-for-concurrent-snapshots': 'error',
            'vitest/valid-describe-callback': 'error',
            'vitest/valid-expect': 'error',
            'vitest/valid-expect-in-promise': 'error',
            'vitest/valid-title': 'error',
          },
          plugins: ['vitest'],
        },
      ],
      options: {
        denyWarnings: true,
        typeAware: true,
      },
      jsPlugins: [
        {
          name: 'vite-plus',
          specifier: 'vite-plus/oxlint-plugin',
        },
      ],
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
        '.junie',
        '.mcp.json',
        '.remember',
        '/AGENTS.md',
        '/CLAUDE.md',
        'boost.json',
        'skills-lock.json',
        '.github',
        'composer.lock',
        'package-lock.json',
        'pnpm-lock.yaml',
        '*.log',
        '*.log.*',
        '*.sql',
        '*.sql.gz',
        '*.sqlite',
        '/data',
        '/resources/js/actions',
        '/resources/js/routes',
        '/resources/js/wayfinder',
        'auto-imports.d.ts',
        'components.d.ts',
        'env.d.ts',
        '/bootstrap/ssr',
        '/public',
        '/storage',
        '/vendor',
        '/website/.docusaurus',
        '/website/build',
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
