import type { EchoConfig } from '@/types'
import { usePage } from '@inertiajs/vue3'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { computed, shallowRef, toRaw, watch, watchEffect } from 'vue'

declare global {
  interface Window {
    Pusher: typeof Pusher
  }
}

if (typeof window !== 'undefined') {
  window.Pusher = Pusher
}

// FIX: Pull type directly from instance fallback array to satisfy linter and module constraints
type NativeEchoInstance = InstanceType<typeof Echo>
const echo = shallowRef<NativeEchoInstance | null>(null)

interface QueueItem {
  event: string
  cb: (data: Record<string, unknown>) => void
}

export function useEcho() {
  const config = computed(() => usePage().props.echo as EchoConfig | null)

  watchEffect((onCleanup) => {
    if (!config.value || !config.value.key) return

    const wsConfig = toRaw(config.value)
    console.log('Initializing Echo with config:', wsConfig)

    // Using core constructor boots the network socket stream instantly
    echo.value = new Echo({
      broadcaster: 'reverb',
      key: wsConfig.key,
      wsHost: wsConfig.host,
      wsPort: wsConfig.port,
      wssPort: wsConfig.port,
      forceTLS: wsConfig.scheme === 'https',
      enabledTransports: ['ws', 'wss'],
      disableStats: true,
      authEndpoint: '/broadcasting/auth',
    })

    onCleanup(() => {
      if (echo.value) {
        echo.value.disconnect()
        echo.value = null
      }
    })
  })

  const privateChannel = (channelName: string) => {
    const listenersQueue: QueueItem[] = []

    const chain = {
      listen: <T = Record<string, unknown>>(eventName: string, callback: (data: T) => void) => {
        if (echo.value) {
          echo.value.private(channelName).listen(eventName, callback)
        } else {
          listenersQueue.push({
            event: eventName,
            cb: callback as unknown as (data: Record<string, unknown>) => void,
          })
        }
        return chain
      },
    }

    if (!echo.value) {
      const unwatch = watch(echo, (instance) => {
        if (instance) {
          const targetChannel = instance.private(channelName)
          listenersQueue.forEach(({ event, cb }) => targetChannel.listen(event, cb))
          unwatch()
        }
      })
    }

    return chain
  }

  return {
    config,
    echo,
    privateChannel,
  }
}
