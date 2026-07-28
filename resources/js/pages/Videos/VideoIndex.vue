<script setup lang="ts">
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import FilterBar from '@/components/Ui/FilterBar.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import type { VideoCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'

defineProps<{
  items: VideoCollection
}>()

defineOptions({
  layout: {
    id: 'videos',
    title: 'Forgot password',
    description: 'Enter your email to receive a password reset link',
  },
})
</script>

<template>
  <UDashboardPanel id="videos">
    <template #header>
      <AppHeader>
        <FilterBar placeholder="Search videos" />
      </AppHeader>
    </template>

    <template #body>
      <UPage>
        <InfiniteScroll
          data="items"
          items-element="#infinite-items"
          :buffer="200"
        >
          <VideoList
            id="infinite-items"
            :items="items?.data"
          />
        </InfiniteScroll>
      </UPage>
    </template>

    <template #footer>
      <AppFooter />
    </template>
  </UDashboardPanel>
</template>
