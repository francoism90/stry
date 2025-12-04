<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const links: NavigationMenuItem[] = [
  {
    label: 'Videos',
    icon: 'i-lucide-videotape',
    to: '/admin/videos',
  },
  {
    label: 'Tags',
    icon: 'i-lucide-tags',
    to: '/admin/tags',
  },
]
</script>

<template>
  <div class="fixed top-2 left-1/2 z-10 mx-auto -translate-x-1/2 transform sm:top-4">
    <UNavigationMenu
      :items="links"
      variant="link"
      color="neutral"
      class="rounded-full border border-muted/50 bg-muted/80 px-2 shadow-lg shadow-neutral-950/5 backdrop-blur-sm sm:px-4"
      :ui="{
        link: 'px-2 py-1',
        linkLeadingIcon: 'hidden',
      }"
    >
      <template #list-trailing>
        <ColorModeButton />
      </template>
    </UNavigationMenu>
  </div>
</template>
