import type { Video, Videos } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { computed, readonly, ref, toValue, watchEffect } from 'vue'

const state = ref<Videos>()
const results = ref<Video[]>([])

export function useVideos() {
  const merge = (items: Video[]) => items.filter((item, index, self) => index === self.findIndex((o) => o.id === item.id))

  const data = computed(() => state.value?.data || [])
  const nextPage = computed(() => state.value?.next_page_url)
  const currentPage = computed(() => state.value?.current_page || 1)

  watchEffect(async () => {
    state.value = toValue(usePage().props.items as Videos)

    // Merge the initial data with results
    results.value = merge([...results.value, ...data.value])
  })

  return {
    state: readonly(state),
    results,
    data,
    nextPage,
    currentPage,
  }
}
