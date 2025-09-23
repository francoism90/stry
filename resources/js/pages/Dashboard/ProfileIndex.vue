<script setup lang="ts">
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import { Head, router } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Profile" />

  <UPageBody>
    <PageSection class="gap-4">
      <PageFeature title="Profile" />

      <UButton
        label="Logout"
        class="w-fit"
        variant="soft"
        @click="() => router.post('/logout')"
      />
    </PageSection>
  </UPageBody>
</template>
