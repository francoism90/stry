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

  return {
    toggleLike,
    toggleSave,
  }
}
