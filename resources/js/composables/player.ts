import PlaylistSessionController from '@/actions/App/Api/Playlists/Controllers/PlaylistSessionController'
import type { Media, Playlist } from '@/types'
import { http } from '@/utils/http'
import { usePage } from '@inertiajs/vue3'
import { useThrottleFn } from '@vueuse/core'
import { computed, readonly } from 'vue'

export function usePlayer() {
  const state = computed(() => usePage().props.playlist as Playlist | null)
  const captions = computed(() => usePage().props.captions as Media[] | null)
  const progress = computed(() => usePage().props.progress as number)

  const ready = computed(() => state.value?.valid && state.value?.asset)
  const src = computed(() => (ready.value ? state.value?.asset : null))

  const store = useThrottleFn(async (value: number) => {
    // Round to 2 decimal places
    const time = Math.round(value * 100) / 100

    // Only store if changed
    if (state.value && progress.value !== time) {
      await http.post(PlaylistSessionController.url(state.value.id), { time })
    }
  }, 2500)

  return {
    state: readonly(state),
    captions,
    ready,
    src,
    progress,
    store,
  }
}
