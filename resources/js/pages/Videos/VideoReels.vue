<script setup lang="ts">
import VideoReelItem from '@/components/Videos/VideoReelItem.vue'
import { useReelFeed } from '@/composables/reels'
import AppLayout from '@/layouts/AppLayout.vue'
import type { VideoReelCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps<{
  items: VideoReelCollection
}>()

defineOptions({
  layout: [AppLayout],
})

const { active, muted, playing, activate, toggleMute, isLoaded, preload } = useReelFeed()

const itemBody = ref<HTMLElement>()
</script>

<template>
  <Head title="Reels" />

  <UDashboardPanel
    id="reels"
    :ui="{ body: 'relative max-w-none gap-0 bg-black p-0 sm:gap-0 sm:p-0' }"
  >
    <template #body>
      <div class="h-dvh w-full snap-y snap-mandatory overflow-y-auto overscroll-contain">
        <InfiniteScroll
          data="items"
          :items-element="() => itemBody"
          :buffer="800"
          only-next
        >
          <div
            ref="itemBody"
            class="mx-auto w-full max-w-md"
          >
            <VideoReelItem
              v-for="(item, index) in items?.data ?? []"
              :key="item.id"
              :item="item"
              :active="index === active"
              :loaded="isLoaded(index)"
              :preload="preload(index)"
              :muted="muted"
              :playing="playing"
              @visible="activate(index)"
              @toggle-mute="toggleMute"
              @blocked="muted = true"
            />
          </div>

          <template #loading>
            <div class="flex h-24 items-center justify-center">
              <UIcon
                name="i-lucide-loader-circle"
                class="size-6 animate-spin text-white"
              />
            </div>
          </template>
        </InfiniteScroll>
      </div>

      <UEmpty
        v-if="!items?.data?.length"
        icon="i-lucide-clapperboard"
        title="No reels yet"
        description="Reels appear here once they've been generated for your videos."
        class="absolute inset-0 m-auto h-fit"
      />
    </template>
  </UDashboardPanel>
</template>
