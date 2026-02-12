<script setup lang="ts">
import AppSidebar from '@/components/Ui/AppSidebar.vue'
import ServiceWorker from '@/components/Ui/ServiceWorker.vue'
import { useAppearance } from '@/composables/appearance'
import { Head } from '@inertiajs/vue3'

const { nonce } = useAppearance()
</script>

<template>
  <Head>
    <title>Stream videos</title>
  </Head>

  <Suspense>
    <UApp :nonce="nonce">
      <ServiceWorker />

      <UDashboardGroup
        unit="rem"
        storage="local"
        class="relative mx-auto w-full max-w-(--ui-container) overflow-clip"
      >
        <AppSidebar />

        <slot />
      </UDashboardGroup>
    </UApp>
  </Suspense>
</template>
