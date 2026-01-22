import MediaConvertController from '@/actions/App/Admin/Media/Controllers/MediaConvertController'
import MediaTranscodeReplaceController from '@/actions/App/Admin/Media/Controllers/MediaTranscodeReplaceController'
import type { Media, Transcode } from '@/types'
import { router } from '@inertiajs/vue3'

export function useMedia(media: Media) {
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

  const replaceWithTranscode = (transcode: Transcode) => {
    router.post(
      MediaTranscodeReplaceController.url([media.id, transcode.id]),
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

  return {
    startConversion,
    replaceWithTranscode,
  }
}
