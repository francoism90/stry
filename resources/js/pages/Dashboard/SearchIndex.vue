<script setup lang="ts">
import SearchController from '@/actions/App/Web/Dashboard/Controllers/SearchController'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageFilters from '@/components/Ui/PageFilters.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoCollection from '@/layouts/Video/VideoCollection.vue'
import type { Videos } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { ref } from 'vue'

interface Props {
  search?: string | null
  sort?: string | null
  items?: Videos
}

defineOptions({ layout: [DefaultLayout, VideoCollection] })

const props = defineProps<Props>()

const input = ref(props.search || '')

const filters = ref<NavigationMenuItem[]>([
  { label: 'Relevant', to: SearchController.url({ mergeQuery: { sort: null } }), exact: true },
  { label: 'Ordered', to: SearchController.url({ mergeQuery: { sort: 'ordered' } }) },
  { label: 'Longest', to: SearchController.url({ mergeQuery: { sort: 'longest' } }) },
  { label: 'Shortest', to: SearchController.url({ mergeQuery: { sort: 'shortest' } }) },
])

watchDebounced(input, () => router.get(SearchController.url(), { search: input.value }), {
  debounce: 350,
  maxWait: 1000,
})
</script>

<template>
  <Head title="Search" />

  <PageSection>
    <PageFeature title="Search" />

    <UFormField class="pt-2">
      <UInput
        v-model.trim="input"
        placeholder="Title, description, tags..."
        class="w-full"
        autofocus
      />
    </UFormField>

    <PageFilters
      v-if="items?.data?.length"
      :filters="filters"
    />
  </PageSection>
</template>
