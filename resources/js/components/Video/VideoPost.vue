<script setup lang="ts">
import { show } from '@/actions/App/Web/Videos/Controllers/VideoController'
import TagItems from '@/components/Tag/TagItems.vue'
import type { Video } from '@/types'
import { computed } from 'vue'

interface Props {
  item: Video
}

const props = defineProps<Props>()

const tags = computed(() => props.item.tags?.slice(0, 5))
</script>

<template>
  <UBlogPost
    :title="item.title"
    :description="item.description"
    :image="item.thumb"
    :badge="item.timestamp"
    :date="item.released_at || item.published_at || item.created_at"
    :href="show.url(props.item.id)"
  >
    <template #description>
      <div v-html="item.description" />
      <TagItems :items="tags" />
    </template>
  </UBlogPost>
</template>
