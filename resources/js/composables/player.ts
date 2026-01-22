import PlaylistLicenseController from '@/actions/App/Api/Playlists/Controllers/PlaylistLicenseController'
import PlaylistSessionController from '@/actions/App/Api/Playlists/Controllers/PlaylistSessionController'
import type { Playlist, Video } from '@/types'
import { http } from '@/utils/http'
import { usePage } from '@inertiajs/vue3'
import { useThrottleFn } from '@vueuse/core'
import { isHLSProvider, type MediaProviderAdapter, type MediaProviderChangeEvent } from 'vidstack'
import { computed } from 'vue'

export function usePlayer() {
  const playlist = computed(() => usePage().props.playlist as Playlist | null)
  const video = computed(() => usePage().props.video as Video | null)
  const progress = computed(() => usePage().props.progress as number | null)

  const store = useThrottleFn(async (value: number) => {
    // Round to 2 decimal places and ensure valid number
    const time = Number.isFinite(value) ? Math.round(value * 100) / 100 : 0

    // Only store if playlist exists and time has changed significantly (> 0.5 seconds)
    if (playlist.value && Math.abs((progress.value ?? 0) - time) > 0.5) {
      await http.post(PlaylistSessionController.url(playlist.value.id), { time })
    }
  }, 2500)

  /**
   * Configure HLS provider for Clear Key DRM support
   */
  const onProviderChange = (provider: MediaProviderAdapter | null, nativeEvent: MediaProviderChangeEvent) => {
    if (!provider || !isHLSProvider(provider)) {
      return
    }

    // Configure hls.js for Clear Key EME support
    provider.config = {
      // Enable EME for encrypted content
      emeEnabled: true,

      // Clear Key DRM configuration
      drmSystems: {
        'org.w3.clearkey': {
          // License server endpoint
          licenseUrl: playlist.value ? PlaylistLicenseController.url(playlist.value.id) : '',
        },
      },
    }
  }

  return {
    playlist,
    video,
    progress,
    store,
    onProviderChange,
  }
}
