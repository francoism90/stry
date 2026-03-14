<script setup lang="ts">
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { VideoCollection } from '@/types'
import { Head, InfiniteScroll, Link, router } from '@inertiajs/vue3'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

defineOptions({ layout: DefaultLayout })

const props = defineProps<{
  search: string
  items: VideoCollection
}>()

const form = useForm('get', '/search/videos', {
  search: props.search,
})

watchDebounced(
  () => form.search,
  (value) => {
    router.visit(`/search/${encodeURIComponent(value)}/videos`, {
      preserveState: true,
      only: ['search', 'items'],
    })
  },
  { debounce: 350, maxWait: 1000 },
)
</script>

<template>
  <Head :title="search ? `Videos: ${search}` : 'Videos'" />

  <UDashboardPanel id="search-videos">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage>
        <UDashboardToolbar>
          <template #left>
            <UFormField :error="form.errors.search">
              <UInput
                v-model="form.search"
                :model-modifiers="{ string: true, trim: true }"
                color="neutral"
                class="min-w-72"
                placeholder="Search videos..."
                icon="i-lucide-search"
                autofocus
              />
            </UFormField>
          </template>

          <template #right>
            <Link
              :href="`/search/${encodeURIComponent(search)}`"
              class="text-muted text-sm hover:underline"
            >
              All results
            </Link>
          </template>
        </UDashboardToolbar>

        <UPageBody>
          <InfiniteScroll
            data="items"
            :buffer="200"
          >
            <VideoList :items="items?.data" />
          </InfiniteScroll>
        </UPageBody>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
