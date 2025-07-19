<script setup lang="ts">
import VideoCard from '@/components/Video/VideoCard.vue'
import type { Video } from '@/types'
import type { NavigationMenuItem } from '@nuxt/ui'

interface Props {
  label: string
  items?: Video[]
  actions?: NavigationMenuItem[]
}

defineProps<Props>()
</script>

<template>
  <section class="flex flex-col">
    <div class="flex items-center justify-between gap-2">
      <h2 class="font-serif text-lg font-semibold tracking-tight">{{ label }}</h2>

      <UNavigationMenu
        v-if="actions && actions.length"
        variant="link"
        :items="actions"
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
  </section>
</template>
