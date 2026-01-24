import VideoConvertController from '@/actions/App/Admin/Videos/Controllers/VideoConvertController'
import VideoTranscodedController from '@/actions/App/Admin/Videos/Controllers/VideoTranscodedController'
import GroupToggleController from '@/actions/App/Client/Groups/Controllers/GroupToggleController'
import type { Video } from '@/types'
import { router } from '@inertiajs/vue3'

export function useVideo(video: Video) {
  const toggleLike = async () => toggleGroup('liked')
  const toggleSave = async () => toggleGroup('saved')

  const toggleGroup = async (type: string) =>
    router.post(
      GroupToggleController.url({ type, video: video.id }),
      {},
      {
        preserveState: true,
        only: ['video', 'group', 'items'],
      },
    )

  const importTranscoded = (onComplete?: () => void) =>
    router.post(
      VideoTranscodedController.url(video.id),
      {},
      {
        preserveScroll: true,
        onFinish: () => {
          onComplete?.()
        },
      },
    )

  const createConversion = (onComplete?: () => void) =>
    router.post(
      VideoConvertController.url(video.id),
      {},
      {
        preserveState: true,
        onFinish: () => {
          onComplete?.()
        },
      },
    )

  return {
    toggleLike,
    toggleSave,
    importTranscoded,
    createConversion,
  }
}
