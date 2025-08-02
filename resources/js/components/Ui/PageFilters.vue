<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  filters: NavigationMenuItem[]
}

const props = defineProps<Props>()

const items = ref<NavigationMenuItem[]>([
  {
    to: usePage().props.path,
    label: 'All',
    exact: true,
  },
  ...props.filters,
])
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
