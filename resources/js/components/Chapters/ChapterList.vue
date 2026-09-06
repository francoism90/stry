<script setup lang="ts">
import ChapterCreateModal from '@/components/Chapters/ChapterCreateModal.vue'
import ChapterDeleteModal from '@/components/Chapters/ChapterDeleteModal.vue'
import ChapterEditModal from '@/components/Chapters/ChapterEditModal.vue'
import type { Chapter, OptionItem, Video } from '@/types'
import { formatDuration } from '@/utils/duration'
import { capitalize } from '@/utils/case'

defineProps<{
  video: Video
  items?: Chapter[] | undefined
  types?: OptionItem[] | undefined
}>()
</script>

<template>
  <div class="flex flex-col gap-3">
    <div class="flex items-center gap-2">
      <ChapterCreateModal
        :video="video"
        :types="types"
      />
    </div>

    <div
      v-if="items === undefined"
      class="flex flex-col gap-2"
    >
      <USkeleton
        v-for="i in 3"
        :key="i"
        class="h-14 w-full rounded-md"
      />
    </div>

    <UPageList
      v-else-if="items.length"
      divide
    >
      <UPageCard
        v-for="item in items"
        :key="item.id"
        variant="naked"
        class="py-3 first:pt-0 last:pb-0"
      >
        <div class="flex items-center justify-between">
          <UUser
            :name="item.label"
            :description="`${capitalize(item.type)} · ${formatDuration(item.start_time)} - ${formatDuration(item.end_time)}`"
          />

          <div class="z-10 flex items-center gap-2">
            <ChapterEditModal
              :video="video"
              :item="item"
              :types="types"
            />
            <ChapterDeleteModal
              :video="video"
              :item="item"
            />
          </div>
        </div>
      </UPageCard>
    </UPageList>
  </div>
</template>
