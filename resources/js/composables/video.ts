import VideoLikeController from '@/actions/App/Web/Videos/Controllers/VideoLikeController'
import VideoSaveController from '@/actions/App/Web/Videos/Controllers/VideoSaveController'
import type { Video } from '@/types'
import { router } from '@inertiajs/vue3'

export function useVideo(video: Video) {
  const http = useHttp({ time: 0 })

  const record = async (time: number): Promise<void> => {
    http.time = time
    http.post(PlaylistSessionController.url(playlist.id))
  }

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
    record,
    toggleLike,
    toggleSave,
  }
}
