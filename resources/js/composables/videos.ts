import type { Videos } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { computed, readonly, ref, toValue, watchEffect } from 'vue'

export function useVideos() {
  const state = ref<Videos>()

  const data = computed(() => state.value?.data || [])
  const currentPage = computed(() => state.value?.current_page || 1)
  const prevPage = computed(() => state.value?.prev_cursor || currentPage.value - 1)
  const nextPage = computed(() => state.value?.next_cursor || currentPage.value + 1)
  const hasPages = computed(() => Boolean(state.value?.next_cursor?.length || state.value?.next_page_url?.length))

  watchEffect(async () => {
    state.value = toValue(usePage().props.items as Videos)
  })

  return {
    state: readonly(state),
    data,
    currentPage,
    prevPage,
    nextPage,
    hasPages,
  }
}
