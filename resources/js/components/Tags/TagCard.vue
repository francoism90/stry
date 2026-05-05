<script setup lang="ts">
import { show } from '@/actions/App/Web/Tags/Controllers/TagController'
import type { Tag } from '@/types'

defineProps<{
  item: Tag
}>()

function tagColor(slug: string): string {
  let hash = 0
  for (let i = 0; i < slug.length; i++) {
    hash = slug.charCodeAt(i) + ((hash << 5) - hash)
  }
  const hue = Math.abs(hash) % 360
  return `hsl(${hue}, 60%, 30%)`
}
</script>

<template>
  <ULink
    :to="show.url(item.id)"
    class="flex flex-col gap-0.5 rounded-lg border-l-6 bg-elevated px-4 py-2.5 transition hover:bg-accented"
    :style="{ borderColor: tagColor(item.slug) }"
  >
    <span class="line-clamp-1 text-sm font-semibold capitalize">{{ item.name }}</span>
    <span class="text-xs text-muted">
      {{ Intl.NumberFormat().format(item.videos ?? 0) }}
      {{ (item.videos ?? 0) === 1 ? 'video' : 'videos' }}
    </span>
  </ULink>
</template>
