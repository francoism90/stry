<script lang="ts" setup>
import AppLogo from '@/components/Ui/AppLogo.vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

const items = ref<NavigationMenuItem[]>([
  {
    label: 'Videos',
    icon: 'i-lucide-gallery-vertical-end',
    to: '/videos',
  },
  {
    label: 'Tags',
    icon: 'i-lucide-library',
    to: '/tags',
  },
  {
    label: 'Profile',
    icon: 'i-lucide-user',
    to: '/profile',
  },
])
</script>

<template>
  <UHeader
    :toggle="false"
    class="sticky inset-x-0 top-0 z-30 flex h-16 min-h-16 border-b border-default bg-default"
  >
    <template #title>
      <AppLogo />
    </template>

    <template #right>
      <UNavigationMenu
        variant="link"
        :items="items"
        :ui="{
          list: 'flex items-center gap-4',
          link: 'px-0',
          linkLabel: 'hidden sm:inline',
        }"
      />
    </template>
  </UHeader>
</template>
