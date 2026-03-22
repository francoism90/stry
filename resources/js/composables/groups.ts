import { clear } from '@/routes/actions/groups'
import type { Group } from '@/types'
import { router } from '@inertiajs/vue3'

export function useGroups() {
  const clearGroup = async (group: Group) =>
    router.post(
      clear.url(group.id),
      {},
      {
        preserveScroll: true,
      },
    )

  return {
    clearGroup,
  }
}
