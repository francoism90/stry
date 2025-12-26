import PlaylistSessionController from '@/actions/App/Api/Playlists/Controllers/PlaylistSessionController'
import type { Playlist, Video } from '@/types'
import { http } from '@/utils/http'
import { usePage } from '@inertiajs/vue3'
import { useThrottleFn } from '@vueuse/core'
import { computed } from 'vue'

export function usePlayer() {
  const state = computed(() => usePage().props.playlist as Playlist | null)
  const video = computed(() => usePage().props.video as Video | null)
  const progress = computed(() => usePage().props.progress as number | null)

  const store = useThrottleFn(async (value: number) => {
    // Round to 2 decimal places and ensure valid number
    const time = Number.isFinite(value) ? Math.round(value * 100) / 100 : 0

    // Only store if playlist exists and time has changed significantly (> 0.5 seconds)
    if (state.value && Math.abs((progress.value ?? 0) - time) > 0.5) {
      await http.post(PlaylistSessionController.url(state.value.id), { time })
    }
  }, 2000)

  return {
    state,
    video,
    progress,
    store,
  }
}
