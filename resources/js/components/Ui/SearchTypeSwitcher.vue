<script setup lang="ts">
import { index as collectionsIndex } from '@/routes/collections'
import { index as tagsIndex } from '@/routes/tags'
import { index as videosIndex } from '@/routes/videos'
import type { QueryValue } from '@/types'
import type { NavigationMenuItem } from '@nuxt/ui'
import { computed } from 'vue'

const props = defineProps<{
  id?: string
  query?: QueryValue
}>()

const indexIds = ['videos.index', 'tags.index', 'collections.index']

const isVisible = computed(() => Boolean(props.query) && indexIds.includes(props.id ?? ''))

const items = computed<NavigationMenuItem[]>(() => [
  {
    label: 'Videos',
    icon: 'i-lucide-video',
    to: videosIndex.url({ query: { query: props.query } }),
    active: props.id === 'videos.index',
  },
  {
    label: 'Tags',
    icon: 'i-lucide-tags',
    to: tagsIndex.url({ query: { query: props.query } }),
    active: props.id === 'tags.index',
  },
  {
    label: 'Collections',
    icon: 'i-lucide-folders',
    to: collectionsIndex.url({ query: { query: props.query } }),
    active: props.id === 'collections.index',
  },
])
</script>

<template>
  <UNavigationMenu
    v-if="isVisible"
    :items="items"
    orientation="horizontal"
    class="border-b border-default px-4 sm:px-6 lg:px-8"
  />
</template>
