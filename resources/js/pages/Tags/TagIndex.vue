<script setup lang="ts">
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageFilters from '@/components/Ui/PageFilters.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import TagCollection from '@/layouts/Tag/TagCollection.vue'
import { Head } from '@inertiajs/vue3'
import type { RadioGroupItem } from '@nuxt/ui'

defineOptions({ layout: [DefaultLayout, TagCollection] })

interface Props {
  type: string | undefined
  types: RadioGroupItem[]
}

defineProps<Props>()
</script>

<template>
  <Head title="Lists" />

  <PageSection>
    <PageFeature title="Lists" />
    <PageFilters
      :items="types"
      :active="type"
    />
  </PageSection>
</template>
