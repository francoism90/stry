import { index } from '@/actions/App/Api/Tags/Controllers/TagController'
import type { Tag, Tags } from '@/types'
import { http } from '@/utils/http'
import { type QueryParams } from '@/wayfinder'
import { computed, readonly, ref, toValue, watchEffect, type MaybeRefOrGetter } from 'vue'

export function useTagInput(selected?: MaybeRefOrGetter<Tag[]>) {
  const state = ref<Tags>()
  const items = ref<Tag[]>([])

  const filter = (values: Tag[]) => values.filter((item, index, self) => index === self.findIndex((o) => o.id === item.id))

  const query = async (query?: QueryParams) => {
    const { data } = await http.get<Tags>(index.url({ query }))
    state.value = toValue(data)
  }

  const data = computed(() => filter([...items.value, ...(state.value?.data || [])]))
  const currentPage = computed(() => state.value?.current_page || 1)
  const nextPage = computed(() => state.value?.next_page_url || '')
  const hasPages = computed(() => !!nextPage.value)

  watchEffect(async () => {
    items.value = toValue(selected || [])
  })

  return {
    state: readonly(state),
    data,
    currentPage,
    nextPage,
    hasPages,
    query,
  }
}
