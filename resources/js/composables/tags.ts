import { index } from '@/actions/App/Api/Tags/Controllers/TagController'
import type { Tag, TagCollection } from '@/types'
import { uniqueModels } from '@/utils/model'
import { type RouteQueryOptions } from '@/wayfinder'
import { useHttp, usePage } from '@inertiajs/vue3'
import { computed, readonly, ref, toValue, watchEffect, type MaybeRefOrGetter } from 'vue'

const isServer = import.meta.env.SSR

export function useTags(tags?: MaybeRefOrGetter<Tag[]>) {
  const state = ref<TagCollection>()
  const ready = ref(false)
  const http = useHttp<object, TagCollection>({})
  const types = computed(() => usePage().props.tags)

  const items = computed(() => uniqueModels([...toValue(tags || []), ...(state.value?.data || [])]))

  const filter = async (options?: RouteQueryOptions) => {
    if (isServer) {
      ready.value = true

      return
    }

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
    if (isServer || ready.value) {
      return
    }

    void filter()
  })

  return {
    state: readonly(state),
    items,
    types,
    filter,
  }
}
