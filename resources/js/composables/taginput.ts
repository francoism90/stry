import { index } from '@/actions/App/Api/Tags/Controllers/TagController'
import type { Tag, TagCollection } from '@/types'
import { http } from '@/utils/http'
import { type QueryParams } from '@/wayfinder'
import { computed, readonly, ref, toValue, watchEffect, type MaybeRefOrGetter } from 'vue'

export function useTagInput(selected?: MaybeRefOrGetter<Tag[]>) {
  const state = ref<TagCollection>()
  const items = ref<Tag[]>([])

  const data = computed(() => mergeDeep([...items.value, ...(state.value?.data || [])]))

  const mergeDeep = (values: Tag[]) => values.filter((item, index, self) => index === self.findIndex((o) => o.id === item.id))

  const query = async (query?: QueryParams) => {
    const { data } = await http.get<TagCollection>(index.url({ query }))

    state.value = Object.assign(state.value || {}, data)
  }

  watchEffect(async () => {
    items.value = toValue(selected || [])

    await query()
  })

  return {
    state: readonly(state),
    data,
    query,
  }
}
