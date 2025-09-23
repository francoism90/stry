<script setup lang="ts">
import PageBody from '@/components/Ui/PageBody.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import { Head, router } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Profile" />

  <PageBody>
    <PageSection class="gap-3">
      <UPageFeature title="Profile" />

      <UButton
        label="Logout"
        class="w-fit"
        variant="soft"
        @click="() => router.post('/logout')"
      />
    </PageSection>
  </PageBody>
</template>
