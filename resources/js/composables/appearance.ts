import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useAppearance() {
  const nonce = computed(() => usePage().props.nonce)

  return {
    nonce,
  }
}
