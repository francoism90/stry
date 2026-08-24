import type { EchoConfig } from '@/types'
import { configureEcho } from '@laravel/echo-vue'

export function bootEcho(config: EchoConfig | undefined): void {
  if (typeof window === 'undefined' || !config || !config.key) return

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
