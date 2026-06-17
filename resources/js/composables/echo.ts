import { usePage } from '@inertiajs/vue3'
import { configureEcho } from '@laravel/echo-vue'
import type Echo from 'laravel-echo'
import { computed, shallowRef, toRaw, watch, watchEffect } from 'vue'

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

  const channel = (channelName: string) => {
    const chain = {
      listen: <T = unknown>(eventName: string, callback: (data: T) => void) => {
        if (echo.value) {
          echo.value.private(channelName).listen(eventName, callback)
        } else {
          const unwatch = watch(echo, (instance) => {
            if (instance) {
              instance.private(channelName).listen(eventName, callback)
              unwatch()
            }
          })
        }
        return chain
      },
    }

    return chain
  }

  return {
    config,
    echo,
    channel,
  }
}
