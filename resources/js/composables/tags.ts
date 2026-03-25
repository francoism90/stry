import { index } from '@/actions/App/Api/Tags/Controllers/TagController'
import type { Tag, TagCollection } from '@/types'
import { unique } from '@/utils/model'
import { type RouteQueryOptions } from '@/wayfinder'
import { useHttp } from '@inertiajs/vue3'
import { computed, readonly, ref, toValue, watchEffect, type MaybeRefOrGetter } from 'vue'

export function useTags(tags?: MaybeRefOrGetter<Tag[]>) {
  const state = ref<TagCollection>()
  const ready = ref(false)
  const http = useHttp<object, TagCollection>({})

  const items = computed(() => unique([...toValue(tags || []), ...(state.value?.data || [])]))

  const filter = async (options?: RouteQueryOptions) => {
    try {
      await http.get(index.url(options), {
        onSuccess: (data) => {
          state.value = Object.assign(state.value || {}, data)
        },
      })
    } finally {
      ready.value = true
    }
  }

  watchEffect(() => {
    if (!ready.value) {
      filter()
    }
  })

  return {
    state: readonly(state),
    items,
    filter,
  }
}
