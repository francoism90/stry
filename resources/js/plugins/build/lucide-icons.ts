import type { IconifyJSON } from '@iconify/vue'
import { globSync, readFileSync } from 'node:fs'
import { createRequire } from 'node:module'
import type { Plugin } from 'vite'

const virtualId = 'virtual:lucide-icons'
const resolvedId = `\0${virtualId}`

/**
 * Files to scan for icon names. Most icons are referenced as `i-lucide-<name>`;
 * the Shaka player plugin maps its icons to bare Lucide names instead, so every
 * quoted kebab-case string in it counts as a candidate (unknown names are ignored).
 */
const prefixedSources = [
  'resources/js/**/*.{vue,ts}',
  'resources/views/**/*.blade.php',
  'src/**/*.php',
  // Nuxt UI's own default icons (chevrons, close, loader, ...).
  'node_modules/@nuxt/ui/dist/shared/*.mjs',
]

const bareSources = ['resources/js/plugins/shaka/*.ts']

/**
 * Serves `virtual:lucide-icons`: the Lucide icon set, reduced in production
 * builds to only the icons the app, Nuxt UI and the Shaka player reference.
 * The full set is ~650 KB.
 *
 * Dev serves the full set so new icons work without restarting Vite. An icon
 * the scan misses (e.g. a name built at runtime) still renders in production,
 * because @iconify/vue falls back to the Iconify API.
 */
export function lucideIcons(): Plugin {
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

      const require = createRequire(import.meta.url)
      const collection = JSON.parse(
        readFileSync(require.resolve('@iconify-json/lucide/icons.json'), 'utf8'),
      ) as IconifyJSON

      if (!isBuild) {
        return `export default ${JSON.stringify(collection)}`
      }

      const names = new Set<string>([
        ...scan(prefixedSources, /i-lucide-([a-z0-9]+(?:-[a-z0-9]+)*)/g),
        ...scan(bareSources, /['"]([a-z0-9]+(?:-[a-z0-9]+)*)['"]/g),
      ])

      return `export default ${JSON.stringify(subset(collection, names))}`
    },
  }
}

function* scan(patterns: string[], pattern: RegExp): Generator<string> {
  for (const file of globSync(patterns)) {
    for (const [, name] of readFileSync(file, 'utf8').matchAll(pattern)) {
      yield name!
    }
  }
}

/**
 * Keeps only the given icons, plus the parents of any aliases among them.
 */
function subset(collection: IconifyJSON, names: Iterable<string>): IconifyJSON {
  const icons: IconifyJSON['icons'] = {}
  const aliases: NonNullable<IconifyJSON['aliases']> = {}

  const add = (name: string): void => {
    if (collection.icons[name]) {
      icons[name] = collection.icons[name]

      return
    }

    const alias = collection.aliases?.[name]

    if (alias && !aliases[name]) {
      aliases[name] = alias
      add(alias.parent)
    }
  }

  for (const name of names) {
    add(name)
  }

  return { ...collection, icons, aliases }
}
