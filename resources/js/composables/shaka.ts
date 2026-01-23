import shaka from 'shaka-player'
import { onBeforeUnmount, onMounted, ref, watch, type Ref } from 'vue'

interface PlaylistData {
  asset?: string | null
  license?: string | null
}

export function useShaka(
  videoElement: Ref<HTMLVideoElement | undefined>,
  playlist: Ref<PlaylistData | null | undefined>,
) {
  const player = ref<shaka.Player>()
  const loading = ref(false)
  const error = ref<string | null>(null)

  const initPlayer = async (manifestUri: string, licenseServer?: string | null) => {
    if (!videoElement.value) return

    loading.value = true
    error.value = null

    try {
      // Destroy existing player
      if (player.value) {
        await player.value.destroy()
      }

      // Create new player
      player.value = new shaka.Player()
      await player.value.attach(videoElement.value)

      // Configure Clear Key DRM if license provided
      if (licenseServer) {
        player.value.configure({
          drm: {
            servers: {
              'org.w3.clearkey': licenseServer,
            },
          },
        })
      }

      // Add error listener
      player.value.addEventListener('error', onErrorEvent)

      // Load manifest
      await player.value.load(manifestUri)

      loading.value = false
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Failed to load video'

      console.error('Error initializing Shaka Player:', err)

      error.value = errorMessage
      loading.value = false
    }
  }

  const onErrorEvent = (event: Event) => {
    const detail = (event as CustomEvent).detail

    console.error('Shaka Player error:', detail)

    error.value = detail?.message || 'Playback error'
  }

  const destroy = async () => {
    if (player.value) {
      await player.value.destroy()

      player.value = undefined
    }
  }

  onMounted(() => {
    shaka.polyfill.installAll()

    // Initialize player if playlist is available
    if (playlist.value?.asset) {
      initPlayer(playlist.value.asset, playlist.value.license)
    }
  })

  watch(
    () => playlist.value?.asset,
    (newAsset) => {
      if (newAsset) {
        initPlayer(newAsset, playlist.value?.license)
      }
    },
  )

  onBeforeUnmount(() => {
    destroy()
  })

  return {
    player,
    loading,
    error,
    initPlayer,
    destroy,
  }
}
