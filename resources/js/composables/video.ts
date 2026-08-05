import VideoSessionController from '@/actions/App/Api/Videos/Controllers/VideoSessionController'
import VideoLikeController from '@/actions/App/Web/Videos/Controllers/VideoLikeController'
import VideoSaveController from '@/actions/App/Web/Videos/Controllers/VideoSaveController'
import type { Video } from '@/types'
import { router, useHttp } from '@inertiajs/vue3'
import { computed, toValue, type MaybeRefOrGetter } from 'vue'

export function useVideo(video?: MaybeRefOrGetter<Video | null>) {
  const http = useHttp({ time: null as number | null })

  const model = computed(() => toValue(video) as Video | null)

  const markViewed = async (time?: number | null): Promise<void> => {
    const videoId = model.value?.id ?? null

    if (videoId) {
      http.time = time ?? null
      http.post(VideoSessionController.url({ video: videoId }))
    }
  }

  const toggleLike = async () => {
    const videoId = model.value?.id ?? null

    if (videoId) {
      toggleGroup(VideoLikeController.url({ video: videoId }))
    }
  }

  const toggleSave = async () => {
    const videoId = model.value?.id

    if (videoId) {
      toggleGroup(VideoSaveController.url({ video: videoId }))
    }
  }

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
    markViewed,
    toggleLike,
    toggleSave,
  }
}
