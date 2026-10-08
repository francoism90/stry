<script setup lang="ts">
import VideoDeleteModal from '@/components/Videos/VideoDeleteModal.vue'
import VideoEditModal from '@/components/Videos/VideoEditModal.vue'
import VideoRow from '@/components/Videos/VideoRow.vue'
import { show } from '@/routes/videos'
import type { OptionItem, Video } from '@/types'
import { defineProps, ref } from 'vue'

defineProps<{
  items?: Video[] | undefined
  chapters?: OptionItem[] | undefined
}>()

const editingItem = ref<Video>()
const isEditModalOpen = ref(false)

const edit = (item: Video): void => {
  editingItem.value = item
  isEditModalOpen.value = true
}
</script>

<template>
  <div class="flex flex-col gap-3">
    <div
      v-if="items === undefined"
      class="flex flex-col gap-1"
      aria-hidden="true"
    >
      <div
        v-for="i in 3"
        :key="i"
        class="flex items-center gap-3 p-2"
      >
        <USkeleton class="aspect-video w-24 shrink-0 rounded-lg bg-(--glass) sm:w-32" />
        <div class="flex flex-1 flex-col gap-2">
          <USkeleton class="h-3.5 w-1/2 rounded-sm bg-(--glass)" />
          <USkeleton class="h-3 w-1/3 rounded-sm bg-(--glass)" />
        </div>
      </div>
    </div>

    <ul
      v-else-if="items.length"
      class="flex flex-col gap-1"
    >
      <VideoRow
        v-for="item in items"
        :key="item.id"
        :item="item"
      >
        <template #meta>
          <span class="dot-separated flex flex-wrap">
            <span
              v-for="detail in [item.filesize, item.codec, item.resolution, item.bitrate].filter(Boolean)"
              :key="detail"
            >
              {{ detail }}
            </span>
          </span>
        </template>

        <template #actions>
          <div class="flex shrink-0 items-center gap-1">
            <UButton
              :to="show.url(item.id)"
              icon="i-lucide-eye"
              :aria-label="`Watch ${item.title}`"
              color="neutral"
              variant="ghost"
              size="sm"
              class="rounded-full text-muted hover:bg-(--glass-strong) hover:text-highlighted"
            />

            <UButton
              v-if="item.manage"
              icon="i-lucide-pencil"
              :aria-label="`Edit ${item.title}`"
              color="neutral"
              variant="ghost"
              size="sm"
              class="rounded-full text-muted hover:bg-(--glass-strong) hover:text-highlighted"
              @click="edit(item)"
            />

            <VideoDeleteModal :item="item">
              <UButton
                icon="i-lucide-trash-2"
                :aria-label="`Delete ${item.title}`"
                color="error"
                variant="ghost"
                size="sm"
                class="rounded-full"
              />
            </VideoDeleteModal>
          </div>
        </template>
      </VideoRow>
    </ul>

    <UEmpty
      v-else
      icon="i-lucide-library-big"
      title="No videos"
      description="Import a video to get started."
      variant="naked"
      class="py-24"
    />

    <VideoEditModal
      v-if="editingItem"
      v-model:open="isEditModalOpen"
      :video="editingItem"
      :chapters="chapters"
      :media="editingItem.media"
      :transcodes="editingItem.transcodes"
    />
  </div>
</template>
