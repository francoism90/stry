<script setup lang="ts">
import { show } from '@/actions/App/Web/Tags/Controllers/TagController'
import type { Tag } from '@/types'

interface Props {
  item: Tag
}

const props = defineProps<Props>()
</script>

<template>
  <UPageCard
    :ui="{ container: 'p-2 sm:p-4' }"
    :title="item.name"
    :description="`${item.category} • ${item.videos} videos`"
    :href="show.url(props.item.id)"
  />
</template>
