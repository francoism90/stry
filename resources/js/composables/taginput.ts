import { index } from '@/actions/App/Api/Tags/Controllers/TagController'
import type { Model, Paginator } from '@/types'
import { http } from '@/utils/http'
import { type QueryParams } from '@/wayfinder'
import { computed, readonly, shallowRef, toValue, watchEffect, type MaybeRefOrGetter } from 'vue'

export function useTagInput(selected?: MaybeRefOrGetter<Model[]>) {
  const state = shallowRef<Paginator>()
  const items = shallowRef<Model[]>([])

  const data = computed(() => filter([...items.value, ...(state.value?.data || [])]))

  const filter = (values: Model[]) => values.filter((item, index, self) => index === self.findIndex((o) => o.id === item.id))

  const query = async (query?: QueryParams) => {
    const { data } = await http.get<Paginator>(index.url({ query }))

    state.value = toValue(data)
  }

  watchEffect(async () => {
    if (!state.value) {
      await query({ sort: 'popularity' })
    }

    items.value = toValue(selected || [])
  })

  return {
    state: readonly(state),
    data,
    query,
  }
}
