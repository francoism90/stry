import { configureEcho } from '@laravel/echo-vue'

export interface LaravelEchoConfig {
  reverbKey: string
  reverbHost: string
  reverbPort: number
  reverbScheme: string
}

export function initializeEcho(envConfig?: LaravelEchoConfig) {
  if (import.meta.env.SSR) {
    configureEcho({ broadcaster: 'null' })
    return
  }

  const key = envConfig?.reverbKey || import.meta.env.VITE_REVERB_APP_KEY || 'app-key'
  const wsHost = envConfig?.reverbHost || import.meta.env.VITE_REVERB_HOST || 'localhost'
  const wsPort = envConfig?.reverbPort || Number(import.meta.env.VITE_REVERB_PORT) || 6001
  const scheme = envConfig?.reverbScheme || import.meta.env.VITE_REVERB_SCHEME || 'http'

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
