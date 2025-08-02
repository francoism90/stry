<script setup lang="ts">
import SearchController from '@/actions/App/Web/Dashboard/Controllers/SearchController'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageFilters from '@/components/Ui/PageFilters.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoCollection from '@/layouts/Video/VideoCollection.vue'
import { Head } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

defineOptions({ layout: [DefaultLayout, VideoCollection] })

const filters = ref<NavigationMenuItem[]>([
  { label: 'All', to: SearchController.url({ query: { list: '' } }) },
  { label: 'Watched', to: SearchController.url({ query: { list: 'watching' } }) },
  { label: 'Newest', to: SearchController.url({ query: { list: 'newest' } }) },
])
</script>

<template>
  <Head title="Search" />

  <div>
    <PageFeature title="Search" />
    <PageFilters :items="filters" />
  </div>
</template>
