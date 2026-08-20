<script setup lang="ts">
import { show } from '@/actions/App/Web/Tags/Controllers/TagController'
import TagEditModal from '@/components/Tags/TagEditModal.vue'
import { useAppearance } from '@/composables/appearance'
import type { Tag } from '@/types'

defineProps<{
  item: Tag
}>()

const { dynamicColor } = useAppearance()
</script>

<template>
  <div
    class="group/card relative flex flex-col gap-0.5 rounded-lg border-l-6 bg-elevated px-4 py-2.5 transition hover:bg-accented"
    :style="{ borderColor: dynamicColor(item.slug) }"
  >
    <ULink
      :to="show.url(item.id)"
      class="flex flex-col gap-0.5"
    >
      <span class="line-clamp-1 text-sm font-semibold capitalize">{{ item.name }}</span>
      <span class="text-xs text-muted">
        {{ Intl.NumberFormat().format(item.videos ?? 0) }}
        {{ (item.videos ?? 0) === 1 ? 'video' : 'videos' }}
      </span>
    </ULink>

    <TagEditModal :item="item">
      <UButton
        icon="i-lucide-pencil"
        color="neutral"
        variant="ghost"
        size="xs"
        class="absolute top-1 right-1 opacity-0 transition-opacity group-hover/card:opacity-100"
      />
    </TagEditModal>
  </div>
</template>
