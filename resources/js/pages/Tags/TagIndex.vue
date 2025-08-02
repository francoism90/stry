<script setup lang="ts">
import { index } from '@/actions/App/Web/Tags/Controllers/TagController'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageFilters from '@/components/Ui/PageFilters.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import TagCollection from '@/layouts/Tag/TagCollection.vue'
import { Head } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { computed } from 'vue'

interface Props {
  type: string
  types: NavigationMenuItem[]
}

defineOptions({ layout: [DefaultLayout, TagCollection] })

const props = defineProps<Props>()

const filters = computed(() =>
  props.types.map((type) => ({
    label: type.label,
    to: index.url({ query: { type: type.value } }),
  })),
)
</script>

<template>
  <Head title="Lists" />

  <div>
    <PageFeature title="Lists" />
    <PageFilters :filters />
  </div>
</template>
