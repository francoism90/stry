import { useSettings } from '@/composables/settings'
import { configureOverlay, getShaka, loadShaka } from '@/plugins/shaka'
import { usePlaylistSession } from '@/plugins/shaka/session'
import { tryOnScopeDispose } from '@vueuse/core'
import type shaka from 'shaka-player/dist/shaka-player.ui'
import { ref, shallowRef, toValue, watch, type MaybeRefOrGetter } from 'vue'

const { get, update } = useSettings('player')
const { updatePlaylistSession } = usePlaylistSession()

export function useShaka(
  asset?: MaybeRefOrGetter<string | null>,
  container?: MaybeRefOrGetter<HTMLElement | undefined>,
  element?: MaybeRefOrGetter<HTMLMediaElement | undefined>,
  starts?: MaybeRefOrGetter<number | null>,
) {
  const player = shallowRef<shaka.Player>()
  const manager = shallowRef<shaka.util.EventManager>()
  const ui = shallowRef<shaka.ui.Overlay>()

  const ready = ref<boolean>(false)
  const error = ref<shaka.util.Error | Error | null>(null)
  const ticker = ref<number | null>(null)

  const initialize = async (videoContainer: HTMLElement | undefined, mediaElement: HTMLMediaElement | undefined) => {
    if (player.value || !videoContainer || !mediaElement) {
      return
    }

    try {
      // Load shaka player
      const shakaInstance = await loadShaka()

      if (!shakaInstance.Player.isBrowserSupported()) {
        console.error('Your browser cannot play this stream with the current media/DRM settings.')
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
    }
  }

  const load = async (manifest: string | null, startTime?: number | null) => {
    if (!player.value || !manifest) {
      return
    }

    try {
      // Load the manifest into the player
      await player.value.load(manifest, startTime)

      error.value = null
      ready.value = true
    } catch (err) {
      onErrorEvent(new CustomEvent('error', { detail: err }))
    }
  }

  const destroy = async () => {
    try {
      await manager.value?.release()
      await ui.value?.destroy()
      await player.value?.destroy()
    } catch (err) {
      onErrorEvent(new CustomEvent('error', { detail: err }))
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
    ready.value = false
    error.value = null
    ticker.value = null
  }

  const onErrorEvent = (event: Event) => {
    const shakaError = (event as CustomEvent).detail as shaka.util.Error

    if (shakaError.severity === getShaka()?.util.Error.Severity.CRITICAL) {
      error.value = shakaError
      ready.value = false
    }

    console.error('Shaka Player Error:', shakaError)
  }

  const onTimeUpdate = (event: Event) => {
    const el = event.target as HTMLMediaElement | null

    if (el) {
      const currentTime = el.currentTime ?? 0
      const time = Number.isFinite(currentTime) ? Math.round(currentTime * 100) / 100 : 0

      if (time > 0 && Math.abs((ticker.value ?? 0) - time) > 0.25) {
        updatePlaylistSession(playlist.value, time)
        ticker.value = time
      }
    }
  }

  const onVolumeChange = (event: Event) => {
    const el = event.target as HTMLMediaElement | null

    if (el) {
      update({ muted: el.muted, volume: el.volume })
    }
  }

  watch(
    () => [asset, container, element],
    async ([reqAsset, reqContainer, reqElement]) => {
      const manifest = toValue(reqAsset) as string | null
      const videoContainer = toValue(reqContainer) as HTMLElement | undefined
      const mediaElement = toValue(reqElement) as HTMLMediaElement | undefined

      const currentManifest = player.value?.getManifest() ?? null
      const currentMediaElement = player.value?.getMediaElement() ?? null

      // If the manifest or media element has changed, destroy the current player instance
      if (mediaElement !== currentMediaElement) {
        await destroy()
      }

      // Initialize the player with the new video container and media element
      await initialize(videoContainer, mediaElement)

      // Load the new manifest if it has changed
      if (manifest !== currentManifest) {
        await load(manifest, toValue(starts) as number | null)
      }
    },
  )

  tryOnScopeDispose(() => destroy())

  return {
    player,
    ready,
    error,
    initialize,
    destroy,
  }
}
