<script setup lang="ts">
import type { VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem, TabsItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: VideoCollection
  sort: string | null
  sorters: SelectMenuItem[]
}>()

const tabs: TabsItem[] = [
  {
    label: 'Recommended',
  },
  {
    label: 'Most Recent',
  },
  {
    label: 'Longest',
  },
]

const form = useForm('get', '', {
  sort: props.sort,
  page: 1,
})
</script>

<template>
  <Head title="Home" />

  <UPage>
    <UPageBody class="mt-4 space-y-4 pb-0">
      <UTabs
        :items="tabs"
        class="w-full gap-4"
        variant="link"
        :ui="{ trigger: 'grow' }"
      />

      <InfiniteScroll data="items">
        <UBlogPosts orientation="vertical">
          <UBlogPost
            v-for="(item, index) in items.data"
            v-bind="item"
            variant="naked"
            orientation="horizontal"
            :key="index"
            :image="item.thumb"
            :badge="item.timestamp"
            :date="item.released_at || item.published_at || item.created_at"
            :ui="{
              root: 'group gap-x-6 gap-y-4 md:grid md:grid-cols-2 lg:items-start',
              header: 'rounded-xs shadow-none',
              body: 'p-0 sm:p-0 lg:px-0',
              title: 'font-serif text-sm',
              description: 'text-sm',
            }"
          >
            <template #description>
              <p v-html="item.description" />
              <div
                v-if="item.tags?.length"
                class="mt-4 flex flex-wrap gap-2 overflow-x-auto"
              >
                <UButton
                  v-for="(tag, index) in item.tags"
                  :key="index"
                  :label="tag.name"
                  variant="soft"
                  size="xs"
                />
              </div>
            </template>
          </UBlogPost>
        </UBlogPosts>
      </InfiniteScroll>
    </UPageBody>
  </UPage>
</template>
