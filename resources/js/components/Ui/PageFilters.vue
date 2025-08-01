<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

interface Props {
  filters: NavigationMenuItem[]
}

defineProps<Props>()
</script>

<template>
  <UNavigationMenu
    :items="filters"
    :ui="{
      root: 'size-full items-center overflow-x-auto',
      list: 'gap-1',
      link: 'text-xs',
    }"
  />
</template>
