<script setup lang="ts">
import { show } from '@/actions/App/Web/Tags/Controllers/TagController'
import type { Tag } from '@/types'

const props = defineProps<{
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
    :to="show.url(props.item.slug)"
    class="bg-elevated hover:bg-accented flex flex-col gap-0.5 rounded-lg border-l-6 px-4 py-2.5 transition"
    :style="{ borderColor: tagColor(props.item.slug) }"
  >
    <span class="line-clamp-1 text-sm font-semibold">{{ props.item.name }}</span>
    <span class="text-muted text-xs">{{ props.item.videos }} {{ props.item.videos === 1 ? 'video' : 'videos' }}</span>
  </ULink>
</template>
