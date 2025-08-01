<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

interface Props {
  items: NavigationMenuItem[]
}

defineProps<Props>()
</script>

<template>
  <UNavigationMenu
    :items
    :ui="{
      root: 'size-full items-center overflow-x-auto',
      list: 'gap-0.5',
      link: 'text-xs',
    }"
  />
</template>
