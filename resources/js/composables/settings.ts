import UserSettingsController from '@/actions/App/Api/Users/Controllers/UserSettingsController'
import type { UserSettings } from '@/types'
import { useHttp, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useSettings<N extends keyof UserSettings>(namespace: N) {
  type S = UserSettings[N]

  const settings = computed(() => usePage().props.auth?.settings?.[namespace] as S | undefined)
  const http = useHttp({})

  function get<K extends keyof S>(key: K, defaultValue: S[K] | null = null): S[K] | null {
    if (!settings.value) return defaultValue
    return (settings.value as Record<K, S[K]>)[key] ?? defaultValue
  }

  function only<K extends keyof S>(...keys: K[]): { [P in K]: S[P] | null } {
    return Object.fromEntries(keys.map((k) => [k, get(k)])) as { [P in K]: S[P] | null }
  }

  function update(data: Partial<S>): void {
    http.transform(() => ({ [namespace]: data })).patch(UserSettingsController.url())
  }

  return { settings, get, only, update }
}
