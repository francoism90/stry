<script setup lang="ts">
import { index } from '@/actions/App/Web/Tags/Controllers/TagController'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageFilters from '@/components/Ui/PageFilters.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import TagCollection from '@/layouts/Tag/TagCollection.vue'
import { Head } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

defineOptions({ layout: [DefaultLayout, TagCollection] })

const filters = ref<NavigationMenuItem[]>([
  { label: 'All', to: index.url({ query: { list: 'all' } }) },
  { label: 'Watched', to: index.url({ query: { list: 'watching' } }) },
  { label: 'Newest', to: index.url({ query: { list: 'newest' } }) },
])
</script>

<template>
  <Head title="Tags" />

  <div>
    <PageFeature title="Tags" />
    <PageFilters :items="filters" />
  </div>
</template>
