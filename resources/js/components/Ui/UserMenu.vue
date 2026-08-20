<script setup lang="ts">
import SettingsModal from '@/components/Settings/SettingsModal.vue'
import { useAuth } from '@/composables/auth'
import { router } from '@inertiajs/vue3'
import type { DropdownMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

const { user } = useAuth()

const isSettingsOpen = ref(false)
const settingsSection = ref('account')

const items = ref<DropdownMenuItem[][]>([
  [
    {
      label: 'Settings',
      icon: 'i-lucide-settings',
      onClick: () => (isSettingsOpen.value = true),
    },
    {
      label: 'Profiles',
      icon: 'i-lucide-users',
      to: '/profiles',
    },
  ],
  [
    {
      label: 'Log out',
      icon: 'i-lucide-log-out',
      onClick: () => router.post('/logout'),
    },
  ],
])
</script>

<template>
  <UDropdownMenu
    :items="items"
    :content="{ align: 'end', collisionPadding: 12 }"
    :portal="false"
  >
    <UAvatar
      :src="user?.avatar ?? undefined"
      :alt="user?.name ?? 'User'"
      :ui="{
        root: 'cursor-pointer p-1',
        fallback: 'flex size-full items-center justify-center',
      }"
      size="sm"
    />
  </UDropdownMenu>

  <SettingsModal
    v-model:open="isSettingsOpen"
    v-model:section="settingsSection"
  />
</template>
