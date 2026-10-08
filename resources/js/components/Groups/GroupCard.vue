<script setup lang="ts">
import { show } from '@/actions/Modules/Web/Groups/Controllers/GroupController'
import GroupCover from '@/components/Groups/GroupCover.vue'
import type { Group } from '@/types'
import { computed } from 'vue'

const props = defineProps<{
  item: Group
}>()

const videoCount = computed(() => props.item.videos ?? 0)

const videoCountLabel = computed(() => {
  if (videoCount.value === 0) {
    return 'No videos yet'
  }

  return `${Intl.NumberFormat().format(videoCount.value)} ${videoCount.value === 1 ? 'video' : 'videos'}`
})
</script>

<template>
  <ULink
    :to="show.url(item.id)"
    class="group/cover flex min-w-0 flex-col gap-3 rounded-xl focus-visible:ring-2 focus-visible:ring-primary focus-visible:outline-none"
  >
    <GroupCover :item="item" />

    <span class="flex flex-col gap-0.5">
      <span class="truncate text-sm font-semibold text-highlighted capitalize">
        {{ item.title ?? item.type }}
      </span>
      <span class="text-xs text-muted">{{ videoCountLabel }}</span>
    </span>
  </ULink>
</template>
