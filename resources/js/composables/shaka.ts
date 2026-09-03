import { usePlaylist } from '@/composables/playlist'
import { useSettings } from '@/composables/settings'
import { useVideo } from '@/composables/video'
import { configureOverlay, createError, isCriticalError, loadShaka, supportsNativeHls } from '@/plugins/shaka'
import type { Playlist, Video } from '@/types'
import { tryOnScopeDispose, useThrottleFn } from '@vueuse/core'
import type shaka from 'shaka-player/dist/shaka-player.ui'
import { computed, ref, shallowRef, toValue, watch, type MaybeRefOrGetter } from 'vue'

export function useShaka(
  container?: MaybeRefOrGetter<HTMLElement | undefined>,
  element?: MaybeRefOrGetter<HTMLMediaElement | undefined>,
  video?: MaybeRefOrGetter<Video | null>,
  playlist?: MaybeRefOrGetter<Playlist | null>,
  progress?: MaybeRefOrGetter<number | null>,
) {
  const { get, update } = useSettings('player')
  const { isPlaylistReplacement, scheduleAssetRefresh, cancelAssetRefresh } = usePlaylist()
  const { markViewed } = useVideo()

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
          retryParameters: {
            baseDelay: 100,
          },
          // Safari's MSE-based HLS/DASH playback is unreliable for AirPlay and PiP, so let
          // Shaka fall back to the browser's native HLS engine there when we hand it an HLS
          // manifest. No effect on browsers without native HLS support (they keep using MSE).
          preferNativeHls: true,
        },
        manifest: {
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
      manager.value.listen(mediaElement, 'ratechange', onPlaybackRateChange)
      manager.value.listen(mediaElement, 'timeupdate', onTimeUpdate)
      manager.value.listen(player.value, 'error', onErrorEvent)
    } catch (err) {
      onErrorEvent(new CustomEvent('error', { detail: err }))
    } finally {
      // Mark the player as no longer initializing
      initializing.value = false
    }
  }

  /**
   * Registers the storyboard VTT as a native Shaka thumbnails track so the seek bar can
   * show hover/scrub previews on its own. The VTT references its sprite by a bare filename,
   * but the sprite and VTT are stored as separate signed URLs (different paths, different
   * signatures), so relative URL resolution against the VTT's own URL would produce an
   * incorrectly-signed URL. Rewriting the reference to the current signed image URL and
   * loading the result from a blob avoids that mismatch.
   */
  const addStoryboardTrack = async (videoModel: Video | null): Promise<void> => {
    if (!player.value || !videoModel?.storyboard_vtt || !videoModel.storyboard_image) {
      return
    }

    try {
      const response = await fetch(videoModel.storyboard_vtt)

      // Signed URLs contain raw '&' query-string separators, but WebVTT cue text uses
      // HTML-entity-style escaping, so a literal '&' must be written as '&amp;' or a parser
      // may mangle everything from there onward (including the "#xywh=" it needs to detect).
      const escapedImageUrl = videoModel.storyboard_image.replaceAll('&', '&amp;')
      const contents = (await response.text()).replace(/^(\S+)#xywh=/gm, `${escapedImageUrl}#xywh=`)
      const blobUrl = URL.createObjectURL(new Blob([contents], { type: 'text/vtt' }))

      try {
        await player.value.addThumbnailsTrack(blobUrl, 'text/vtt')
      } finally {
        URL.revokeObjectURL(blobUrl)
      }
    } catch (err) {
      console.error('Error adding storyboard thumbnails track:', err)
    }
  }

  /**
   * Picks which manifest to hand to Shaka. On platforms with native HLS support (Safari on
   * macOS/iOS), prefer the HLS master playlist — combined with `preferNativeHls` above, this
   * lets Shaka use the browser's native HLS engine there instead of MSE. Everywhere else, DASH.
   */
  const resolveAssetUri = (playlistModel: Playlist): string | null => {
    const mediaElement = media.value

    if (mediaElement && playlistModel.asset_hls && supportsNativeHls(mediaElement)) {
      return playlistModel.asset_hls
    }

    return playlistModel.asset
  }

  const load = async (playlist: Playlist | null, startTime?: number | null) => {
    // Ensure the player and playlist are available before proceeding
    if (!player.value || !playlist || !playlist.valid) {
      return
    }

    const assetUri = resolveAssetUri(playlist)

    if (!assetUri) {
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
      await player.value.load(assetUri, startTime)

      // Select the first text track if captions are enabled
      const textTracks = player.value.getTextTracks()

      if (get('captions', true) && textTracks.length > 0) {
        player.value.selectTextTrack(textTracks[0])
      }

      // Register the storyboard sprite as a thumbnails track so Shaka's seek bar
      // can show hover/scrub previews natively
      await addStoryboardTrack(toValue(video) as Video | null)

      scheduleAssetRefresh(playlist)
    } catch (err) {
      onErrorEvent(new CustomEvent('error', { detail: err }))
    }
  }

  const replace = async (playlist: Playlist | null) => {
    // Ensure the player and playlist are available before proceeding
    if (!player.value || !playlist || !playlist.valid) {
      return
    }

    const assetUri = resolveAssetUri(playlist)

    if (!assetUri) {
      return
    }

    // Resume from the current playback position instead of restarting the manifest
    const startTime = player.value.getMediaElement()?.currentTime ?? ticker.value

    // Track the replaced playlist and reset any previous errors
    current.value = playlist
    error.value = null
    ticker.value = startTime ?? null

    try {
      await player.value.load(assetUri, startTime)

      scheduleAssetRefresh(playlist)
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
    cancelAssetRefresh()

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

  const onTimeUpdate = useThrottleFn(async (event: Event) => {
    const model = toValue(video) as Video | null
    const el = event.target as HTMLMediaElement | null

    if (model && el) {
      const currentTime = el.currentTime
      const time = Number.isFinite(currentTime) ? Math.round(currentTime * 100) / 100 : 0

      // Prevent excessive updates by only marking as viewed if the time has changed significantly
      if (time > 0 && Math.abs((ticker.value ?? 0) - time) > 1.5) {
        // Update the ticker value to the current time
        ticker.value = time

        try {
          await markViewed(model, time)
        } catch (err) {
          console.error('Error marking video as viewed:', err)
        }
      }
    }
  }, 2500)

  const onVolumeChange = (event: Event) => {
    const el = event.target as HTMLMediaElement | null

    if (el) {
      const muted = el.muted ?? null
      const volume = el.volume ?? null

      // Avoid persisting a redundant update when the values already match, e.g. right after
      // the initial mediaElement.muted/volume assignment during initialization
      if (muted === get('muted', false) && volume === get('volume', 1)) {
        return
      }

      update({ muted, volume })
    }
  }

  const onPlaybackRateChange = (event: Event) => {
    const el = event.target as HTMLMediaElement | null

    if (el) {
      const playbackRate = el.playbackRate ?? null

      // Avoid persisting a redundant update when the value already matches, e.g. right after
      // the initial mediaElement.playbackRate assignment during initialization
      if (playbackRate === get('playback_speed', 1)) {
        return
      }

      update({ playback_speed: playbackRate })
    }
  }

  // Watch only the external inputs explicitly instead of using watchEffect, which would also
  // track `player`/`loaded`/`current`/`initializing` (read below before the first `await`).
  // Those are mutated by destroy()/initialize()/load() themselves, so an auto-tracking effect
  // re-triggers itself on its own writes, racing overlapping load() calls that abort each
  // other with LOAD_INTERRUPTED errors.
  let isSyncing = false
  let queued = false

  const sync = async (): Promise<void> => {
    if (isSyncing) {
      // A change arrived mid-sync (e.g. the media element mounts and the playlist prop
      // resolves in close succession on first load) — re-check current values once done
      // instead of dropping it, or nothing would ever pick the change back up.
      queued = true
      return
    }

    isSyncing = true

    try {
      do {
        queued = false

        const videoContainer = toValue(container) as HTMLElement | undefined
        const mediaElement = toValue(element) as HTMLMediaElement | undefined
        const playlistModel = toValue(playlist) as Playlist | null
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
      } while (queued)
    } finally {
      isSyncing = false
    }
  }

  watch(() => [toValue(container), toValue(element), toValue(playlist), toValue(progress)], sync, { immediate: true })

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
