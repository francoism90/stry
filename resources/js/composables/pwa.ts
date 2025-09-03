import { useRegisterSW } from 'virtual:pwa-register/vue'
import { ref } from 'vue'

export function useServiceWorker() {
  const activated = ref(false)
  const period = ref(60 * 60 * 1000) // 60 minutes

  const register = () =>
    useRegisterSW({
      immediate: true,
      onRegisteredSW(swUrl: string, r: ServiceWorkerRegistration) {
        if (r?.active?.state === 'activated') {
          activated.value = true
          registerPeriodicSync(swUrl, r)
        } else if (r?.installing) {
          r.installing.addEventListener('statechange', (e) => {
            const sw = e.target as ServiceWorker
            activated.value = sw.state === 'activated'
            if (activated.value) registerPeriodicSync(swUrl, r)
          })
        }
      },
    })

  const registerPeriodicSync = (swUrl: string, r: ServiceWorkerRegistration) => {
    if (period.value <= 0) return

    setInterval(async () => {
      if ('onLine' in navigator && !navigator.onLine) return

      const resp = await fetch(swUrl, {
        cache: 'no-store',
        headers: {
          cache: 'no-store',
          'cache-control': 'no-cache',
        },
      })

      if (resp?.status === 200) await r.update()
    }, period.value)
  }

  return {
    register,
  }
}
