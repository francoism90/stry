import type { EchoConfig } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { tryOnScopeDispose, whenever } from '@vueuse/core'
import Echo from 'laravel-echo'
import { computed, shallowRef, toRaw, watchEffect } from 'vue'

type EchoInstance = InstanceType<typeof Echo>

const echo = shallowRef<EchoInstance | null>(null)

interface QueueItem {
  event: string
  cb: (data: Record<string, unknown>) => void
}

export function useEcho() {
  const config = computed(() => usePage().props.echo as EchoConfig | null)

  watchEffect((onCleanup) => {
    if (typeof window === 'undefined') return
    if (!config.value || !config.value.key) return

    const wsConfig = toRaw(config.value)

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
    if (typeof window === 'undefined') {
      return {
        listen: () => ({ listen: () => {} }),
      }
    }

    const listenersQueue: QueueItem[] = []
    const registeredEvents = new Set<string>()

    tryOnScopeDispose(() => {
      if (echo.value) {
        echo.value.leave(channelName)
      }
    })

    const chain = {
      listen: <T = Record<string, unknown>>(eventName: string, callback: (data: T) => void) => {
        registeredEvents.add(eventName)

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

    const stopWatch = whenever(
      echo,
      (instance) => {
        const targetChannel = instance.private(channelName)
        listenersQueue.forEach(({ event, cb }) => targetChannel.listen(event, cb))
        listenersQueue.length = 0
      },
      { immediate: true },
    )

    tryOnScopeDispose(() => {
      stopWatch()
    })

    return chain
  }

  return {
    config,
    echo,
    privateChannel,
  }
}
