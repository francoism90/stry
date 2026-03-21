import type { AppearanceSettings, GeneralSettings } from '@/types'
import type { RequestPayload } from '@inertiajs/core'
import { router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

type SettingsMap = {
  general: GeneralSettings
  appearance: AppearanceSettings
}

export function useSettings<N extends keyof SettingsMap>(namespace: N) {
  type S = SettingsMap[N]

  const settings = computed(() => usePage().props[namespace] as S | undefined)

  function get<K extends keyof S>(key: K, defaultValue: S[K] | null = null): S[K] | null {
    if (!settings.value) return defaultValue
    return (settings.value as Record<K, S[K]>)[key] ?? defaultValue
  }

  function only<K extends keyof S>(...keys: K[]): { [P in K]: S[P] | null } {
    return Object.fromEntries(keys.map((k) => [k, get(k)])) as { [P in K]: S[P] | null }
  }

  function update(data: Partial<S>): void {
    const payload = { [namespace]: data } as RequestPayload
    router.patch('/api/v1/settings', payload, { preserveScroll: true })
  }

  return { settings, get, only, update }
}
