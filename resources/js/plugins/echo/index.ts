import type { EchoConfig } from '@/types'
import { configureEcho } from '@laravel/echo-vue'

export function bootEcho(config: EchoConfig | undefined): void {
  // `useEcho()` throws when Echo is unconfigured, so SSR (no `window`) and
  // missing config fall back to the no-op `null` broadcaster.
  if (typeof window === 'undefined' || !config || !config.key) {
    configureEcho({ broadcaster: 'null' })

    return
  }

  configureEcho({
    broadcaster: 'reverb',
    key: config.key,
    wsHost: config.host,
    wsPort: config.port,
    wssPort: config.port,
    forceTLS: config.scheme === 'https',
    enabledTransports: ['ws', 'wss'],
  })
}
