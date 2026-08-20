<script setup lang="ts">
import GroupList from '@/components/Groups/GroupList.vue'
import TagList from '@/components/Tags/TagList.vue'
import AppFooter from '@/components/Ui/AppFooter.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import type { Group, Tag, Video } from '@/types'
import { Head, router, useForm } from '@inertiajs/vue3'
import { watchDebounced } from '@vueuse/core'

const props = defineProps<{
  search: string
  videos: Video[]
  tags: Tag[]
  collections: Group[]
}>()

const form = useForm({
  search: props.search,
})

watchDebounced(
  () => form.search,
  (value) => {
    router.visit(`/search/${encodeURIComponent(value)}`, {
      preserveState: true,
      only: ['search', 'videos', 'tags', 'collections'],
    })
  },
  { debounce: 350, maxWait: 1000 },
)
</script>

<template>
  <Head :title="search ? `Search: ${search}` : 'Search'" />

  <UDashboardPanel id="search">
    <template #header>
      <AppHeader />

      <UDashboardToolbar
        :ui="{
          root: 'min-h-16 border-b border-default',
          left: 'flex-1',
        }"
      >
        <template #left>
          <UFormField
            :error="form.errors.search"
            class="mx-auto w-full max-w-2xl"
          >
            <UInput
              v-model="form.search"
              :model-modifiers="{ string: true, trim: true }"
              variant="soft"
              size="xl"
              color="neutral"
              class="w-full"
              placeholder="Search videos, tags, collections..."
              icon="i-lucide-search"
              autofocus
            />
          </UFormField>
        </template>
      </UDashboardToolbar>
    </template>

    <template #body>
      <UPage>
        <UPageBody>
          <!-- Empty state -->
          <div
            v-if="!search"
            class="flex flex-col items-center justify-center gap-3 py-24 text-center"
          >
            <UIcon
              name="i-lucide-search"
              class="size-10 text-muted"
            />
            <p class="font-semibold">Search for something</p>
            <p class="text-sm text-muted">Enter a term above to find videos, tags, and collections.</p>
          </div>

          <!-- Results -->
          <template v-else>
            <p class="text-sm text-muted">
              Results for <span class="font-semibold text-default">{{ search }}</span>
            </p>

            <!-- No results at all -->
            <div
              v-if="!videos.length && !tags.length && !collections.length"
              class="flex flex-col items-center justify-center gap-3 py-24 text-center"
            >
              <UIcon
                name="i-lucide-search-x"
                class="size-10 text-muted"
              />
              <p class="font-semibold">No results found</p>
              <p class="text-sm text-muted">Try searching with different keywords.</p>
            </div>

            <!-- Videos -->
            <section
              v-if="videos.length"
              class="flex flex-col gap-4"
            >
              <div class="flex items-center justify-between">
                <p class="font-semibold">Videos</p>

                <UButton
                  variant="link"
                  size="sm"
                  trailing-icon="i-lucide-arrow-right"
                  :to="`/search/${encodeURIComponent(search)}/videos`"
                >
                  See all videos
                </UButton>
              </div>

              <VideoList :items="videos" />
            </section>

            <USeparator v-if="videos.length && (tags.length || collections.length)" />

            <!-- Tags -->
            <section
              v-if="tags.length"
              class="flex flex-col gap-4"
            >
              <div class="flex items-center justify-between">
                <p class="font-semibold">Tags</p>

                <UButton
                  variant="link"
                  size="sm"
                  trailing-icon="i-lucide-arrow-right"
                  :to="`/search/${encodeURIComponent(search)}/tags`"
                >
                  See all tags
                </UButton>
              </div>

              <TagList :items="tags" />
            </section>

            <USeparator v-if="tags.length && collections.length" />

            <!-- Collections -->
            <section
              v-if="collections.length"
              class="flex flex-col gap-4"
            >
              <div class="flex items-center justify-between">
                <p class="font-semibold">Collections</p>

                <UButton
                  variant="link"
                  size="sm"
                  trailing-icon="i-lucide-arrow-right"
                  :to="`/search/${encodeURIComponent(search)}/collections`"
                >
                  See all collections
                </UButton>
              </div>

              <GroupList :items="collections" />
            </section>
          </template>
        </UPageBody>
      </UPage>
    </template>

    <template #footer>
      <AppFooter />
    </template>
  </UDashboardPanel>
</template>
