import MediaConvertController from '@/actions/App/Admin/Media/Controllers/MediaConvertController'
import MediaTranscodeReplaceController from '@/actions/App/Admin/Media/Controllers/MediaTranscodeReplaceController'
import type { Media, Video } from '@/types'
import { router } from '@inertiajs/vue3'

export function useMedia(video: Video, media: Media) {
  const toast = useToast()

  const startConversion = () => {
    router.post(
      MediaConvertController.url([media.id]),
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

  const replaceWithTranscode = (transcodeId: number) => {
    router.post(
      MediaTranscodeReplaceController.url([media.id, transcodeId]),
      {},
      {
        preserveState: true,
        onSuccess: () =>
          toast.add({
            title: 'Media Replaced',
            description: 'Original media has been replaced with AV1 version.',
            icon: 'i-lucide-check',
            color: 'success',
          }),
      },
    )
  }

  const getStateColor = (state: string) => {
    switch (state) {
      case 'completed':
        return 'success'
      case 'processing':
        return 'primary'
      case 'failed':
        return 'error'
      default:
        return 'neutral'
    }
  }

  return {
    startConversion,
    replaceWithTranscode,
    getStateColor,
  }
}
