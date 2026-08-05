import { useSettings } from '@/composables/settings'
import { configureOverlay, getShaka, loadShaka } from '@/plugins/shaka'
import { usePlaylistSession } from '@/plugins/shaka/session'
import { tryOnScopeDispose, useEventListener, useThrottleFn, watchDeep, whenever } from '@vueuse/core'
import type shaka from 'shaka-player/dist/shaka-player.ui'
import { ref, shallowRef, toValue, type MaybeRefOrGetter } from 'vue'

export function useShaka(
  assetUri?: MaybeRefOrGetter<string | null | undefined>,
  startTime?: MaybeRefOrGetter<number | null | undefined>,
  container?: MaybeRefOrGetter<HTMLElement | undefined>,
  element?: MaybeRefOrGetter<HTMLMediaElement | undefined>,
) {
  const { get, update } = useSettings('player')
  const { updatePlaylistSession } = usePlaylistSession()

  const player = shallowRef<shaka.Player>()
  const ui = shallowRef<shaka.ui.Overlay>()
  const error = ref<shaka.util.Error | null>(null)

  const initializing = ref<boolean>(false)
  const ready = ref<boolean>(false)
  const ticker = ref<number>(0)

  const initialize = async () => {
    // Get the current values of the container and media element
    const videoContainer = toValue(container)
    const mediaElement = toValue(element)

    if (!videoContainer || !mediaElement) {
      return
    }

    try {
      // If a player instance already exists, destroy it before creating a new one
      if (player.value) {
        await destroy()
      }

      // Load shaka player
      const shakaInstance = await loadShaka()

      if (!shakaInstance.Player.isBrowserSupported()) {
        error.value = new Error('Your browser cannot play this stream with the current media/DRM settings.')
        return
      }

      // Create a new player instance
      player.value = new shakaInstance.Player()

      // Create a new UI instance
      ui.value = new shakaInstance.ui.Overlay(
        player.value,
        toValue(container) as HTMLElement,
        toValue(element) as HTMLMediaElement,
      )

      // Configure the UI
      configureOverlay(ui.value)

      // Attach the player to the media element
      await player.value.attach(el.value)

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
      el.value.muted = get('muted', false)!
      el.value.volume = get('volume', 1)!
      el.value.playbackRate = get('playback_speed', 1)!

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

    if (playlist.value?.valid && time > 0 && Math.abs((ticker.value ?? 0) - time) > 0.25) {
      updatePlaylistSession(playlist.value, time)
      ticker.value = time
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
