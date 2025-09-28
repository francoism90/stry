<script setup lang="ts">
import { index } from '@/actions/App/Web/Tags/Controllers/TagController'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageFilters from '@/components/Ui/PageFilters.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import TagCollection from '@/layouts/Tag/TagCollection.vue'
import { Head } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

defineOptions({ layout: [DefaultLayout, TagCollection] })

interface Props {
  type: string | null
  types: NavigationMenuItem[]
}

const props = defineProps<Props>()

const filters = ref<NavigationMenuItem[]>([
  { label: 'All', to: index.url(), active: props.type === null },
  ...props.types.map((type) => ({
    label: type.label,
    to: index.url({ query: { type: type.value } }),
    active: props.type === type.value,
  })),
])
</script>

<template>
  <Head title="Lists" />

  <PageSection>
    <PageFeature title="Lists" />
    <PageFilters :filters />
  </PageSection>
</template>
