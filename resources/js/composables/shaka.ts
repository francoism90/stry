import { isPlaylistReplacement } from '@/composables/playlist'
import { useSettings } from '@/composables/settings'
import { useVideo } from '@/composables/video'
import { configureOverlay, createError, isCriticalError, loadShaka } from '@/plugins/shaka'
import type { Playlist, Video } from '@/types'
import { tryOnScopeDispose, useThrottleFn } from '@vueuse/core'
import type shaka from 'shaka-player/dist/shaka-player.ui'
import { computed, ref, shallowRef, toValue, watchEffect, type MaybeRefOrGetter } from 'vue'

export function useShaka(
  container?: MaybeRefOrGetter<HTMLElement | undefined>,
  element?: MaybeRefOrGetter<HTMLMediaElement | undefined>,
  video?: MaybeRefOrGetter<Video | null>,
  playlist?: MaybeRefOrGetter<Playlist | null>,
  progress?: MaybeRefOrGetter<number | null>,
) {
  const { get, update } = useSettings('player')
  const { markViewed } = useVideo(video)

  const player = shallowRef<shaka.Player>()
  const manager = shallowRef<shaka.util.EventManager>()
  const ui = shallowRef<shaka.ui.Overlay>()

  const initializing = ref<boolean>(false)
  const loaded = ref<boolean>(false)
  const current = shallowRef<Playlist | null>(null)
  const error = ref<shaka.util.Error | Error | null>(null)
  const ticker = ref<number | null>(null)

  const ready = computed(() => player.value !== undefined && !initializing.value && loaded.value)
  const media = computed(() => player.value?.getMediaElement() ?? null)

  const initialize = async (videoContainer: HTMLElement | undefined, mediaElement: HTMLMediaElement | undefined) => {
    // Ensure the video container and media element are available before proceeding
    if (!videoContainer || !mediaElement) {
      return
    }

    // Mark the player as initializing to prevent multiple initializations
    initializing.value = true

    try {
      // Load shaka player
      const shakaInstance = await loadShaka()

      if (!shakaInstance.Player.isBrowserSupported()) {
        onErrorEvent(new CustomEvent('error', { detail: new Error('Shaka Player is not supported in this browser.') }))
        return
      }

      // Create a new player instance
      player.value = new shakaInstance.Player()

      // Create a new event manager instance
      manager.value = new shakaInstance.util.EventManager()

      // Create a new UI instance
      ui.value = new shakaInstance.ui.Overlay(player.value, videoContainer, mediaElement)

      // Configure the UI
      configureOverlay(ui.value)

      // Attach the player to the media element
      await player.value.attach(mediaElement)

      // Get the quality setting and configure the player accordingly
      const quality = get('quality', 'auto')!
      const maxHeight = quality !== 'auto' ? Number.parseInt(quality, 10) : NaN

      // Configure the player
      player.value.configure({
        preferredAudio: [{ language: get('audio_language', 'en')! }],
        preferredText: [{ language: get('caption_language', 'en')! }],
        abr: {
          restrictions: Number.isFinite(maxHeight) ? { maxHeight } : {},
        },
        streaming: {
          bufferingGoal: 30,
          bufferBehind: 30,
          rebufferingGoal: 0,
          segmentPrefetchLimit: 3,
          ignoreTextStreamFailures: true,
          retryParameters: {
            baseDelay: 100,
          },
        },
        manifest: {
          dash: { xlinkFailGracefully: true },
          hls: { ignoreImageStreamFailures: true, ignoreTextStreamFailures: true },
          retryParameters: {
            baseDelay: 100,
          },
        },
      })

      // Configure the media element
      mediaElement.muted = get('muted', false)!
      mediaElement.volume = get('volume', 1)!
      mediaElement.playbackRate = get('playback_speed', 1)!

      // Listen for events
      manager.value.listen(mediaElement, 'volumechange', onVolumeChange)
      manager.value.listen(mediaElement, 'timeupdate', onTimeUpdate)
      manager.value.listen(player.value, 'error', onErrorEvent)
    } catch (err) {
      onErrorEvent(new CustomEvent('error', { detail: err }))
    } finally {
      // Mark the player as no longer initializing
      initializing.value = false
    }
  }

  const load = async (playlist: Playlist | null, startTime?: number | null) => {
    // Ensure the player and playlist are available before proceeding
    if (!player.value || !playlist?.valid || !playlist.asset) {
      return
    }

    // Mark the manifest as loaded and reset any previous errors
    loaded.value = true
    current.value = playlist
    ticker.value = startTime ?? null
    error.value = null

    if (playlist.failed) {
      error.value = createError('MEDIA_SOURCE_OPERATION_FAILED', 'MANIFEST')
      return
    }

    if (playlist.expired) {
      error.value = createError('EXPIRED', 'MANIFEST')
      return
    }

    // Prepare the configuration for the player, including DRM settings if available
    const config = player.value.getConfiguration()
    const keyId = playlist.encryption_key_id?.toLowerCase() ?? null
    const keyContent = playlist.encryption_key?.toLowerCase() ?? null

    try {
      // If both the key ID and key content are available, configure the player with DRM settings
      if (keyId && keyContent) {
        player.value.configure({
          ...config,
          drm: {
            clearKeys: { [keyId]: keyContent },
          } as shaka.extern.DrmConfiguration,
        })
      }

      // Load the manifest into the player
      await player.value.load(playlist.asset, startTime)

      // Select the first text track if captions are enabled
      const textTracks = player.value.getTextTracks()

      if (get('captions', true) && textTracks.length > 0) {
        player.value.selectTextTrack(textTracks[0])
      }
    } catch (err) {
      onErrorEvent(new CustomEvent('error', { detail: err }))
    }
  }

  const replace = async (playlist: Playlist | null) => {
    // Ensure the player and playlist are available before proceeding
    if (!player.value || !playlist?.valid || !playlist.asset) {
      return
    }

    // Resume from the current playback position instead of restarting the manifest
    const startTime = player.value.getMediaElement()?.currentTime ?? ticker.value

    // Track the replaced playlist and reset any previous errors
    current.value = playlist
    error.value = null
    ticker.value = startTime ?? null

    try {
      await player.value.load(playlist.asset, startTime)
    } catch (err) {
      onErrorEvent(new CustomEvent('error', { detail: err }))
    }
  }

  const destroy = async () => {
    try {
      // Release the event manager and unload the player
      await manager.value?.release()
      await player.value?.unload()

      // Destroy the UI and player instances
      await ui.value?.destroy()
      await player.value?.destroy()
    } catch {
      // Ignore errors during destruction
    } finally {
      // Reset the player, manager, and UI references
      ui.value = undefined
      player.value = undefined
      manager.value = undefined

      // Reset the state
      reset()
    }
  }

  const reset = () => {
    initializing.value = false
    loaded.value = false
    current.value = null
    error.value = null
    ticker.value = null
  }

  const onErrorEvent = (event: Event) => {
    const detail = (event as CustomEvent).detail as shaka.util.Error | Error

    if (isCriticalError(detail)) {
      error.value = detail
    }

    console.error('Shaka Player Error:', detail)
  }

  const onTimeUpdate = useThrottleFn((event: Event) => {
    const model = toValue(video) as Video | null
    const el = event.target as HTMLMediaElement | null

    if (model && el) {
      const currentTime = el.currentTime ?? 0
      const time = Number.isFinite(currentTime) ? Math.round(currentTime * 100) / 100 : 0

      // Prevent excessive updates by only marking as viewed if the time has changed significantly
      if (time > 0 && Math.abs((ticker.value ?? 0) - time) > 2) {
        // Update the ticker value to the current time
        ticker.value = time ?? 0

        try {
          markViewed(time)
        } catch (err) {
          console.error('Error marking video as viewed:', err)
        }
      }
    }
  }, 2500)

  const onVolumeChange = (event: Event) => {
    const el = event.target as HTMLMediaElement | null

    if (el) {
      update({ muted: el.muted ?? null, volume: el.volume ?? null })
    }
  }

  watchEffect(async () => {
    const playlistModel = toValue(playlist) as Playlist | null
    const videoContainer = toValue(container) as HTMLElement | undefined
    const mediaElement = toValue(element) as HTMLMediaElement | undefined
    const startsAt = toValue(progress) as number | null

    // Re-initialize the player if the media element has changed
    if (initializing.value === false && mediaElement !== media.value) {
      await destroy()
      await initialize(videoContainer, mediaElement)
    }

    if (playlistModel) {
      if (loaded.value === false) {
        // Load the initial manifest
        await load(playlistModel, startsAt)
      } else if (isPlaylistReplacement(playlistModel, current.value)) {
        // Swap in a newly issued manifest (e.g. after the previous one expired) without a full re-initialize
        await replace(playlistModel)
      }
    }
  })

  tryOnScopeDispose(() => destroy())

  return {
    player,
    ready,
    error,
    initialize,
    replace,
    destroy,
  }
}
