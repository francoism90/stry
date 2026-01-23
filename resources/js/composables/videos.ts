import VideoImportController from '@/actions/App/Admin/Videos/Controllers/VideoImportController'
import VideoGroupController from '@/actions/App/Api/Videos/Controllers/VideoGroupController'
import type { Video } from '@/types'
import { router } from '@inertiajs/vue3'

export function useVideos() {
  const toggleLike = async (video: Video) => toggleGroup(video, 'liked')

  const toggleSave = async (video: Video) => toggleGroup(video, 'saved')

  const toggleGroup = async (video: Video, type: string) =>
    router.post(
      VideoGroupController.url({ video: video.id, type }),
      {},
      {
        preserveState: true,
        only: ['video'],
      },
    )

  const importVideos = (onComplete?: () => void) =>
    router.post(
      VideoImportController.url(),
      {},
      {
        preserveScroll: true,
        onFinish: () => {
          onComplete?.()
        },
      },
    )

  return {
    toggleLike,
    toggleSave,
    importVideos,
  }
}
