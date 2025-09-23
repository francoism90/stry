<script setup lang="ts">
import { edit, show } from '@/actions/App/Web/Tags/Controllers/TagController'
import PageActions from '@/components/Ui/PageActions.vue'
import PageColumns from '@/components/Ui/PageColumns.vue'
import PageDetails from '@/components/Ui/PageDetails.vue'
import PageFilters from '@/components/Ui/PageFilters.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoCollection from '@/layouts/Video/VideoCollection.vue'
import type { Tag } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  tag: Tag
}

defineOptions({ layout: [DefaultLayout, VideoCollection] })

const props = defineProps<Props>()

const actions = ref<NavigationMenuItem[]>([{ label: 'Edit', icon: 'i-lucide-clipboard-pen', to: edit.url(props.tag.id) }])

const details = ref<NavigationMenuItem[]>([
  { label: 'Type', value: props.tag.category },
  { label: 'Videos', value: props.tag.videos + ' videos' },
])

const filters = ref<NavigationMenuItem[]>([
  { label: 'All', to: show.url(props.tag.id), exact: true },
  { label: 'Ordered', to: show.url(props.tag.id, { query: { sort: 'ordered' } }) },
  { label: 'Longest', to: show.url(props.tag.id, { query: { sort: 'longest' } }) },
  { label: 'Shortest', to: show.url(props.tag.id, { query: { sort: 'shortest' } }) },
])

useEcho<Tag>(`tags.${props.tag.id}`, '.tag.updated', () => router.reload({ only: ['tag'] }))
</script>

<template>
  <Head :title="tag.name" />

  <PageSection>
    <PageColumns>
      <template #left>
        <UPageFeature :title="tag.name" />
        <PageDetails :details />
      </template>

      <template #right>
        <PageActions :actions />
      </template>
    </PageColumns>

    <PageFilters :filters />
  </PageSection>
</template>
