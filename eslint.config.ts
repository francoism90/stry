import skipFormatting from '@vue/eslint-config-prettier/skip-formatting'
import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript'
import pluginVue from 'eslint-plugin-vue'
import { globalIgnores } from 'eslint/config'

export default defineConfigWithVueTs(
  {
    name: 'app/files-to-lint',
    files: ['**/*.{ts,mts,tsx,vue}'],
  },

  globalIgnores([
    '**/vendor/**',
    '**/node_modules/**',
    '**/public/**',
    '**/bootstrap/ssr/**',
    '**/resources/js/actions/**',
    '**/resources/js/wayfinder/**',
    '**/storage/**',
    '**/dist/**',
    '**/.git/**',
    '**/.idea/**',
    'package-lock.json',
    'pnpm-lock.yaml',
    'pnpm-workspace.yaml',
  ]),

  pluginVue.configs['flat/essential'],
  vueTsConfigs.base,
  skipFormatting,
)
