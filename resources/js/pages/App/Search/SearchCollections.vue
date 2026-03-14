<script setup lang="ts">
import GroupList from '@/components/Groups/GroupList.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { GroupCollection } from '@/types'
import { Head, InfiniteScroll, Link, router } from '@inertiajs/vue3'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

defineOptions({ layout: DefaultLayout })

const props = defineProps<{
  search: string
  items: GroupCollection
}>()

const form = useForm('get', '/search/collections', {
  search: props.search,
})

watchDebounced(
  () => form.search,
  (value) => {
    router.visit(`/search/${encodeURIComponent(value)}/collections`, {
      preserveState: true,
      only: ['search', 'items'],
    })
  },
  { debounce: 350, maxWait: 1000 },
)
</script>

<template>
  <Head :title="search ? `Collections: ${search}` : 'Collections'" />

  <UDashboardPanel id="search-collections">
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
                placeholder="Search collections..."
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
            <GroupList :items="items?.data" />
          </InfiniteScroll>
        </UPageBody>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
