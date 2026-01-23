import MediaConvertedController from '@/actions/App/Admin/Media/Controllers/MediaConvertedController'
import MediaTranscodeController from '@/actions/App/Admin/Media/Controllers/MediaTranscodeController'
import type { Media } from '@/types'
import { router } from '@inertiajs/vue3'

export function useMedia(media: Media) {
  const toast = useToast()

  const getFirstStream = () => media.custom_properties?.streams?.[0]

  const startConversion = () => {
    router.post(
      MediaTranscodeController.url(media.id),
      {},
      {
        preserveState: true,
        onSuccess: () =>
          toast.add({
            title: 'Conversion Started',
            description: 'AV1 conversion has been queued.',
            icon: 'i-lucide-play',
            color: 'primary',
          }),
      },
    )
  }

  const addTranscodes = () => {
    router.post(
      MediaConvertedController.url(media.id),
      {},
      {
        preserveState: true,
        onSuccess: () =>
          toast.add({
            title: 'Transcodes Added',
            description: 'All successful transcodes have been added to the media library.',
            icon: 'i-lucide-check',
            color: 'success',
          }),
      },
    )
  }

  const getStreamInfo = () => {
    const stream = getFirstStream()

    if (!stream) return []

    return [
      {
        label: `${stream.width}×${stream.height}`,
        color: 'secondary' as const,
      },
      {
        label: `${Math.round(Number(stream.bit_rate) / 1000)}kbps`,
        color: 'secondary' as const,
      },
      {
        label: `${parseFloat(stream.duration).toFixed(1)}s`,
        color: 'secondary' as const,
      },
      {
        label: stream.codec_name.toUpperCase(),
        color: 'secondary' as const,
      },
      ...(stream.closed_captions > 0
        ? [
            {
              label: 'CC',
              color: 'primary' as const,
            },
          ]
        : []),
    ]
  }

  return {
    startConversion,
    addTranscodes,
    getStreamInfo,
  }
}
