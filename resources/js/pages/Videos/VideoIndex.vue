<script setup lang="ts">
import PageFilters from '@/components/Ui/PageFilters.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoCollection from '@/layouts/Video/VideoCollection.vue'
import { Head } from '@inertiajs/vue3'
import type { RadioGroupItem } from '@nuxt/ui'

interface Props {
  filter: string | undefined
  filters: RadioGroupItem[]
}

defineOptions({ layout: [DefaultLayout, VideoCollection] })

defineProps<Props>()
</script>

<template>
  <Head title="Videos" />

  <PageFilters
    title="Library"
    headline="Videos"
    :filters
    :filter
  />
</template>
