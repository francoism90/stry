import PlaylistSessionController from '@/actions/App/Api/Playlists/Controllers/PlaylistSessionController'
import type { Playlist } from '@/types'
import { router, usePage } from '@inertiajs/vue3'
import { useThrottleFn } from '@vueuse/core'
import shaka from 'shaka-player'
import { computed, onBeforeMount, onBeforeUnmount, ref, shallowRef, watch } from 'vue'

export function useShaka() {
  const player = shallowRef<shaka.Player>()
  const error = ref<shaka.util.Error | null>(null)
  const el = ref<HTMLMediaElement | null>(null)
  const ready = ref(false)

  const playlist = computed(() => usePage().props.playlist as Playlist | null)
  const startTime = computed(() => usePage().props.progress as number | null)

  const initialize = async (element: HTMLMediaElement | null) => {
    // Set media element
    el.value = element || null

    // Create player instance
    if (!player.value) {
      player.value = new shaka.Player()

      const keyId = playlist.value?.encryption_key_id?.toLowerCase() ?? ''
      const key = playlist.value?.encryption_key?.toLowerCase() ?? ''

      // Configure DRM if keys are present
      player.value.configure({
        drm: {
          clearKeys: {
            [keyId]: key,
          },
        },
      })
    }

    // Attach video element
    if (el.value) {
      await player.value.attach(el.value)

      // Add timeupdate listener
      el.value.addEventListener('timeupdate', onTimeUpdate)
    }

    // Add error listener
    player.value.addEventListener('error', onErrorEvent)

    // Check playlist state
    const isExpired = playlist.value?.expired
    const isFailed = playlist.value?.failed
    const isValid = playlist.value?.valid

    if (isFailed) {
      error.value = new shaka.util.Error(
        shaka.util.Error.Severity.CRITICAL,
        shaka.util.Error.Category.MANIFEST,
        shaka.util.Error.Code.MEDIA_SOURCE_OPERATION_FAILED,
      )
    }

    if (isExpired) {
      error.value = new shaka.util.Error(
        shaka.util.Error.Severity.CRITICAL,
        shaka.util.Error.Category.MANIFEST,
        shaka.util.Error.Code.EXPIRED,
      )
    }

    // Load manifest
    const manifestUri = playlist.value?.asset

    if (manifestUri && isValid) {
      await player.value.load(manifestUri, startTime.value ?? 0)
    }

    // Set ready state
    ready.value = isValid ?? false
  }

  const onErrorEvent = (event: Event) => {
    error.value = (event as CustomEvent).detail as shaka.util.Error
  }

  const onTimeUpdate = useThrottleFn(() => {
    const currentTime = el.value?.currentTime ?? 0

    // Round to 2 decimal places and ensure valid number
    const time = Number.isFinite(currentTime) ? Math.round(currentTime * 100) / 100 : 0

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
  }, 900)

  const destroy = async () => {
    if (player.value) {
      await player.value.destroy()
    }

    // Reset state
    player.value = undefined
    ready.value = false
    error.value = null

    // Remove timeupdate listener
    el.value?.removeEventListener('timeupdate', onTimeUpdate)
  }

  onBeforeMount(() => shaka.polyfill.installAll())
  onBeforeUnmount(() => destroy())
  watch(playlist, () => initialize(el.value), { deep: true })

  return {
    player,
    playlist,
    ready,
    error,
    initialize,
    destroy,
  }
}
