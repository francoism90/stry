import { usePage } from '@inertiajs/vue3'
import type { SelectItem } from '@nuxt/ui'
import { computed } from 'vue'

export function useCollection() {
  const order = computed(() => usePage().props.order as string | undefined)
  const orders = computed(() => usePage().props.orders as SelectItem[] | undefined)
  const search = computed(() => usePage().props.search as string | undefined)

  return {
    order,
    orders,
    search,
  }
}
