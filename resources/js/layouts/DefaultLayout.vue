<script lang="ts" setup>
import AppLogo from '@/components/Ui/AppLogo.vue'
import ServiceWorker from '@/components/Ui/ServiceWorker.vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import { computed } from 'vue'

const items = computed<NavigationMenuItem[]>(() => [
  {
    label: 'Videos',
    icon: 'i-lucide-gallery-vertical-end',
    to: '/videos',
    ui: {
      linkLabel: 'sr-only',
    },
  },
  {
    label: 'Lists',
    icon: 'i-lucide-library',
    to: '/tags',
    ui: {
      linkLabel: 'sr-only',
    },
  },
  {
    label: 'Search',
    icon: 'i-lucide-search',
    to: '/search',
    ui: {
      linkLabel: 'sr-only',
    },
  },
  {
    label: 'Profile',
    icon: 'i-lucide-user',
    to: '/profile',
    ui: {
      linkLabel: 'sr-only',
    },
  },
])
</script>

<template>
  <UApp>
    <ServiceWorker />

    <div class="relative flex h-dvh min-h-dvh flex-col overflow-x-hidden">
      <UHeader
        :toggle="false"
        class="sticky inset-x-0 top-0 z-30 flex h-18 min-h-18 border-b border-default bg-default"
      >
        <template #title>
          <AppLogo />
        </template>

        <template #right>
          <UNavigationMenu
            variant="link"
            :items="items"
            :ui="{
              list: 'inline-flex items-center gap-4',
              link: 'px-0',
              linkTrailingIcon: 'hidden',
            }"
          />
        </template>
      </UHeader>

      <UMain class="flex-1 contain-inline-size contain-layout contain-paint contain-style">
        <slot />
      </UMain>
    </div>
  </UApp>
</template>
