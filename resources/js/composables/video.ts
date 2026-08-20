import VideoSessionController from '@/actions/App/Api/Videos/Controllers/VideoSessionController'
import type { Video } from '@/types'
import { router, useHttp } from '@inertiajs/vue3'

export function useVideo() {
  const http = useHttp({ time: null as number | null })

  const markViewed = async (video: Video, time?: number | null): Promise<void> => {
    http.time = time ?? null
    await http.post(VideoSessionController.url({ video: video.id }))
  }

  const toggleLike = (video: Video): void => {
    // toggleGroup(VideoLikeController.url({ video: video.id }))
  }

  const toggleSave = (video: Video): void => {
    // toggleGroup(VideoSaveController.url({ video: video.id }))
  }

  const toggleGroup = (url: string): void =>
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
    markViewed,
    toggleLike,
    toggleSave,
  }
}
