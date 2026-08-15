import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useSearch() {
  const search = computed(() => usePage().props.search)

  return {
    search,
  }
}
