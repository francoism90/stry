import { useSettings } from '@/composables/settings'
import { configureOverlay, loadShaka } from '@/plugins/shaka'
import { tryOnScopeDispose } from '@vueuse/core'
import type shaka from 'shaka-player/dist/shaka-player.ui'
import { computed, ref, shallowRef, toValue, watch, type MaybeRefOrGetter } from 'vue'

const { get, update } = useSettings('player')
// const { updatePlaylistSession } = usePlaylistSession()

export function useShaka(
  asset?: MaybeRefOrGetter<string | null>,
  container?: MaybeRefOrGetter<HTMLElement | undefined>,
  element?: MaybeRefOrGetter<HTMLMediaElement | undefined>,
  starts?: MaybeRefOrGetter<number | null>,
) {
  const player = shallowRef<shaka.Player>()
  const manager = shallowRef<shaka.util.EventManager>()
  const ui = shallowRef<shaka.ui.Overlay>()
  const error = ref<shaka.util.Error | Error | null>(null)

  const ready = computed(() => !!player.value)

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

      // Create a new UI instance
      ui.value = new shakaInstance.ui.Overlay(player.value, videoContainer, mediaElement)

      // Configure the UI
      configureOverlay(ui.value)

      // Attach the player to the media element
      await player.value.attach(mediaElement)

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
    } catch (err) {
      error.value = err as shaka.util.Error | Error
      console.error('Error initializing Shaka player:', err)
    }
  }

  const load = async (manifest: string | null, startTime?: number | null) => {
    if (!player.value || !manifest) {
      return
    }

    try {
      await player.value.load(manifest, startTime)
      error.value = null
    } catch (err) {
      error.value = err as shaka.util.Error | Error
      console.error('Error loading manifest:', err)
    }
  }

  const destroy = async () => {
    try {
      await manager.value?.release()
      await ui.value?.destroy()
      await player.value?.destroy()
    } catch (err) {
      console.error('Error destroying Shaka player:', err)
    }

    ui.value = undefined
    player.value = undefined
    error.value = null
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
