import { configureEcho } from '@laravel/echo-vue'

export interface LaravelEchoConfig {
  key: string
  host: string
  port: number
  scheme: string
}

export function initializeEcho(config?: LaravelEchoConfig) {
  if (import.meta.env.SSR) {
    configureEcho({ broadcaster: 'null' })
    return
  }

  const key = config?.key || import.meta.env.VITE_REVERB_APP_KEY || 'app-key'
  const wsHost = config?.host || import.meta.env.VITE_REVERB_HOST || 'localhost'
  const wsPort = config?.port || Number(import.meta.env.VITE_REVERB_PORT) || 6001
  const scheme = config?.scheme || import.meta.env.VITE_REVERB_SCHEME || 'http'

  configureEcho({
    broadcaster: 'reverb',
    key: key,
    wsHost: wsHost,
    wsPort: wsPort,
    wssPort: wsPort,
    forceTLS: scheme === 'https',
    enabledTransports: ['ws', 'wss'],
  })
}
