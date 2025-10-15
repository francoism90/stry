import { usePage } from '@inertiajs/vue3'
import { computed, watch } from 'vue'

export function useFlash() {
  const toast = useToast()

  const title = computed(() => usePage().props.flash?.message)
  const level = computed(() => usePage().props.flash?.level)
  const color = computed(() => usePage().props.flash?.class)

  const show = () => {
    if (!title.value) {
      return
    }

    toast.add({
      title: title.value,
      color: color.value,
    })
  }

  watch(title, () => show())

  return {
    title,
    level,
    color,
    show,
  }
}
