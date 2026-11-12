import { index } from '@/actions/Modules/Api/Tags/Controllers/TagController'
import type { Tag, TagCollection } from '@/types'
import { uniqueModels } from '@/utils/model'
import { type RouteQueryOptions } from '@/wayfinder'
import { useHttp, usePage } from '@inertiajs/vue3'
import { computed, readonly, ref, toValue, watchEffect, type MaybeRefOrGetter } from 'vue'

const isServer = import.meta.env.SSR

const tagIcons: Record<string, string> = {
  serie: 'i-lucide-tv',
  studio: 'i-lucide-video',
  genre: 'i-lucide-shapes',
  person: 'i-lucide-user-round',
  language: 'i-lucide-languages',
}

export const tagIcon = (type: string | null | undefined): string => tagIcons[type ?? ''] ?? 'i-lucide-tag'

// Fetches the first tag suggestions once `enabled` turns true. Modals pass their open state, because
// every tag card renders an edit modal and fetching on mount would send one request per card.
export function useTags(tags?: MaybeRefOrGetter<Tag[]>, enabled: MaybeRefOrGetter<boolean> = true) {
  const state = ref<TagCollection>()
  let hasRequestedSuggestions = false
  const http = useHttp<object, TagCollection>({})
  const types = computed(() => usePage().props.tags)

  const items = computed(() => uniqueModels([...toValue(tags || []), ...(state.value?.data || [])]))

  const filter = async (options?: RouteQueryOptions) => {
    if (isServer) {
      return
    }

    await http.get(index.url(options), {
      onSuccess: (data) => {
        state.value = Object.assign(state.value || {}, data)
      },
    })
  }

  // Flagged before the request starts, so reopening a modal while it is still loading doesn't fetch twice.
  watchEffect(() => {
    if (isServer || hasRequestedSuggestions || !toValue(enabled)) {
      return
    }

    hasRequestedSuggestions = true
    void filter()
  })

  return {
    state: readonly(state),
    items,
    types,
    filter,
  }
}
