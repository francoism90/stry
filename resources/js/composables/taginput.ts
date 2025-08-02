import { index } from '@/actions/App/Api/Tags/Controllers/TagController'
import type { Model, Paginator } from '@/types'
import { http } from '@/utils/http'
import { type QueryParams } from '@/wayfinder'
import { computed, readonly, ref, toValue, watchEffect, type MaybeRefOrGetter } from 'vue'

export function useTagInput(selected?: MaybeRefOrGetter<Model[]>) {
  const state = ref<Paginator>()
  const items = ref<Model[]>([])

  const filter = (values: Model[]) => values.filter((item, index, self) => index === self.findIndex((o) => o.id === item.id))

  const query = async (query?: QueryParams) => {
    const { data } = await http.get<Paginator>(index.url({ query }))
    state.value = toValue(data)
  }

  const data = computed(() => filter([...items.value, ...(state.value?.data || [])]))

  watchEffect(async () => {
    items.value = toValue(selected || [])
  })

  return {
    state: readonly(state),
    data,
    query,
  }
}
