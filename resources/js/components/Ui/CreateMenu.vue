<script setup lang="ts">
import GroupCreateModal from '@/components/Groups/GroupCreateModal.vue'
import TagCreateModal from '@/components/Tags/TagCreateModal.vue'
import VideoImportModal from '@/components/Videos/VideoImportModal.vue'
import type { DropdownMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

const isVideoModalOpen = ref(false)
const isTagModalOpen = ref(false)
const isCollectionModalOpen = ref(false)

const items = ref<DropdownMenuItem[]>([
  {
    label: 'Video',
    icon: 'i-lucide-file-video',
    onClick: () => (isVideoModalOpen.value = true),
  },
  {
    label: 'Tag',
    icon: 'i-lucide-tag-plus',
    onClick: () => (isTagModalOpen.value = true),
  },
  {
    label: 'Collection',
    icon: 'i-lucide-folder',
    onClick: () => (isCollectionModalOpen.value = true),
  },
])
</script>

<template>
  <UDropdownMenu
    :items="items"
    :portal="false"
  >
    <UButton
      icon="i-lucide-plus"
      label="Create"
      color="neutral"
      variant="outline"
      :ui="{
        base: 'me-2',
        label: 'hidden sm:inline-flex',
      }"
    />
  </UDropdownMenu>

  <VideoImportModal v-model:open="isVideoModalOpen">
    <template #default />
  </VideoImportModal>

  <TagCreateModal v-model:open="isTagModalOpen">
    <template #default />
  </TagCreateModal>

  <GroupCreateModal v-model:open="isCollectionModalOpen">
    <template #default />
  </GroupCreateModal>
</template>
