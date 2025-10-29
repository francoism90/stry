<script setup lang="ts">
import PageHeader from '@/components/Ui/PageHeader.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import TagCollection from '@/layouts/Tag/TagCollection.vue'
import { Head } from '@inertiajs/vue3'
import type { RadioGroupItem } from '@nuxt/ui'

defineOptions({ layout: [DefaultLayout, TagCollection] })

interface Props {
  filter: string | undefined
  filters: RadioGroupItem[]
}

defineProps<Props>()
</script>

<template>
  <Head title="Tags" />

  <PageHeader
    title="Overview"
    headline="Tags"
    :filters
    :filter
  />
</template>
