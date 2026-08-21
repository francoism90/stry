<script setup lang="ts">
import GroupCreateModal from '@/components/Groups/GroupCreateModal.vue'
import TagCreateModal from '@/components/Tags/TagCreateModal.vue'
import UserCreateModal from '@/components/Users/UserCreateModal.vue'
import VideoImportModal from '@/components/Videos/VideoImportModal.vue'
import { useAuth } from '@/composables/auth'
import type { DropdownMenuItem } from '@nuxt/ui'
import { computed, ref } from 'vue'

const { hasRole } = useAuth()

const isVideoModalOpen = ref(false)
const isTagModalOpen = ref(false)
const isCollectionModalOpen = ref(false)
const isUserModalOpen = ref(false)

const items = computed<DropdownMenuItem[]>(() => [
  {
    label: 'Collection',
    icon: 'i-lucide-folder',
    onClick: () => (isCollectionModalOpen.value = true),
  },
  ...(hasRole('super-admin')
    ? [
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
          label: 'User',
          icon: 'i-lucide-user-plus',
          onClick: () => (isUserModalOpen.value = true),
        },
      ]
    : []),
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

  <VideoImportModal
    v-model:open="isVideoModalOpen"
    :trigger="false"
  />

  <TagCreateModal
    v-model:open="isTagModalOpen"
    :trigger="false"
  />

  <GroupCreateModal
    v-model:open="isCollectionModalOpen"
    :trigger="false"
  />

  <UserCreateModal
    v-model:open="isUserModalOpen"
    :trigger="false"
  />
</template>
