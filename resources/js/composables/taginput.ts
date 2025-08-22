import { index } from '@/actions/App/Api/Tags/Controllers/TagController'
import type { Tag, Tags } from '@/types'
import { http } from '@/utils/http'
import { type QueryParams } from '@/wayfinder'
import { computed, readonly, ref, toValue, watchEffect, type MaybeRefOrGetter } from 'vue'

export function useTagInput(selected?: MaybeRefOrGetter<Tag[]>) {
  const state = ref<Tags>()
  const items = ref<Tag[]>([])

  const data = computed(() => mergeDeep([...items.value, ...(state.value?.data || [])]))

  const mergeDeep = (values: Tag[]) => values.filter((item, index, self) => index === self.findIndex((o) => o.id === item.id))

  const query = async (query?: QueryParams) => {
    if (!query) {
      query = { sort: 'popularity', page: 1 }
    }

    const { data } = await http.get<Tags>(index.url({ query }))
    state.value = data
  }

  watchEffect(async () => {
    items.value = toValue(selected || [])

    if (!state.value) {
      await query()
    }
  })

  return {
    state: readonly(state),
    data,
    query,
  }
}
