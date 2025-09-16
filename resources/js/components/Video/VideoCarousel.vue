<script setup lang="ts">
import VideoCard from '@/components/Video/VideoCard.vue'
import type { Video } from '@/types'
import type { NavigationMenuItem } from '@nuxt/ui'

interface Props {
  label?: string
  items?: Video[] | null
  actions?: NavigationMenuItem[]
}

defineProps<Props>()
</script>

<template>
  <div
    v-if="items?.length"
    class="flex flex-col gap-1"
  >
    <div class="flex items-center justify-between gap-2">
      <h2 class="py-1.5 font-serif text-lg font-semibold tracking-tight">
        {{ label }}
      </h2>

      <UNavigationMenu
        :items="actions"
        variant="link"
        :ui="{
          root: 'items-center gap-2',
          list: 'inline-flex size-full items-center gap-4',
          link: 'px-0',
        }"
      />
    </div>

    <UCarousel
      v-slot="{ item }"
      :items="items"
      :ui="{
        item: 'basis-xs md:basis-2/4 lg:basis-sm',
      }"
    >
      <VideoCard :item />
    </UCarousel>
  </div>
</template>
