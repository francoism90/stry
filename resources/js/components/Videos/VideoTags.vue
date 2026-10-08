<script setup lang="ts">
import { show } from '@/actions/Modules/Web/Tags/Controllers/TagController'
import { tagIcon } from '@/composables/tags'
import type { Tag } from '@/types'

withDefaults(
  defineProps<{
    items: Tag[] | undefined
    variant?: 'inline' | 'chips'
  }>(),
  {
    variant: 'inline',
  },
)
</script>

<template>
  <div
    v-if="items?.length && variant === 'chips'"
    class="flex flex-wrap items-center gap-1.5"
  >
    <UButton
      v-for="item in items"
      :key="item.id"
      :to="show.url(item.id)"
      :label="item.name"
      :icon="tagIcon(item.type)"
      color="neutral"
      variant="outline"
      size="xs"
      class="rounded-full bg-(--glass) ps-2 pe-3 text-default ring-(--glass-border) backdrop-blur-md backdrop-saturate-140 hover:bg-(--glass-strong) hover:text-highlighted"
    />
  </div>

  <div
    v-else-if="items?.length"
    class="flex flex-wrap items-center gap-x-1 gap-y-0.5 text-sm text-muted"
  >
    <template
      v-for="(item, index) in items"
      :key="item.id"
    >
      <span
        v-if="index > 0"
        class="opacity-40 select-none"
      >
        ·
      </span>

      <ULink
        :to="show.url(item.id)"
        active-class="text-neutral"
        class="z-10 transition-colors hover:text-highlighted"
      >
        {{ item.name }}
      </ULink>
    </template>
  </div>
</template>
