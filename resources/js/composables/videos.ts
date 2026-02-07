import VideoImportController from '@/actions/App/Admin/Videos/Controllers/VideoImportController'
import { router } from '@inertiajs/vue3'

export function useVideos() {
  const importVideos = (onComplete?: () => void) =>
    router.post(
      VideoImportController.url(),
      {},
      {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
          onComplete?.()
        },
      },
    )

  return {
    importVideos,
  }
}
