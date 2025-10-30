<script setup lang="ts">
import { show } from '@/actions/App/Web/Videos/Controllers/VideoController'
import type { Video } from '@/types'
import { computed } from 'vue'

interface Props {
  item: Video
}

const props = defineProps<Props>()

const tags = computed(() => props.item.tags?.slice(0, 4))
</script>

<template>
  <UBlogPost
    :to="show(props.item.id)"
    :title="item.name"
    :description="item.summary"
    :image="item.thumb"
    :date="item.published_at ?? item.created_at"
    :badge="item.timestamp"
  >
    <template #description>
      <p>{{ item.summary }}</p>

      <div
        v-if="tags"
        class="mt-4 flex flex-wrap gap-2"
      >
        <UBadge
          v-for="(tag, index) in item.tags"
          :key="index"
          :label="tag.name"
          variant="soft"
        />
      </div>
    </template>
  </UBlogPost>
</template>
