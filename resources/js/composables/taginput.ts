import { index } from '@/actions/App/Api/Tags/Controllers/TagController'
import type { Tag, TagMenuItem, Tags } from '@/types'
import { http } from '@/utils/http'
import { type QueryParams } from '@/wayfinder'
import { computed, readonly, ref, toValue, watchEffect, type MaybeRefOrGetter } from 'vue'

const state = ref<Tag[]>([])

export function useTagInput(selected: MaybeRefOrGetter<Tag[]> = []) {
  const filter = (items: Tag[]) => items.filter((item, index, self) => index === self.findIndex((o) => o.id === item.id))

  const results = computed(() => state.value as TagMenuItem[])

  const query = async (query?: QueryParams) => {
    const { data } = await http.get<Tags>(index.url({ query }))

    // Merge the fetched data with the current state
    state.value = filter([...state.value, ...(data.data || [])])
  }

  watchEffect(async () => {
    state.value = filter([...state.value, ...toValue(selected)])
  })

  return {
    state: readonly(state),
    results,
    query,
  }
}
