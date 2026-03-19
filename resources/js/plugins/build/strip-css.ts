import type { Plugin } from 'vite'

/**
 * Strips CSS rules matching the given patterns from all built CSS assets.
 * Useful for removing third-party @font-face rules that would violate CSP.
 *
 * @param patterns - One or more regex patterns to strip from built CSS
 */
export function stripCss(...patterns: RegExp[]): Plugin {
  return {
    name: 'strip-css',
    generateBundle(_, bundle) {
      for (const file of Object.values(bundle)) {
        if (file.type !== 'asset' || !file.fileName.endsWith('.css') || typeof file.source !== 'string') {
          continue
        }

        for (const pattern of patterns) {
          file.source = file.source.replace(pattern, '')
        }
      }
    },
  }
}
