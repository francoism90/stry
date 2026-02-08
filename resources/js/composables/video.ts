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
        preserveScroll: true,
        only: ['video', 'group', 'items'],
      },
    )

  return {
    toggleLike,
    toggleSave,
  }
}
