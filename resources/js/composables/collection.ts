import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useCollection() {
  const filters = computed(() => usePage().props.filters)
  const filter = computed(() => usePage().props.filter)
  const search = computed(() => usePage().props.search)
  const grid = computed(() => usePage().props.grid === 'true')
  const orientation = computed(() => (grid.value ? 'horizontal' : 'vertical'))

  return {
    filter,
    filters,
    search,
    grid,
    orientation,
  }
}
