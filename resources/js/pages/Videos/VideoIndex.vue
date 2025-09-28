<script setup lang="ts">
import { index } from '@/actions/App/Web/Videos/Controllers/VideoController'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageFilters from '@/components/Ui/PageFilters.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoCollection from '@/layouts/Video/VideoCollection.vue'
import { Head } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  list: string | null
}

defineOptions({ layout: [DefaultLayout, VideoCollection] })

const props = defineProps<Props>()

const filters = ref<NavigationMenuItem[]>([
  { label: 'All', to: index.url(), active: props.list === null },
  { label: 'Watched', to: index.url({ query: { list: 'watching' } }), active: props.list === 'watching' },
  { label: 'Newest', to: index.url({ query: { list: 'newest' } }), active: props.list === 'newest' },
])
</script>

<template>
  <Head title="Videos" />

  <PageSection>
    <PageFeature title="Videos" />
    <PageFilters :filters />
  </PageSection>
</template>
