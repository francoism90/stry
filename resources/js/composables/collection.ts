import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useCollection() {
  const filters = computed(() => usePage().props.filters)
  const filter = computed(() => usePage().props.filter)
  const search = computed(() => usePage().props.search)
  const view = computed(() => usePage().props.view)

  return {
    filter,
    filters,
    search,
    view,
  }
}
