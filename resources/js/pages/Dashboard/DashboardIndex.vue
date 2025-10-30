<script setup lang="ts">
import DashboardToolbar from '@/components/Dashboard/DashboardToolbar.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { Video } from '@/types'
import { Head } from '@inertiajs/vue3'

interface Props {
  items: Video[] | null
}

defineOptions({ layout: [DefaultLayout, DashboardLayout] })

defineProps<Props>()
</script>

<template>
  <Head title="Library" />

  <UPage>
    <DashboardToolbar />

    <UPageBody>
      <UPageHero title="Ultimate Vue UI library" />

      {{ items }}

      <UPageSection class="h-10 w-full bg-amber-500">foo</UPageSection>

      <UPageSection title="Beautiful Vue UI components" />
    </UPageBody>
  </UPage>
</template>
