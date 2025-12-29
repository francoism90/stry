import VideoGroupController from '@/actions/App/Api/Videos/Controllers/VideoGroupController'
import type { Video } from '@/types'
import { router } from '@inertiajs/vue3'

export function useVideos() {
  const toggleFavorite = async (video: Video) => await toggleGroup(video, 'favorite')

  const toggleSaved = async (video: Video) => await toggleGroup(video, 'saved')

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
    toggleFavorite,
    toggleSaved,
  }
}
