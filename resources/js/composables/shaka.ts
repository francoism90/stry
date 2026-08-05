import { useSettings } from '@/composables/settings'
import { configureOverlay, getShaka, loadShaka } from '@/plugins/shaka'
import { usePlaylistSession } from '@/plugins/shaka/session'
import { tryOnScopeDispose, useEventListener, useThrottleFn, watchDeep, whenever } from '@vueuse/core'
import type shaka from 'shaka-player/dist/shaka-player.ui'
import { ref, shallowRef, toValue, type MaybeRefOrGetter } from 'vue'

export function useShaka(
  manifestUri?: MaybeRefOrGetter<string | null>,
  startTime?: MaybeRefOrGetter<number | null>,
  container?: MaybeRefOrGetter<HTMLElement | undefined>,
  element?: MaybeRefOrGetter<HTMLMediaElement | undefined>,
) {
  const { get, update } = useSettings('player')
  const { updatePlaylistSession } = usePlaylistSession()

  const player = shallowRef<shaka.Player>()
  const error = shallowRef<shaka.util.Error | Error | null>(null)

  const ready = ref<boolean>(false)
  const initializing = ref<boolean>(false)

  const initialize = async () => {
    // Set the initializing state to true
    initializing.value = true

    const assetUri = toValue(manifestUri)
    const videoContainer = toValue(container)
    const mediaElement = toValue(element)

    if (!videoContainer || !mediaElement) return

    try {
      // Destroy the existing player if it exists
      if (player.value) {
        await destroy()
      }

      // Load shaka player
      const shaka = await loadShaka()

      if (!shaka.Player.isBrowserSupported()) {
        error.value = new Error('Your browser cannot play this stream with the current media/DRM settings.')
        return
      }

      // Create a new player instance
      player.value = new shaka.Player()

      // Create a new UI instance
      const ui = new shaka.ui.Overlay(player.value, videoContainer, mediaElement)

      // Configure the UI
      configureOverlay(ui)

      // Configure the player
      const quality = get('quality', 'auto')!
      const maxHeight = quality !== 'auto' ? Number.parseInt(quality, 10) : NaN

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

      // Load the playlist
      await load()
    } finally {
      initializing.value = false
    }
  }

  const load = async () => {
    error.value = null
    const shakaInstance = await loadShaka()

    if (playlist.value?.failed) {
      ready.value = false
      error.value = new shakaInstance.util.Error(
        shakaInstance.util.Error.Severity.CRITICAL,
        shakaInstance.util.Error.Category.MANIFEST,
        shakaInstance.util.Error.Code.MEDIA_SOURCE_OPERATION_FAILED,
      )
      return
    }

    if (playlist.value?.expired) {
      ready.value = false
      error.value = new shakaInstance.util.Error(
        shakaInstance.util.Error.Severity.CRITICAL,
        shakaInstance.util.Error.Category.MANIFEST,
        shakaInstance.util.Error.Code.EXPIRED,
      )
      return
    }

    const manifestUri = playlist.value?.asset ?? null
    if (!manifestUri || !el.value || !player.value) return

    if (playlist.value?.valid) {
      const config: Partial<shaka.extern.PlayerConfiguration> = {}
      const keyId = playlist.value?.encryption_key_id?.toLowerCase() ?? ''
      const keyContent = playlist.value?.encryption_key?.toLowerCase() ?? ''

      if (keyId && keyContent) {
        config.drm = {
          clearKeys: { [keyId]: keyContent },
        } as shaka.extern.DrmConfiguration
      }

      try {
        // Configure the player with DRM settings if available
        if (config.drm) {
          player.value.configure(config)
        }

        // Load the manifest and start playback
        await player.value.load(manifestUri, startTime.value)

        // Select the first text track if captions are enabled
        const textTracks = player.value.getTextTracks()

        if (get('captions', true) && textTracks.length > 0) {
          player.value.selectTextTrack(textTracks[0])
        }

        // Set the ready state to true
        ready.value = true
      } catch (err) {
        // shaka.util.Error intentionally does not extend the native Error at
        // runtime, so both types must be checked to catch real playback errors.
        if (err instanceof Error || err instanceof shakaInstance.util.Error) {
          ready.value = false
          error.value = err as shaka.util.Error | Error
        }
      }
    }
  }

  const setup = async () => {
    if (!player.value) {
      await initialize()
    } else {
      await load()
    }
  }

  const onErrorEvent = (event: Event) => {
    const shakaError = (event as CustomEvent).detail as shaka.util.Error

    if (shakaError.severity === getShaka()?.util.Error.Severity.CRITICAL) {
      error.value = shakaError
      ready.value = false
    }

    console.error('Shaka Player Error:', shakaError)
  }

  const onTimeUpdate = useThrottleFn(() => {
    const currentTime = el.value?.currentTime ?? 0
    const time = Number.isFinite(currentTime) ? Math.round(currentTime * 100) / 100 : 0

    if (playlist.value?.valid && time > 0) {
      updatePlaylistSession(playlist.value, time)
    }
  }, 2500)

  const onVolumeChange = () => {
    if (el.value) {
      update({ muted: el.value.muted, volume: el.value.volume })
    }
  }

  const destroy = async () => {
    try {
      el.value?.pause()
      await ui.value?.destroy()
      await player.value?.destroy()
    } catch (err) {
      console.error('Error destroying Shaka player:', err)
    }

    ui.value = undefined
    player.value = undefined
    ready.value = false
    error.value = null
  }

  useEventListener(() => player.value, 'error', onErrorEvent)
  useEventListener(el, 'timeupdate', onTimeUpdate)
  useEventListener(el, 'volumechange', onVolumeChange)

  whenever(el, () => initialize(), { immediate: true })
  watchDeep(playlist, () => setup())

  tryOnScopeDispose(() => destroy())

  return {
    player,
    playlist,
    ready,
    initializing,
    error,
    initialize,
    destroy,
  }
}
