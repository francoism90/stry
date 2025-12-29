import { index } from '@/actions/App/Api/Tags/Controllers/TagController'
import type { Tag, TagCollection } from '@/types'
import { http } from '@/utils/http'
import { unique } from '@/utils/model'
import { type RouteQueryOptions } from '@/wayfinder'
import { computed, readonly, ref, toValue, watchEffect, type MaybeRefOrGetter } from 'vue'

export function useTags(tags?: MaybeRefOrGetter<Tag[]>) {
  const state = ref<TagCollection>()
  const ready = ref(false)
  const preselect = ref<Tag[]>([])

  const items = computed(() => unique([...preselect.value, ...(state.value?.data || [])]))

  const query = async (options?: RouteQueryOptions) => http.get<TagCollection>(index.url(options))

  const filter = async (options?: RouteQueryOptions) => {
    try {
      const { data } = await query(options)
      state.value = Object.assign(state.value || {}, data)
    } finally {
      ready.value = true
    }
  }

  watchEffect(async () => {
    preselect.value = toValue(tags || [])

    // Preload tags
    if (!ready.value) {
      await filter()
    }
  })

  return {
    state: readonly(state),
    items,
    filter,
  }
}
