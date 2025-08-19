import PlaylistSessionController from '@/actions/App/Api/Playlists/Controllers/PlaylistSessionController'
import type { Media, Playlist } from '@/types'
import { http } from '@/utils/http'
import { usePage } from '@inertiajs/vue3'
import { computed, readonly } from 'vue'

export function usePlayer() {
  const state = computed(() => usePage().props.playlist as Playlist | null)
  const captions = computed(() => usePage().props.captions as Media[] | null)
  const starts = computed(() => usePage().props.starts as number | null)

  const ready = computed(() => state.value?.valid && state.value?.asset)
  const src = computed(() => (ready.value ? state.value?.asset : null))

  const watchtime = async (time?: number | null) => {
    if (!state.value || !time) {
      return
    }

    await http.post(PlaylistSessionController.url({ playlist: state.value.id }), { time })
  }

  return {
    state: readonly(state),
    captions,
    ready,
    src,
    starts,
    watchtime,
  }
}
