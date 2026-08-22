<script setup lang="ts">
import type { Video } from '@/types'

defineProps<{
  items?: Video[] | undefined
}>()
</script>

<template>
  <div class="flex flex-col gap-3">
    <div
      v-if="items === undefined"
      class="flex flex-col gap-2"
    >
      <USkeleton
        v-for="i in 3"
        :key="i"
        class="h-14 w-full rounded-md"
      />
    </div>

    <UPageList
      v-else-if="items.length"
      divide
    >
      <UPageCard
        v-for="item in items"
        :key="item.id"
        variant="naked"
        class="py-3 first:pt-0 last:pb-0"
      >
        <UUser
          :name="item.title"
          :description="[item.created_at, item.filesize, item.codec?.toUpperCase()].filter(Boolean).join(' · ')"
          :avatar="{
            src: item.thumb ?? undefined,
            alt: item.title,
            loading: 'lazy',
            decoding: 'async',
            class: 'rounded-sm size-10 me-1',
          }"
        />
      </UPageCard>
    </UPageList>
  </div>
</template>
