import type { EchoConfig } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { tryOnScopeDispose, whenever } from '@vueuse/core'
import Echo from 'laravel-echo'
import { computed, shallowRef, toRaw, watch } from 'vue'

type NativeEchoInstance = InstanceType<typeof Echo>

const echo = shallowRef<NativeEchoInstance | null>(null)

interface QueueItem {
  event: string
  cb: (data: Record<string, unknown>) => void
}

export function useEcho() {
  const config = computed(() => usePage().props.echo as EchoConfig | null)

  const configKey = computed(() => {
    const wsConfig = config.value
    if (!wsConfig || !wsConfig.key) return null
    return `${wsConfig.key}:${wsConfig.host}:${wsConfig.port}:${wsConfig.scheme}`
  })

  watch(
    configKey,
    (key, _previousKey, onCleanup) => {
      if (typeof window === 'undefined') return
      if (!key || !config.value) return

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
    },
    { immediate: true },
  )

  const privateChannel = (channelName: string) => {
    if (typeof window === 'undefined') {
      const chainMock = {
        listen: () => chainMock,
      }
      return chainMock
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
