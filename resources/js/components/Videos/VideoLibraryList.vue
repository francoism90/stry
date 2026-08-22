<script setup lang="ts">
import VideoDeleteModal from '@/components/Videos/VideoDeleteModal.vue'
import VideoEditModal from '@/components/Videos/VideoEditModal.vue'
import { show } from '@/routes/videos'
import type { Video } from '@/types'
import { ref } from 'vue'

defineProps<{
  items?: Video[] | undefined
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
            :name="item.title"
            :description="[item.created_at, item.filesize, item.codec?.toUpperCase()].filter(Boolean).join(' · ')"
            :avatar="{
              src: item.thumb ?? undefined,
              alt: item.title,
              loading: 'lazy',
              decoding: 'async',
              class: 'rounded-sm size-10 me-1',
            }"
          />

          <div class="z-10 flex items-center gap-1">
            <UButton
              :to="show.url(item.id)"
              icon="i-lucide-eye"
              color="neutral"
              variant="ghost"
              size="sm"
            />

            <UButton
              v-if="item.manage"
              icon="i-lucide-pencil"
              color="neutral"
              variant="ghost"
              size="sm"
              @click="edit(item)"
            />

            <VideoDeleteModal :item="item" />
          </div>
        </div>
      </UPageCard>
    </UPageList>

    <VideoEditModal
      v-if="editingItem"
      v-model:open="isEditModalOpen"
      :video="editingItem"
      :media="editingItem.media"
      :playlists="editingItem.playlists"
      :transcodes="editingItem.transcodes"
    />
  </div>
</template>
