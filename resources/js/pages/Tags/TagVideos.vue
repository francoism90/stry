<script setup lang="ts">
import { index } from '@/actions/App/Web/Tags/Controllers/TagVideoController'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageFilters from '@/components/Ui/PageFilters.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoCollection from '@/layouts/Video/VideoCollection.vue'
import type { Tag } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  tag: Tag
}

defineOptions({ layout: [DefaultLayout, VideoCollection] })

const props = defineProps<Props>()

const filters = ref<NavigationMenuItem[]>([
  { label: 'All', to: index.url(props.tag.id), exact: true },
  { label: 'Ordered', to: index.url(props.tag.id, { query: { sort: 'ordered' } }) },
  { label: 'Longest', to: index.url(props.tag.id, { query: { sort: 'longest' } }) },
  { label: 'Shortest', to: index.url(props.tag.id, { query: { sort: 'shortest' } }) },
])
</script>

<template>
  <Head :title="tag.name" />

  <PageSection>
    <PageFeature :title="tag.name">
      <dl class="list text-xs font-light tracking-tight text-neutral-100">
        <dt class="sr-only">Type</dt>
        <dd>{{ tag.type }}</dd>
        <dt class="sr-only">Videos</dt>
        <dd>{{ tag.videos }} videos</dd>
      </dl>
    </PageFeature>
    <PageFilters :filters />
  </PageSection>
</template>
