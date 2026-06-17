import { usePage } from '@inertiajs/vue3'
import { configureEcho } from '@laravel/echo-vue'
import { tryOnMounted, watchOnce } from '@vueuse/core'
import type Echo from 'laravel-echo'
import { computed, shallowRef } from 'vue'

interface EchoPageProps {
  key: string
  host: string
  port: number
  scheme: string
}

const echo = shallowRef<Echo<'pusher'> | null>(null)

export function useEcho() {
  const props = usePage().props as unknown as { echo?: EchoPageProps }
  const config = computed(() => props.echo)

  const initialize = async () => {
    console.log('Initializing Echo with config:', config.value)

    if (!config.value || echo.value) return

    echo.value = configureEcho({
      broadcaster: 'reverb',
      key: config.value.key,
      wsHost: config.value.host,
      wsPort: config.value.port,
      wssPort: config.value.port,
      forceTLS: config.value.scheme === 'https',
      enabledTransports: ['ws', 'wss'],
      disableStats: true,
    }) as unknown as Echo<'pusher'>
  }

  const listen = <T = Record<string, unknown>>(channelName: string, eventName: string, callback: (data: T) => void) => {
    if (echo.value) {
      echo.value.channel(channelName).listen(eventName, callback)
      return
    }

    watchOnce(echo, (instance: Echo<'pusher'> | null) => {
      if (instance) {
        instance.channel(channelName).listen(eventName, callback)
      }
    })
  }

  tryOnMounted(() => {
    initialize()
  })

  return {
    config,
    echo,
    listen,
  }
}
