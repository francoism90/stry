import { usePlaylist } from '@/composables/playlist'
import { useSettings } from '@/composables/settings'
import { useVideo } from '@/composables/video'
import { configureOverlay, createError, isCriticalError, loadShaka, resolveAssetUri } from '@/plugins/shaka'
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
  const currentTime = ref<number>(0)
  const hasStartedPlayback = ref<boolean>(false)

  const ready = computed(() => player.value !== undefined && !initializing.value && loaded.value)
  const media = computed(() => player.value?.getMediaElement() ?? null)

  const initialize = async (videoContainer: HTMLElement | undefined, mediaElement: HTMLMediaElement | undefined) => {
    if (!videoContainer || !mediaElement) {
      return
    }

    initializing.value = true

    try {
      const shakaInstance = await loadShaka()

      if (!shakaInstance.Player.isBrowserSupported()) {
        onErrorEvent(new CustomEvent('error', { detail: new Error('Shaka Player is not supported in this browser.') }))
        return
      }

      player.value = new shakaInstance.Player()
      manager.value = new shakaInstance.util.EventManager()
      ui.value = new shakaInstance.ui.Overlay(player.value, videoContainer, mediaElement)

      configureOverlay(ui.value)

      await player.value.attach(mediaElement)

      const quality = get('quality', 'auto')!
      const maxHeight = quality !== 'auto' ? Number.parseInt(quality, 10) : NaN

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
          // Lets Shaka use Safari's native HLS engine (more reliable AirPlay/PiP) instead of MSE; no-op elsewhere.
          preferNativeHls: true,
        },
        manifest: {
          retryParameters: {
            baseDelay: 100,
          },
        },
      })

      mediaElement.muted = get('muted', false)!
      mediaElement.volume = get('volume', 1)!
      mediaElement.playbackRate = get('playback_speed', 1)!

      manager.value.listen(mediaElement, 'volumechange', onVolumeChange)
      manager.value.listen(mediaElement, 'ratechange', onPlaybackRateChange)
      manager.value.listen(mediaElement, 'timeupdate', onTimeUpdate)
      manager.value.listen(mediaElement, 'timeupdate', onTimeUpdateTick)
      manager.value.listen(mediaElement, 'playing', onPlaying)
      manager.value.listen(player.value, 'error', onErrorEvent)
    } catch (err) {
      onErrorEvent(new CustomEvent('error', { detail: err }))
    } finally {
      initializing.value = false
    }
  }

  // Registers the storyboard VTT as a thumbnails track, rewriting its sprite reference to the
  // current signed image URL first (the VTT and sprite have separate signed URLs, so resolving
  // the VTT's bare filename reference against its own URL would sign it incorrectly).
  const addStoryboardTrack = async (videoModel: Video | null): Promise<void> => {
    if (!player.value || !videoModel?.storyboard_vtt || !videoModel.storyboard_image) {
      return
    }

    try {
      const response = await fetch(videoModel.storyboard_vtt)

      // WebVTT cue text needs HTML-entity escaping, or a literal '&' mangles parsing from there on.
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

  // Adds the chapters sidecar VTT as a native text track (chapters menu, accessibility). Purely
  // additive to playback: the skip button and chapter list read `video.chapters` directly rather
  // than this track, since Shaka's chapters API only exposes {start, end, title}, not our `type`.
  const addChaptersTrack = async (videoModel: Video | null): Promise<void> => {
    if (!player.value || !videoModel?.chapters_vtt) {
      return
    }

    try {
      await player.value.addChaptersTrack(videoModel.chapters_vtt, 'en')
    } catch (err) {
      console.error('Error adding chapters track:', err)
    }
  }

  const load = async (playlist: Playlist | null, startTime?: number | null) => {
    if (!player.value || !playlist || !playlist.valid) {
      return
    }

    const assetUri = resolveAssetUri(playlist, media.value)

    if (!assetUri) {
      return
    }

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

    const config = player.value.getConfiguration()
    const keyId = playlist.encryption_key_id?.toLowerCase() ?? null
    const keyContent = playlist.encryption_key?.toLowerCase() ?? null

    try {
      if (keyId && keyContent) {
        player.value.configure({
          ...config,
          drm: {
            clearKeys: { [keyId]: keyContent },
          } as shaka.extern.DrmConfiguration,
        })
      }

      await player.value.load(assetUri, startTime)

      const textTracks = player.value.getTextTracks()

      if (get('captions', true) && textTracks.length > 0) {
        player.value.selectTextTrack(textTracks[0])
      }

      await addStoryboardTrack(toValue(video) as Video | null)
      await addChaptersTrack(toValue(video) as Video | null)

      scheduleAssetRefresh(playlist)
    } catch (err) {
      onErrorEvent(new CustomEvent('error', { detail: err }))
    }
  }

  const replace = async (playlist: Playlist | null) => {
    if (!player.value || !playlist || !playlist.valid) {
      return
    }

    const assetUri = resolveAssetUri(playlist, media.value)

    if (!assetUri) {
      return
    }

    // Resume from the current position instead of restarting the manifest.
    const startTime = player.value.getMediaElement()?.currentTime ?? ticker.value

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
      await manager.value?.release()
      await player.value?.unload()
      await ui.value?.destroy()
      await player.value?.destroy()
    } catch {
      // Ignore errors during destruction
    } finally {
      ui.value = undefined
      player.value = undefined
      manager.value = undefined

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
    currentTime.value = 0
    hasStartedPlayback.value = false
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

      // Only mark as viewed once the time has moved meaningfully, to avoid excessive updates.
      if (time > 0 && Math.abs((ticker.value ?? 0) - time) > 1.5) {
        ticker.value = time

        try {
          await markViewed(model, time)
        } catch (err) {
          console.error('Error marking video as viewed:', err)
        }
      }
    }
  }, 2500)

  // Unthrottled, unlike onTimeUpdate above: the skip button needs to react within a fraction of a
  // second of crossing a chapter boundary, not on markViewed()'s 2.5s cadence.
  const onTimeUpdateTick = (event: Event) => {
    const el = event.target as HTMLMediaElement | null

    if (el) {
      currentTime.value = el.currentTime
    }
  }

  // Fires once frames actually start rendering (including after the initial buffering delay), so
  // UI gated on this - e.g. the chapter skip button - doesn't flash on top of a still-loading
  // player. Only ever set true here; reset() clears it again on the next video.
  const onPlaying = () => {
    hasStartedPlayback.value = true
  }

  const onVolumeChange = (event: Event) => {
    const el = event.target as HTMLMediaElement | null

    if (el) {
      const muted = el.muted ?? null
      const volume = el.volume ?? null

      // Skip the redundant update right after the initial assignment in initialize().
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

      // Skip the redundant update right after the initial assignment in initialize().
      if (playbackRate === get('playback_speed', 1)) {
        return
      }

      update({ playback_speed: playbackRate })
    }
  }

  // watch() over explicit sources, not watchEffect: watchEffect would also track the
  // player/loaded/current/initializing refs mutated below, re-triggering on its own writes and
  // racing overlapping load() calls into LOAD_INTERRUPTED errors.
  let isSyncing = false
  let queued = false

  const sync = async (): Promise<void> => {
    if (isSyncing) {
      // Re-check current values once the in-flight sync finishes, rather than dropping this change.
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

        if (initializing.value === false && mediaElement !== media.value) {
          await destroy()
          await initialize(videoContainer, mediaElement)
        }

        if (playlistModel) {
          if (loaded.value === false) {
            await load(playlistModel, startsAt)
          } else if (isPlaylistReplacement(playlistModel, current.value)) {
            // Swap in a newly issued manifest (e.g. after the previous one expired) without a full re-initialize.
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
    media,
    ready,
    error,
    currentTime,
    hasStartedPlayback,
    initialize,
    replace,
    destroy,
  }
}
