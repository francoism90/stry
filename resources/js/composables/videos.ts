import VideoImportController from '@/actions/App/Admin/Videos/Controllers/VideoImportController'
import GroupClearController from '@/actions/App/Client/Groups/Controllers/GroupClearController'
import GroupToggleController from '@/actions/App/Client/Groups/Controllers/GroupToggleController'
import type { Video } from '@/types'
import { router } from '@inertiajs/vue3'

export function useVideos() {
  const toggleLike = async (video: Video) => toggleGroup(video, 'liked')
  const toggleSave = async (video: Video) => toggleGroup(video, 'saved')

  const toggleGroup = async (video: Video, type: string) =>
    router.post(
      GroupToggleController.url({ type, video: video.id }),
      {},
      {
        preserveState: true,
        only: ['video', 'group', 'items'],
      },
    )

  const clearGroup = async (type: string) =>
    router.post(
      GroupClearController.url({ type }),
      {},
      {
        preserveState: true,
        only: ['video', 'group', 'items'],
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
    clearGroup,
    importVideos,
  }
}
