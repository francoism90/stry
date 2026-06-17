import { configureEcho } from '@laravel/echo-vue'
import { onMounted } from 'vue'

declare global {
  interface Window {
    LaravelEchoConfig?: {
      key: string
      host: string
      port: number
      scheme: string
    }
  }
}

export function useEcho() {
  onMounted(() => {
    // SSR ignores this hook completely, so it only fires in the browser
    const config = window.LaravelEchoConfig

    if (!config) {
      console.warn('Echo config is missing from the global window context.')
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
      disableStats: true,
    })
  })
}
