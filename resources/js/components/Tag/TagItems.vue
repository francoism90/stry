<script setup lang="ts">
import { show } from '@/actions/App/Web/Tags/Controllers/TagController'
import type { Tag } from '@/types'

interface Props {
  items: Tag[] | null | undefined
}

defineProps<Props>()
</script>

<template>
  <div
    v-if="items?.length"
    class="flex flex-wrap gap-2 overflow-x-auto"
  >
    <ULink
      v-for="item in items"
      :key="item.id"
      :to="show.url(item.id)"
    >
      <UBadge
        :label="item.name"
        variant="soft"
      />
    </ULink>
  </div>
</template>
