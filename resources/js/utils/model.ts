import type { Model } from '@/types'

export const uniqueModels = (values: Model[]) =>
  values.filter((item, index, self) => index === self.findIndex((o) => o.id === item.id))

export const modelFilters = (values?: Record<string, unknown>): string[] =>
  Object.entries(values ?? {})
    .filter(([, value]) => value != null && value !== false && value !== '')
    .map(([key]) => key)
