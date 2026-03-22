import VideoLikeController from '@/actions/App/Api/Videos/Controllers/VideoLikeController'
import VideoSaveController from '@/actions/App/Api/Videos/Controllers/VideoSaveController'
import type { Video } from '@/types'
import { router } from '@inertiajs/vue3'

export function useVideo(video: Video) {
  const toggleLike = async () => toggleGroup(VideoLikeController.url({ video: video.id }))
  const toggleSave = async () => toggleGroup(VideoSaveController.url({ video: video.id }))

  const toggleGroup = async (url: string) =>
    router.post(
      url,
      {},
      {
        preserveState: true,
        preserveScroll: true,
        only: ['video', 'group', 'items'],
      },
    )

  return {
    toggleLike,
    toggleSave,
  }
}
