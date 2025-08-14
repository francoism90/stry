import PlaylistSessionController from '@/actions/App/Api/Playlists/Controllers/PlaylistSessionController'
import type { Media, Playlist } from '@/types'
import { http } from '@/utils/http'
import { usePage } from '@inertiajs/vue3'
import { computed, readonly, ref, shallowRef, toValue, watchEffect } from 'vue'

export function usePlayer() {
  const state = shallowRef<Playlist | null>()
  const captions = shallowRef<Media[] | null>(null)
  const time = ref<number | null>(null)

  const ready = computed(() => state.value?.valid && state.value?.asset)
  const src = computed(() => (ready.value ? state.value?.asset : null))

  const watchtime = async (time?: number | null) => http.post(PlaylistSessionController.url({ playlist: state.value?.id || '' }), { time })

  watchEffect(async () => {
    state.value = toValue(usePage().props.playlist as Playlist)
    captions.value = toValue(usePage().props.captions as Media[] | null)
    time.value = toValue(usePage().props.time as number | null)
  })

  return {
    state: readonly(state),
    captions,
    ready,
    src,
    watchtime,
  }
}
