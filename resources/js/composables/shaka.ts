import PlaylistSessionController from '@/actions/App/Api/Playlists/Controllers/PlaylistSessionController'
import type { Playlist } from '@/types'
import { http } from '@/utils/http'
import { usePage } from '@inertiajs/vue3'
import { useThrottleFn } from '@vueuse/core'
import shaka from 'shaka-player'
import { computed, onBeforeMount, onBeforeUnmount, ref, shallowRef, toValue, watch, type MaybeRefOrGetter } from 'vue'

export function useShaka(element?: MaybeRefOrGetter<HTMLMediaElement | undefined>) {
  const playlist = computed(() => usePage().props.playlist as Playlist | null)
  const startTime = computed(() => usePage().props.progress as number | null)
  const el = computed(() => toValue(element))

  const player = shallowRef<shaka.Player>()
  const error = ref<shaka.util.Error | null>(null)
  const ready = ref<boolean>(false)
  const ticker = ref<number>(startTime.value ?? 0)

  const initialize = async () => {
    // Load manifest
    const manifestUri = playlist.value?.asset

    // Ensure we have a video element and manifest URI
    if (!el.value || !manifestUri) return

    // Build configuration
    const config: Partial<shaka.extern.PlayerConfiguration> = {}

    // Get encryption keys (if any)
    const keyId = playlist.value?.encryption_key_id?.toLowerCase() ?? ''
    const keyContent = playlist.value?.encryption_key?.toLowerCase() ?? ''

    // Configure DRM with clear keys
    if (keyId && keyContent) {
      config.drm = {
        clearKeys: {
          [keyId]: keyContent,
        },
      } as shaka.extern.DrmConfiguration
    }

    // Create new Shaka Player
    player.value = new shaka.Player()

    // Apply configuration
    player.value.configure(config)

    // Attach player to video element
    await player.value.attach(el.value)

    // Add timeupdate listener
    el.value.addEventListener('timeupdate', onTimeUpdate)

    // Add error listener
    player.value.addEventListener('error', onErrorEvent)

    // Check playlist state
    const isExpired = playlist.value?.expired
    const isFailed = playlist.value?.failed
    const isValid = playlist.value?.valid

    // Set error state if expired or failed
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

    // Load manifest if valid
    if (isValid) {
      await player.value.load(manifestUri, startTime.value ?? 0)

      ready.value = true
    }
  }

  const onErrorEvent = (event: Event) => {
    const shakaError = (event as CustomEvent).detail as shaka.util.Error

    // Only set error state for non-recoverable errors
    if (shakaError.severity !== shaka.util.Error.Severity.RECOVERABLE) {
      error.value = shakaError
    }

    console.error('Shaka Player Error:', shakaError)
  }

  const onTimeUpdate = useThrottleFn(() => {
    const currentTime = el.value?.currentTime ?? 0

    // Round to 2 decimal places and ensure valid number
    const time = Number.isFinite(currentTime) ? Math.round(currentTime * 100) / 100 : 0

    // Only store if playlist exists and time has changed (> 0.25 seconds)
    if (playlist.value && Math.abs((ticker.value ?? 0) - time) > 0.25) {
      http.post(PlaylistSessionController.url(playlist.value.id), { time }).then(() => {
        ticker.value = time
      })
    }
  }, 500)

  const destroy = async () => {
    try {
      // Remove timeupdate listener
      el.value?.removeEventListener('timeupdate', onTimeUpdate)

      // Destroy Shaka player instance
      await player.value?.destroy()
    } catch (error) {
      console.error('Error destroying Shaka player:', error)
    }

    // Reset state
    player.value = undefined
    ready.value = false
    error.value = null
  }

  onBeforeMount(() => shaka.polyfill.installAll())
  onBeforeUnmount(() => destroy())
  watch([el, playlist], () => initialize(), { deep: true })

  return {
    player,
    playlist,
    ready,
    error,
    initialize,
    destroy,
  }
}
