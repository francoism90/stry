<script setup lang="ts">
import PageFilters from '@/components/Ui/PageFilters.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoCollection from '@/layouts/Video/VideoCollection.vue'
import { Head } from '@inertiajs/vue3'

defineOptions({ layout: [DefaultLayout, VideoCollection] })
</script>

<template>
  <Head title="History" />

  <PageFilters
    title="History"
    headline="Videos"
    :searchable="false"
  />
</template>
