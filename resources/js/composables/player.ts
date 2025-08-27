import PlaylistProgressController from '@/actions/App/Api/Playlists/Controllers/PlaylistProgressController'
import type { Media, Playlist } from '@/types'
import { http } from '@/utils/http'
import { usePage } from '@inertiajs/vue3'
import { computed, readonly } from 'vue'

export function usePlayer() {
  const state = computed(() => usePage().props.playlist as Playlist | null)
  const captions = computed(() => usePage().props.captions as Media[] | null)
  const progress = computed(() => usePage().props.progress as number)

  const ready = computed(() => state.value?.valid && state.value?.asset)
  const src = computed(() => (ready.value ? state.value?.asset : null))

  const record = async (time: number | null) => {
    if (state.value?.id) {
      await http.post(PlaylistProgressController.url(state.value.id), { time })
    }
  }

  return {
    state: readonly(state),
    captions,
    ready,
    src,
    progress,
    record,
  }
}
