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

  const channel = (channelName: string, isPrivate = true) => {
    // FIX: Explicitly type the callback parameter here as (data: any) => void
    const listenersQueue: { event: string; cb: (data: unknown) => void }[] = []

    const chain = {
      // FIX: Cast your generic callback assignment function so it maps cleanly to the raw parameters queue
      listen: <T = unknown>(eventName: string, callback: (data: T) => void) => {
        if (echo.value) {
          const targetChannel = isPrivate ? echo.value.private(channelName) : echo.value.channel(channelName)
          targetChannel.listen(eventName, callback)
        } else {
          // Storing it as a casted type here completely satisfies the TypeScript compiler
          listenersQueue.push({ event: eventName, cb: callback as (data: unknown) => void })
        }
        return chain
      },
    }

    if (!echo.value) {
      const unwatch = watch(echo, (instance) => {
        if (instance) {
          const targetChannel = isPrivate ? instance.private(channelName) : instance.channel(channelName)
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
    publicChannel: (name: string) => channel(name, false),
    privateChannel: (name: string) => channel(name, true),
  }
}
