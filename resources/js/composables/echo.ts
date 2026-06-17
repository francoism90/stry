import { usePage } from '@inertiajs/vue3'
import { configureEcho } from '@laravel/echo-vue'
import type Echo from 'laravel-echo'
import { computed, shallowRef, toRaw, watchEffect } from 'vue'

interface EchoPageProps {
  key: string
  host: string
  port: number
  scheme: string
}

const echo = shallowRef<Echo<'pusher'> | null>(null)

export function useEcho() {
  const config = computed(() => usePage().props.echo as EchoPageProps | null)

  watchEffect((onCleanup) => {
    if (typeof window === 'undefined' || !config.value || !config.value.key) return

    const cleanConfig = toRaw(config.value)

    echo.value = configureEcho({
      broadcaster: 'reverb',
      key: cleanConfig.key,
      wsHost: cleanConfig.host,
      wsPort: cleanConfig.port,
      wssPort: cleanConfig.port,
      forceTLS: cleanConfig.scheme === 'https',
      enabledTransports: ['ws', 'wss'],
      disableStats: true,
      authEndpoint: '/broadcasting/auth',
    }) as unknown as Echo<'pusher'>

    onCleanup(() => {
      if (echo.value) {
        echo.value.connector?.disconnect()
        echo.value = null
      }
    })
  })

  return {
    config,
    echo,
  }
}
