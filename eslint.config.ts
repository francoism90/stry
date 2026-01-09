import pluginVitest from '@vitest/eslint-plugin'
import skipFormatting from '@vue/eslint-config-prettier/skip-formatting'
import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript'
import pluginOxlint from 'eslint-plugin-oxlint'
import pluginVue from 'eslint-plugin-vue'
import { globalIgnores } from 'eslint/config'

// To allow more languages other than `ts` in `.vue` files, uncomment the following lines:
// import { configureVueProject } from '@vue/eslint-config-typescript'
// configureVueProject({ scriptLangs: ['ts', 'tsx'] })
// More info at https://github.com/vuejs/eslint-config-typescript/#advanced-setup

export default defineConfigWithVueTs(
  {
    name: 'app/files-to-lint',
    files: [
      'resources/js/**/*.{vue,ts,mts,tsx}',
      '*.config.{ts,mts}',
      'env.d.ts',
    ],
  },

  globalIgnores([
    '**/node_modules/**',
    '**/vendor/**',
    '**/public/**',
    '**/storage/**',
    '**/bootstrap/**',
    '**/database/**',
    '**/resources/js/actions/**',
    '**/resources/js/routes/**',
    '**/resources/js/wayfinder/**',
    '**/*.d.ts',
    '!env.d.ts',
  ]),

  ...pluginVue.configs['flat/essential'],
  vueTsConfigs.recommended,

  {
    ...pluginVitest.configs.recommended,
    files: ['resources/**/__tests__/*'],
  },

  skipFormatting,

  ...pluginOxlint.configs['flat/recommended'],
)
