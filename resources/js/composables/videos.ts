import VideoGroupController from '@/actions/App/Api/Videos/Controllers/VideoGroupController'
import type { Video } from '@/types'
import { router } from '@inertiajs/vue3'

export function useVideos() {
  const toggleGroup = (video: Video, type: string) =>
    router.post(
      VideoGroupController.url({ video: video.id, type }),
      {},
      {
        preserveState: true,
        only: ['video'],
      },
    )

  return {
    toggleGroup,
  }
}
