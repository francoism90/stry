import { clear } from '@/routes/actions/groups'
import type { Group } from '@/types'
import { router } from '@inertiajs/vue3'

const groupIcons: Record<string, string> = {
  custom: 'i-lucide-folder',
  liked: 'i-lucide-heart',
  mixer: 'i-lucide-shuffle',
  saved: 'i-lucide-bookmark',
  viewed: 'i-lucide-history',
}

const groupGradients: Record<string, string> = {
  custom: 'from-violet-500 to-purple-700',
  liked: 'from-rose-500 to-pink-700',
  mixer: 'from-indigo-500 to-blue-700',
  saved: 'from-sky-500 to-cyan-700',
  viewed: 'from-emerald-500 to-green-700',
}

export function useGroups() {
  const clearGroup = async (group: Group) =>
    router.post(
      clear.url(group.id),
      {},
      {
        preserveScroll: true,
      },
    )

  const groupIcon = (type: string | null | undefined): string => groupIcons[type ?? ''] ?? 'i-lucide-layers'

  const groupGradient = (type: string | null | undefined): string =>
    groupGradients[type ?? ''] ?? 'from-neutral-500 to-neutral-700'

  return {
    clearGroup,
    groupIcon,
    groupGradient,
  }
}
