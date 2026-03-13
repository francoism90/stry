<script setup lang="ts">
import VideoController from '@/actions/App/Web/Videos/Controllers/VideoController'
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { Video } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import { computed } from 'vue'

const props = defineProps<{
  video: Video
}>()

const items: NavigationMenuItem[] = [
  {
    label: 'General',
    icon: 'i-lucide-film',
    to: VideoController.edit.url(props.video.id),
    exact: true,
  },
]

const meta = computed(() => [props.video.timestamp, props.video.filesize, props.video.user?.name].filter(Boolean))

useEcho<Video>(`videos.${props.video.id}`, '.video.updated', () => router.reload({ only: ['video'] }))
useEcho<Video>(`videos.${props.video.id}`, '.playlist.created', () => router.reload({ only: ['playlist'] }))
useEcho<Video>(`videos.${props.video.id}`, '.playlist.updated', () => router.reload({ only: ['playlist'] }))
</script>

<template>
  <Head :title="video.title" />

  <UDashboardPanel id="video">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage>
        <UPageHeader :title="video.title">
          <template #description>
            <div class="dot-separated text-muted flex flex-wrap items-center text-sm">
              <span
                v-for="(item, index) in meta"
                :key="index"
              >
                {{ item }}
              </span>
            </div>
          </template>
        </UPageHeader>

        <UNavigationMenu
          :items="items"
          variant="link"
          highlight
          :ui="{
            root: 'border-default mt-2 w-full flex-1 border-b',
          }"
        />

        <div class="mx-auto w-full max-w-6xl p-4 sm:p-6">
          <slot />
        </div>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
