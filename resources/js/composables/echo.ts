import { usePage } from '@inertiajs/vue3'
import { configureEcho } from '@laravel/echo-vue'
import { tryOnMounted, watchOnce } from '@vueuse/core'
import type Echo from 'laravel-echo'
import { computed, nextTick, shallowRef, toRaw } from 'vue'

interface EchoPageProps {
  key: string
  host: string
  port: number
  scheme: string
}

// Keep the global instance persistent across layout shifts
const echo = shallowRef<Echo<'pusher'> | null>(null)

export function useEcho() {
  // 1. Dynamic computed page lookup fixes layout tracking drops during page switches
  const config = computed(() => {
    const pageProps = usePage().props as unknown as { echo?: EchoPageProps }
    return pageProps.echo || null
  })

  const initialize = async () => {
    // Wait for the Inertia frame to finish loading deferred page variables completely
    await nextTick()

    if (!config.value || echo.value) return

    console.log('Initializing Echo with clean config:', toRaw(config.value))

    // 2. Extract raw values using toRaw to peel back the Vue Proxy layer
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
      // Forces authorization relative to current site, solving local cross-origin network errors
      authEndpoint: '/broadcasting/auth',
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
