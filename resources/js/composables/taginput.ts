import { index } from '@/actions/App/Api/Tags/Controllers/TagController'
import type { Tag, Tags } from '@/types'
import { http } from '@/utils/http'
import { type QueryParams } from '@/wayfinder'
import { computed, readonly, shallowRef, toValue, watchEffect, type MaybeRefOrGetter } from 'vue'

export function useTagInput(selected?: MaybeRefOrGetter<Tag[]>) {
  const state = shallowRef<Tags>()
  const items = shallowRef<Tag[]>([])

  const data = computed(() => filter([...items.value, ...(state.value?.data || [])]))

  const filter = (values: Tag[]) => values.filter((item, index, self) => index === self.findIndex((o) => o.id === item.id))

  const query = async (query?: QueryParams) => {
    const { data } = await http.get<Tags>(index.url({ query }))
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
