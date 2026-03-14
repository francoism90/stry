<script setup lang="ts">
import TagList from '@/components/Tags/TagList.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { TagCollection } from '@/types'
import { Head, InfiniteScroll, Link, router } from '@inertiajs/vue3'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

defineOptions({ layout: DefaultLayout })

const props = defineProps<{
  search: string
  items: TagCollection
}>()

const form = useForm('get', '/search/tags', {
  search: props.search,
})

watchDebounced(
  () => form.search,
  (value) => {
    router.visit(`/search/${encodeURIComponent(value)}/tags`, {
      preserveState: true,
      only: ['search', 'items'],
    })
  },
  { debounce: 350, maxWait: 1000 },
)
</script>

<template>
  <Head :title="search ? `Tags: ${search}` : 'Tags'" />

  <UDashboardPanel id="search-tags">
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
                placeholder="Search tags..."
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
            <TagList :items="items?.data" />
          </InfiniteScroll>
        </UPageBody>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
