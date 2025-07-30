<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

interface Props {
  items: NavigationMenuItem[][]
}

defineProps<Props>()
</script>

<template>
  <UNavigationMenu
    :items="items"
    orientation="horizontal"
    variant="link"
    highlight
    highlight-color="primary"
    :ui="{
      root: 'border-b-neutral size-full items-center overflow-x-auto border-b border-default',
      list: 'gap-4',
      link: 'px-0 after:inset-x-0',
      linkLabel: 'text-sm text-neutral-400',
    }"
  />
</template>
