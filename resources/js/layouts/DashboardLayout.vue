<script setup lang="ts">
import DashboardSidebar from '@/components/Admin/DashboardSidebar.vue'
import AppNotifications from '@/components/Ui/AppNotifications.vue'
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
      <AppNotifications />
      <ServiceWorker />

      <UDashboardGroup
        unit="rem"
        storage="local"
        class="relative w-full overflow-clip"
      >
        <DashboardSidebar />

        <slot />
      </UDashboardGroup>
    </UApp>
  </Suspense>
</template>
