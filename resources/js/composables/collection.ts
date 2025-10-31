import { usePage } from '@inertiajs/vue3'
import type { SelectItem } from '@nuxt/ui'
import { computed } from 'vue'

export function useCollection() {
  const filters = computed(() => usePage().props.filters as SelectItem[] | undefined)
  const filter = computed(() => usePage().props.filter as string | undefined)
  const search = computed(() => usePage().props.search as string | undefined)

  return {
    filter,
    filters,
    search,
  }
}
