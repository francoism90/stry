import type { EchoConfig } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { configureEcho } from '@laravel/echo-vue'
import { watchOnce } from '@vueuse/core'
import type Echo from 'laravel-echo'
import { computed, shallowRef, toRaw, watchEffect } from 'vue'

const echo = shallowRef<Echo<'pusher'> | null>(null)

export function useEcho() {
  const config = computed(() => usePage().props.echo as EchoConfig | null)

  // watchEffect handles tracking, initial boot, and hot-swap cleanups in one block
  watchEffect((onCleanup) => {
    if (!config.value) return

    const wsConfig = toRaw(config.value)
    console.log('Initializing Echo with config:', wsConfig)

    echo.value = configureEcho({
      broadcaster: 'reverb',
      key: wsConfig.key,
      wsHost: wsConfig.host,
      wsPort: wsConfig.port,
      wssPort: wsConfig.port,
      forceTLS: wsConfig.scheme === 'https',
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

  const listen = <T = Record<string, unknown>>(channelName: string, eventName: string, callback: (data: T) => void) => {
    if (echo.value) {
      echo.value.channel(channelName).listen(eventName, callback)
    } else {
      watchOnce(echo, (instance) => instance?.channel(channelName).listen(eventName, callback))
    }
  }

  return { config, echo, listen }
}
