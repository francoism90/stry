import GroupClearController from '@/actions/App/Api/Groups/Controllers/GroupClearController'
import { router } from '@inertiajs/vue3'

export function useGroups() {
  const clearGroup = async (type: string) =>
    router.post(
      GroupClearController.url({ type }),
      {},
      {
        preserveState: true,
        preserveScroll: true,
      },
    )

  return {
    clearGroup,
  }
}
