<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

interface Props {
  tabs: NavigationMenuItem[] | undefined
}

defineProps<Props>()
</script>

<template>
  <UNavigationMenu
    :items="tabs"
    variant="link"
    highlight
    highlight-color="primary"
    :ui="{
      root: 'mb-4 size-full items-center overflow-x-auto border-b border-muted',
      list: 'gap-4',
      link: 'px-0 text-neutral-400 after:inset-x-0',
    }"
  />
</template>
