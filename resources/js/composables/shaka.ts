import PlaylistSessionController from '@/actions/App/Api/Playlists/Controllers/PlaylistSessionController'
import { configureOverlay } from '@/plugins/shaka'
import { playerSetting } from '@/plugins/shaka/settings'
import type { PlayerSettings, Playlist } from '@/types'
import { http } from '@/utils/http'
import { usePage } from '@inertiajs/vue3'
import { useEventListener, useThrottleFn, watchDeep, whenever } from '@vueuse/core'
import shaka from 'shaka-player/dist/shaka-player.ui'
import { computed, onBeforeMount, onBeforeUnmount, ref, shallowRef, toValue, type MaybeRefOrGetter } from 'vue'

export function useShaka(
  container?: MaybeRefOrGetter<HTMLElement | undefined>,
  element?: MaybeRefOrGetter<HTMLMediaElement | undefined>,
) {
  const playlist = computed(() => usePage().props.playlist as Playlist | null)
  const settings = computed(() => usePage().props.player as PlayerSettings | null)
  const startTime = computed(() => usePage().props.progress as number | null)
  const el = computed(() => toValue(element))

  const player = shallowRef<shaka.Player>()
  const ui = shallowRef<shaka.ui.Overlay>()
  const error = ref<shaka.util.Error | null>(null)
  const ready = ref<boolean>(false)
  const ticker = ref<number>(startTime.value ?? 0)

  const initialize = async () => {
    // If a player instance already exists, destroy it before re-initializing
    if (player.value) {
      await destroy()
    }

    // Ensure video element is available before creating player
    if (!el.value) return

    // Create new Shaka Player
    player.value = new shaka.Player()

    // Create UI overlay for player controls
    ui.value = new shaka.ui.Overlay(
      player.value,
      toValue(container) as HTMLElement,
      toValue(element) as HTMLMediaElement,
    )

    // Configure UI: disable double-tap fullscreen, enable tap-to-seek, add seek buttons
    configureOverlay(ui.value)

    // Attach player to video element
    await player.value.attach(el.value)

    // Configure player preferences
    const quality = playerSetting(settings.value, 'quality')

    player.value.configure({
      preferredAudioLanguage: playerSetting(settings.value, 'audio_languages'),
      preferredTextLanguage: playerSetting(settings.value, 'caption_languages'),
      abr: {
        restrictions: quality !== 'auto' ? { maxHeight: parseInt(quality), minHeight: parseInt(quality) } : {},
      },
    })

    // Restore saved mute state and playback speed
    el.value.muted = playerSetting(settings.value, 'muted')
    el.value.playbackRate = playerSetting(settings.value, 'playback_speed')

    // Load the manifest and start playback
    await load()
  }

  const load = async () => {
    // Reset error state before evaluating new playlist
    error.value = null

    // On failed set critical error state
    if (playlist.value?.failed) {
      error.value = new shaka.util.Error(
        shaka.util.Error.Severity.CRITICAL,
        shaka.util.Error.Category.MANIFEST,
        shaka.util.Error.Code.MEDIA_SOURCE_OPERATION_FAILED,
      )

      return
    }

    // On expired set critical error state
    if (playlist.value?.expired) {
      error.value = new shaka.util.Error(
        shaka.util.Error.Severity.CRITICAL,
        shaka.util.Error.Category.MANIFEST,
        shaka.util.Error.Code.EXPIRED,
      )

      return
    }

    // Get manifest URI from playlist asset
    const manifestUri = playlist.value?.asset ?? null

    // Ensure manifest URI and video element are available before attempting to load
    if (!manifestUri || !el.value) return

    // If player instance doesn't exist, initialize it first (will call load again on completion)
    if (!player.value) {
      await initialize()
      return
    }

    // Only attempt to load if playlist is valid (not expired or failed)
    if (playlist.value?.valid) {
      // Build DRM configuration with clear keys (if available)
      const config: Partial<shaka.extern.PlayerConfiguration> = {}

      // Configure DRM with clear keys (if available)
      const keyId = playlist.value?.encryption_key_id?.toLowerCase() ?? ''
      const keyContent = playlist.value?.encryption_key?.toLowerCase() ?? ''

      if (keyId && keyContent) {
        config.drm = {
          clearKeys: {
            [keyId]: keyContent,
          },
        } as shaka.extern.DrmConfiguration
      }

      // Attempt to load manifest and start playback
      try {
        // Apply DRM configuration to player (if any)
        if (config.drm) {
          player.value.configure(config)
        }

        // Load the manifest with optional resume time
        await player.value.load(manifestUri, startTime.value)

        // Enable the first available subtitle track if captions are enabled
        const textTracks = player.value.getTextTracks()

        if (playerSetting(settings.value, 'captions') && textTracks.length > 0) {
          player.value.selectTextTrack(textTracks[0])
        }

        // Set ready state
        ready.value = true
      } catch {
        // Handled by error event listener
        // Reset ready state on load failure
        ready.value = false
      }
    }
  }

  const onErrorEvent = (event: Event) => {
    const shakaError = (event as CustomEvent).detail as shaka.util.Error

    // Only set error state for non-recoverable errors
    if (shakaError.severity === shaka.util.Error.Severity.CRITICAL) {
      error.value = shakaError
    }

    console.error('Shaka Player Error:', shakaError)
  }

  const onTimeUpdate = useThrottleFn(() => {
    const currentTime = el.value?.currentTime ?? 0

    // Round to 2 decimal places and ensure valid number
    const time = Number.isFinite(currentTime) ? Math.round(currentTime * 100) / 100 : 0

    // Only store if playlist valid, time > 0, and time has changed (> 0.25 seconds)
    if (playlist.value?.valid && time > 0 && Math.abs((ticker.value ?? 0) - time) > 0.25) {
      http.post(PlaylistSessionController.url(playlist.value.id), { time }).then(() => {
        ticker.value = time
      })
    }
  }, 900)

  const destroy = async () => {
    try {
      // Pause video to stop playback and clean MediaSource state
      el.value?.pause()

      // Destroy UI overlay before the player
      await ui.value?.destroy()

      // Destroy Shaka player instance
      await player.value?.destroy()
    } catch (err) {
      console.error('Error destroying Shaka player:', err)
    }

    // Reset state
    ui.value = undefined
    player.value = undefined
    ready.value = false
    error.value = null
  }

  onBeforeMount(() => shaka.polyfill.installAll())
  onBeforeUnmount(() => destroy())

  useEventListener(player, 'error', onErrorEvent)
  useEventListener(el, 'timeupdate', onTimeUpdate)

  whenever(el, () => initialize(), { immediate: true })
  watchDeep(playlist, () => load())

  return {
    player,
    playlist,
    ready,
    error,
    initialize,
    destroy,
  }
}
