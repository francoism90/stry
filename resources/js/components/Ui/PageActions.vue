<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

interface Props {
  actions: NavigationMenuItem[] | undefined
}

defineProps<Props>()
</script>

<template>
  <UNavigationMenu
    :items="actions"
    :ui="{
      root: 'size-full items-center overflow-x-auto',
      list: 'inline-flex size-full items-center gap-2',
      link: 'rounded-full bg-neutral-800/40',
      linkLeadingIcon: 'size-3.5',
      linkLabel: 'text-xs text-neutral-400',
    }"
  />
</template>
