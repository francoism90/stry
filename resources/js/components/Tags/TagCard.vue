<script setup lang="ts">
<<<<<<< HEAD
import { show } from '@/actions/App/Modules/Tags/Controllers/TagController'
=======
import { show } from '@/actions/App/Web/Tags/Controllers/TagController'
>>>>>>> caaed9fe (fix: refactor tag and video list components to use dynamic color and update layout structure)
import { useAppearance } from '@/composables/appearance'
import type { Tag } from '@/types'

defineProps<{
  item: Tag
}>()

const { dynamicColor } = useAppearance()
</script>

<template>
  <ULink
    :to="show.url(item.id)"
    class="flex flex-col gap-0.5 rounded-lg border-l-6 bg-elevated px-4 py-2.5 transition hover:bg-accented"
    :style="{ borderColor: dynamicColor(item.slug) }"
  >
    <span class="line-clamp-1 text-sm font-semibold capitalize">{{ item.name }}</span>
    <span class="text-xs text-muted">
      {{ Intl.NumberFormat().format(item.videos ?? 0) }}
      {{ (item.videos ?? 0) === 1 ? 'video' : 'videos' }}
    </span>
  </ULink>
</template>
