import VideoImportController from '@/actions/App/Admin/Videos/Controllers/VideoImportController'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

export function useVideos() {
  const importing = ref(false)
  const error = ref<string | null>(null)

  const importVideos = async (onComplete?: () => void) => {
    importing.value = true
    error.value = null

    try {
      await router.post(
        VideoImportController.url(),
        {},
        {
          preserveScroll: true,
          preserveState: true,
          onFinish: () => {
            importing.value = false
            onComplete?.()
          },
          onError: (errors) => {
            importing.value = false
            error.value = 'Import failed. Please try again.'
            console.error('Import error:', errors)
          },
        },
      )
    } catch {
      importing.value = false
      error.value = 'An unexpected error occurred.'
    }
  }

  return { importVideos, importing, error }
}
