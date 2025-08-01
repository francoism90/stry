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
    variant="link"
    highlight
    highlight-color="primary"
    :ui="{
      root: 'size-full items-center overflow-x-auto border-b border-default',
      list: 'gap-4',
      link: 'px-0 text-neutral-400 after:inset-x-0',
    }"
  />
</template>
