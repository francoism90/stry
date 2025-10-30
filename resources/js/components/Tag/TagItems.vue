<script setup lang="ts">
import { show } from '@/actions/App/Web/Tags/Controllers/TagController'
import type { Tag } from '@/types'

interface Props {
  items: Tag[] | undefined
}

defineProps<Props>()
</script>

<template>
  <div
    v-if="items?.length"
    class="mt-4 flex flex-wrap gap-2"
  >
    <ULink
      v-for="(item, index) in items"
      :key="index"
      :to="show.url(item.id)"
    >
      <UBadge
        :label="item.name"
        variant="soft"
      />
    </ULink>
  </div>
</template>
