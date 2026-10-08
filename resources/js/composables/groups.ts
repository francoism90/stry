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

/**
 * Tailwind classes per collection type for the stacked cover: two layers behind it, and a wash over the picture.
 */
const groupCovers: Record<string, { back: string; middle: string; wash: string }> = {
  custom: {
    back: 'bg-violet-500/35',
    middle: 'bg-violet-500/65',
    wash: 'from-purple-700/95 via-violet-500/45',
  },
  liked: {
    back: 'bg-rose-500/35',
    middle: 'bg-rose-500/65',
    wash: 'from-pink-700/95 via-rose-500/45',
  },
  mixer: {
    back: 'bg-indigo-500/35',
    middle: 'bg-indigo-500/65',
    wash: 'from-blue-700/95 via-indigo-500/45',
  },
  saved: {
    back: 'bg-sky-500/35',
    middle: 'bg-sky-500/65',
    wash: 'from-cyan-700/95 via-sky-500/45',
  },
  viewed: {
    back: 'bg-emerald-500/35',
    middle: 'bg-emerald-500/65',
    wash: 'from-green-700/95 via-emerald-500/45',
  },
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

  const groupCover = (type: string | null | undefined) =>
    groupCovers[type ?? ''] ?? {
      back: 'bg-neutral-500/35',
      middle: 'bg-neutral-500/65',
      wash: 'from-neutral-700/95 via-neutral-500/45',
    }

  return {
    clearGroup,
    groupCover,
    groupIcon,
    groupGradient,
  }
}
