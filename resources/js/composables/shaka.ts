import PlaylistSessionController from '@/actions/App/Api/Playlists/Controllers/PlaylistSessionController'
import type { Playlist } from '@/types'
import { router, usePage } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import shaka from 'shaka-player'
import { computed, onBeforeUnmount, onMounted, ref, shallowRef, toValue, watchEffect, type MaybeRefOrGetter } from 'vue'

export function useShaka(videoElement: MaybeRefOrGetter<HTMLMediaElement | undefined>) {
  const player = shallowRef<shaka.Player>()
  const error = ref<shaka.util.Error | null>(null)
  const ready = ref(false)
  const el = toValue(videoElement)

  const playlist = computed(() => usePage().props.playlist as Playlist | null)
  const startTime = computed(() => usePage().props.progress as number | null)

  const initialize = async () => {
    // Create player instance
    if (!player.value) {
      player.value = new shaka.Player()
    }

    // Attach video element
    if (el) {
      await player.value.attach(el)

      // Add timeupdate listener
      el.addEventListener('timeupdate', onTimeUpdate)
    }

    // Add error listener
    player.value.addEventListener('error', onErrorEvent)

    // Load manifest
    const manifestUri = playlist.value?.asset

    if (manifestUri) {
      await player.value.load(manifestUri, startTime.value ?? 0)
    }

    // Mark as ready
    ready.value = true
  }

  const onErrorEvent = (event: Event) => {
    error.value = (event as CustomEvent).detail as shaka.util.Error
  }

  const onTimeUpdate = useDebounceFn(() => {
    const currentTime = el?.currentTime ?? 0

    // Round to 2 decimal places and ensure valid number
    const time = Number.isFinite(currentTime) ? Math.round(currentTime * 100) / 100 : 0

    console.log('Time update:', time)

    // Only store if playlist exists and time has changed (> 0.25 seconds)
    if (playlist.value && Math.abs((startTime.value ?? 0) - time) > 0.25) {
      router.post(
        PlaylistSessionController.url(playlist.value.id),
        { time },
        {
          preserveState: true,
          only: ['progress'],
        },
      )
    }
  }, 1500)

  const destroy = async () => {
    if (player.value) {
      await player.value.destroy()
    }

    // Reset state
    player.value = undefined
    ready.value = false
    error.value = null

    // Remove timeupdate listener
    el?.removeEventListener('timeupdate', onTimeUpdate)
  }

  watchEffect(() => initialize())
  onMounted(() => shaka.polyfill.installAll())
  onBeforeUnmount(() => destroy())

  return {
    player,
    ready,
    error,
    initialize,
    destroy,
  }
}
