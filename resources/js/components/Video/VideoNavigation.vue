<script setup lang="ts">
import { edit } from '@/actions/App/Web/Videos/Controllers/VideoController'
import { useAuth } from '@/composables/auth'
import type { Video } from '@/types'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  item: Video
}

const props = defineProps<Props>()
const { hasPermission } = useAuth()

const items = ref<NavigationMenuItem[][]>([
  [
    {
      label: '0',
      icon: 'i-lucide-thumbs-up',
      to: '/search',
    },
    {
      label: 'Edit',
      icon: 'i-lucide-clipboard-pen',
      to: edit.url(props.item.id),
      ui: {
        item: hasPermission('edit videos') ? undefined : 'hidden',
      },
    },
    {
      label: 'Save',
      icon: 'i-lucide-bookmark',
      to: '/lists',
    },
  ],
])
</script>

<template>
  <UNavigationMenu
    orientation="horizontal"
    :items="items"
    :ui="{
      root: 'size-full items-center overflow-x-auto',
      list: 'inline-flex size-full items-center gap-2',
      link: 'rounded-full bg-neutral-800/40',
      linkLeadingIcon: 'size-3.5',
      linkLabel: 'text-xs text-neutral-400',
    }"
  />
</template>
