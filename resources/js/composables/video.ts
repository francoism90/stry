import type { Video } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { formatDate, formatTimeAgoIntl } from '@vueuse/core'
import { computed } from 'vue'

export function useVideo() {
  const state = computed(() => usePage().props.video as Video)

  const created = computed(() => formatDate(new Date(state.value.created_at), 'YYYY-MM-DD'))
  const updated = computed(() => formatTimeAgoIntl(new Date(state.value.updated_at)))

  return {
    state,
    created,
    updated,
  }
}
