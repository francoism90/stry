<script setup lang="ts">
import { show } from '@/actions/App/Web/Tags/Controllers/TagController'
import type { Tag } from '@/types'

interface Props {
  tags?: Tag[] | null
}

defineProps<Props>()
</script>

<template>
  <div
    v-if="tags?.length"
    class="flex flex-wrap gap-1 py-2"
  >
    <ULink
      v-for="item in tags"
      :key="item.id"
      :to="show.url(item.id)"
      class="block"
    >
      <UBadge
        :label="item.name"
        variant="soft"
      />
    </ULink>
  </div>
</template>
