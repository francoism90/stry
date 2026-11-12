import { getIcons } from '@iconify/utils'
import { globSync, readFileSync } from 'node:fs'
import { fileURLToPath } from 'node:url'
import type { Plugin } from 'vite-plus'

type LucideIconsOptions = {
  /**
   * Files that reference Lucide icons by their bare name (`'play'`) instead of
   * `i-lucide-play`. Every quoted kebab-case string in them counts as a
   * candidate; names that aren't Lucide icons are ignored.
   */
  bareNameSources?: string[]
}

/**
 * Serves `virtual:lucide-icons`: the Lucide icon set, reduced in production
 * builds to only the icons the app or Nuxt UI actually reference. The full
 * set is ~650 KB; scanning for `i-lucide-*` names keeps the bundle to a few KB.
 *
 * Dev serves the full set so new icons work without restarting Vite. An icon
 * missed by the scan (e.g. a name built at runtime) still renders in
 * production: @iconify/vue falls back to the Iconify API, which the CSP allows.
 */
export function lucideIcons({ bareNameSources = [] }: LucideIconsOptions = {}): Plugin {
  const virtualId = 'virtual:lucide-icons'
  const resolvedId = `\0${virtualId}`
  const sources = [
    'resources/js/**/*.{vue,ts}',
    'resources/views/**/*.blade.php',
    'src/**/*.php',
    // Nuxt UI's own default icons (chevrons, close, loader, ...).
    'node_modules/@nuxt/ui/dist/shared/*.mjs',
  ]

  let isBuild = false

  return {
    name: 'lucide-icons',
    configResolved(config) {
      isBuild = config.command === 'build'
    },
    resolveId(id) {
      return id === virtualId ? resolvedId : undefined
    },
    load(id) {
      if (id !== resolvedId) {
        return undefined
      }

      const collection = JSON.parse(
        readFileSync(fileURLToPath(import.meta.resolve('@iconify-json/lucide/icons.json')), 'utf8'),
      ) as Parameters<typeof getIcons>[0]

      if (!isBuild) {
        return `export default ${JSON.stringify(collection)}`
      }

      const names = new Set([
        ...scan(sources, /i-lucide-([a-z0-9]+(?:-[a-z0-9]+)*)/g),
        ...scan(bareNameSources, /['"]([a-z0-9]+(?:-[a-z0-9]+)*)['"]/g),
      ])

      return `export default ${JSON.stringify(getIcons(collection, [...names]))}`
    },
  }
}

function* scan(patterns: string[], pattern: RegExp): Generator<string> {
  if (patterns.length === 0) {
    return
  }

  for (const file of globSync(patterns)) {
    for (const [, name] of readFileSync(file, 'utf8').matchAll(pattern)) {
      yield name!
    }
  }
}
