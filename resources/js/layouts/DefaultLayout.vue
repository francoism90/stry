<script lang="ts" setup>
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

const items = ref<NavigationMenuItem[][]>([
  [
    {
      label: 'stry',
      to: '/',
      ui: {
        linkLabel: 'font-serif text-neutral-500 font-bold uppercase',
      },
    },
  ],
  [
    {
      label: 'Lists',
      icon: 'i-lucide-library',
      to: '/lists',
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
  ],
])
</script>

<template>
  <UApp>
    <div class="relative flex h-dvh min-h-dvh flex-col overflow-x-hidden">
      <header class="sticky inset-x-0 top-0 z-30 flex h-20 min-h-20 bg-default">
        <UContainer>
          <UNavigationMenu
            orientation="horizontal"
            variant="link"
            :items="items"
            :ui="{
              root: 'size-full items-center',
              list: 'inline-flex size-full items-center gap-4 sm:gap-6',
              link: 'px-0',
              linkTrailingIcon: 'hidden',
            }"
          />

          <div class="h-[1px] bg-gradient-to-r from-neutral-500/0 via-primary-500/75 to-neutral-500/0"></div>
        </UContainer>
      </header>

      <div class="flex-1 contain-inline-size contain-layout contain-paint contain-style">
        <slot />
      </div>
    </div>
  </UApp>
</template>
