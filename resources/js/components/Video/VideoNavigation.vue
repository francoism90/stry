<script setup lang="ts">
import { edit } from '@/actions/App/Web/Videos/Controllers/VideoController'
import type { Video } from '@/types'
import { usePage } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { computed, ref } from 'vue'

interface Props {
  item: Video
}

const props = defineProps<Props>()

const page = usePage()

const link = computed(() => edit.url(props.item.id))
const editable = computed(() => page.props.auth.user?.permissions?.includes('edit videos') ?? false)

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
      disabled: !editable.value,
      to: link,
      ui: {
        item: editable.value ? undefined : 'hidden',
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
