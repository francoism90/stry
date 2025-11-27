<script setup lang="ts">
import { show } from '@/actions/App/Web/Videos/Controllers/VideoController'
import TagItems from '@/components/Tag/TagItems.vue'
import type { Video } from '@/types'

interface Props {
  item: Video
}

const props = defineProps<Props>()
</script>

<template>
  <UBlogPost
    :title="item.title"
    :description="item.description"
    :image="item.thumb"
    :badge="item.timestamp"
    :date="item.released_at || item.published_at || item.created_at"
    :to="show.url(props.item.id)"
  >
    <template #description>
      <div v-html="item.description" />

      <TagItems
        class="mt-4"
        :items="item.tags"
      />
    </template>
  </UBlogPost>
</template>
