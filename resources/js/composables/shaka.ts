import { useSettings } from '@/composables/settings'
import { configureOverlay, getShaka, loadShaka } from '@/plugins/shaka'
import { usePlaylistSession } from '@/plugins/shaka/session'
import type { Playlist } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { tryOnScopeDispose, useEventListener, useThrottleFn, watchDeep, whenever } from '@vueuse/core'
import type shaka from 'shaka-player/dist/shaka-player.ui'
import { computed, ref, shallowRef, toValue, type MaybeRefOrGetter } from 'vue'

export function useShaka(
  container?: MaybeRefOrGetter<HTMLElement | undefined>,
  element?: MaybeRefOrGetter<HTMLMediaElement | undefined>,
) {
  const playlist = computed(() => usePage().props.playlist as Playlist | null)
  const startTime = computed(() => usePage().props.progress as number | null)

  const { get, update } = useSettings('player')
  const { updatePlaylistSession } = usePlaylistSession()
  const el = computed(() => toValue(element))

  const player = shallowRef<shaka.Player>()
  const ui = shallowRef<shaka.ui.Overlay>()
  const initializing = ref<boolean>(false)
  const error = ref<shaka.util.Error | Error | null>(null)
  const ticker = ref<number>(startTime.value ?? 0)

  const ready = computed<boolean>(() => !!player.value)

  const initialize = async () => {
    if (initializing.value) return
    initializing.value = true

    try {
      if (player.value) {
        await destroy()
      }

      if (!el.value) return

      const shakaInstance = await loadShaka()

      if (!shakaInstance.Player.isBrowserSupported()) {
        error.value = new Error('Your browser cannot play this stream with the current media/DRM settings.')
        return
      }

      player.value = new shakaInstance.Player()

      ui.value = new shakaInstance.ui.Overlay(
        player.value,
        toValue(container) as HTMLElement,
        toValue(element) as HTMLMediaElement,
      )

      configureOverlay(ui.value)
      await player.value.attach(el.value)

      const quality = get('quality', 'auto')!

      player.value.configure({
        preferredAudio: [{ language: get('audio_language', 'en')! }],
        preferredText: [{ language: get('caption_language', 'en')! }],
        abr: {
          restrictions: quality !== 'auto' ? { maxHeight: parseInt(quality), minHeight: parseInt(quality) } : {},
        },
        streaming: {
          bufferingGoal: 16,
          ignoreTextStreamFailures: true,
        },
        manifest: {
          dash: { xlinkFailGracefully: true },
          hls: { ignoreTextStreamFailures: true, ignoreImageStreamFailures: true },
        },
      })

      el.value.muted = get('muted', false)!
      el.value.volume = get('volume', 1)!
      el.value.playbackRate = get('playback_speed', 1)!

      await load()
    } finally {
      initializing.value = false
    }
  }

  const load = async () => {
    error.value = null
    const shakaInstance = await loadShaka()

    if (playlist.value?.failed) {
      error.value = new shakaInstance.util.Error(
        shakaInstance.util.Error.Severity.CRITICAL,
        shakaInstance.util.Error.Category.MANIFEST,
        shakaInstance.util.Error.Code.MEDIA_SOURCE_OPERATION_FAILED,
      )
      return
    }

    if (playlist.value?.expired) {
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
        if (config.drm) {
          player.value.configure(config)
        }

        await player.value.load(manifestUri, startTime.value)

        const textTracks = player.value.getTextTracks()
        if (get('captions', true) && textTracks.length > 0) {
          player.value.selectTextTrack(textTracks[0])
        }
      } catch (err) {
        if (err instanceof Error) {
          error.value = err
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
  }, 1800)

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
