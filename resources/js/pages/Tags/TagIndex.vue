<script setup lang="ts">
import PageFilters from '@/components/Ui/PageFilters.vue'
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
  <Head title="Tags" />

  <PageFilters
    title="Filter Tags"
    headline="Tags"
    :items="types"
    :active="type"
  />
</template>
