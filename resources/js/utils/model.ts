import type { Model } from '@/types'

export const unique = (values: Model[]) =>
  values.filter((item, index, self) => index === self.findIndex((o) => o.id === item.id))
