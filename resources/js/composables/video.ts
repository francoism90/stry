import VideoSessionController from '@/actions/Modules/Api/Videos/Controllers/VideoSessionController'
import VideoLikeController from '@/actions/Modules/Web/Videos/Controllers/VideoLikeController'
import VideoSaveController from '@/actions/Modules/Web/Videos/Controllers/VideoSaveController'
import type { Video } from '@/types'
import { router, useHttp } from '@inertiajs/vue3'

export function useVideo() {
  const http = useHttp({ time: null as number | null })

  const markViewed = async (video: Video, time?: number | null): Promise<void> => {
    http.time = time ?? null
    await http.post(VideoSessionController.url({ video: video.id }))
  }

  const toggleLike = (video: Pick<Video, 'id'>, only?: string[]): void => {
    toggleGroup(VideoLikeController.url({ video: video.id }), only)
  }

  const toggleSave = (video: Pick<Video, 'id'>, only?: string[]): void => {
    toggleGroup(VideoSaveController.url({ video: video.id }), only)
  }

  const toggleGroup = (url: string, only = ['video', 'group', 'items']): void =>
    router.post(
      url,
      {},
      {
        preserveState: true,
        preserveScroll: true,
        only,
      },
    )

  return {
    markViewed,
    toggleLike,
    toggleSave,
  }
}
