import MediaConvertedController from '@/actions/App/Admin/Media/Controllers/MediaConvertedController'
import MediaTranscodeController from '@/actions/App/Admin/Media/Controllers/MediaTranscodeController'
import type { Media } from '@/types'
import { router } from '@inertiajs/vue3'

export function useMedia(media: Media) {
  const toast = useToast()

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

  return {
    startConversion,
    addTranscodes,
  }
}
