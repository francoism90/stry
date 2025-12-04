<script setup lang="ts">
import type { VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: VideoCollection
  sort: string | null
  sorters: SelectMenuItem[]
}>()

const form = useForm('get', '', {
  sort: props.sort,
  page: 1,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'sort'],
    reset: ['items'],
  })
</script>

<template>
  <Head title="Home" />

  <UPage>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-1.5">
      <USelect
        v-model="form.sort"
        :items="sorters"
        label-key="label"
        value-key="value"
        placeholder="Filter by"
        class="w-32 sm:w-36"
        @update:modelValue="onSubmit"
      />
    </div>

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
            root: 'group overflow-visible transition-all duration-300 md:grid md:grid-cols-2',
            image: 'rounded-xs',
          }"
        >
          <template #description>
            <div v-html="item.description" />
            <div
              v-if="item.tags?.length"
              class="mt-4 flex flex-wrap gap-2 overflow-x-auto"
            >
              <ULink
                v-for="(tag, index) in item.tags"
                :key="index"
              >
                <UBadge
                  :label="tag.name"
                  variant="soft"
                />
              </ULink>
            </div>
          </template>
        </UBlogPost>
      </UBlogPosts>
    </InfiniteScroll>
  </UPage>
</template>
