<script setup lang="ts">
import DashboardToolbar from '@/components/Dashboard/DashboardToolbar.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

defineOptions({ layout: [DefaultLayout, DashboardLayout] })

const page = usePage()

const user = computed(() => page.props.auth)
</script>

<template>
  <Head title="Library" />

  <UPage>
    <DashboardToolbar />

    <UPageBody>
      <UPageHero title="Ultimate Vue UI library" />

      <UPageSection class="h-10 w-full bg-amber-500">foo</UPageSection>

      <UPageSection title="Beautiful Vue UI components" />
    </UPageBody>
  </UPage>
</template>
