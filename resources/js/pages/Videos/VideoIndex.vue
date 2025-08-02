<script setup lang="ts">
import { index } from '@/actions/App/Web/Videos/Controllers/VideoController'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageFilters from '@/components/Ui/PageFilters.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoCollection from '@/layouts/Video/VideoCollection.vue'
import { Head } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

defineOptions({ layout: [DefaultLayout, VideoCollection] })

const filters = ref<NavigationMenuItem[]>([
  { label: 'All', to: index.url({ query: { list: 'all' } }) },
  { label: 'Watched', to: index.url({ query: { list: 'watching' } }) },
  { label: 'Newest', to: index.url({ query: { list: 'newest' } }) },
])
</script>

<template>
  <Head title="Videos" />

  <div>
    <PageFeature title="Videos" />
    <PageFilters :items="filters" />
  </div>
</template>
