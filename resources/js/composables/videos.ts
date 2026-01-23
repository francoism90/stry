import VideoImportController from '@/actions/App/Admin/Videos/Controllers/VideoImportController'
import VideoGroupController from '@/actions/App/Api/Videos/Controllers/VideoGroupController'
import type { Video } from '@/types'
import { router } from '@inertiajs/vue3'

const toast = useToast()

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

  const importVideos = (onSuccess?: () => void, onError?: () => void) => {
    router.post(
      VideoImportController.url(),
      {},
      {
        preserveScroll: true,
        onSuccess: (page) => {
          const flash = page.props.flash as any
          if (flash?.success) {
            toast.add({
              title: 'Import Started',
              description: flash.success,
              icon: 'i-lucide-check',
              color: 'success',
            })
          }
          onSuccess?.()
        },
        onError: (errors) => {
          const flash = (errors as any).flash
          const errorMessage = flash?.error || 'An error occurred during import.'
          toast.add({
            title: 'Import Failed',
            description: errorMessage,
            icon: 'i-lucide-alert-circle',
            color: 'error',
          })
          onError?.()
        },
      },
    )
  }

  return {
    toggleLike,
    toggleSave,
    importVideos,
  }
}
