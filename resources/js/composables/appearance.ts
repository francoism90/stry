import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useAppearance() {
  const nonce = computed(() => usePage().props.nonce)

  const dynamicColor = (value?: string | null) => {
    if (!value) return `hsl(0, 0%, 50%)`

    let hash = 0

    for (let i = 0; i < value.length; i++) {
      hash = value.charCodeAt(i) + ((hash << 5) - hash)
    }

    const hue = Math.abs(hash) % 360

    return `hsl(${hue}, 60%, 30%)`
  }

  return {
    nonce,
    dynamicColor,
  }
}
