<script setup lang="ts">
import DashboardSidebar from '@/components/Admin/DashboardSidebar.vue'
import AppNotifications from '@/components/Ui/AppNotifications.vue'
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
      <UDashboardGroup
        unit="rem"
        storage="local"
        storage-key="dashboard"
        class="relative w-full overflow-clip"
      >
        <AppNotifications />
        <DashboardSidebar />
        <slot />
      </UDashboardGroup>
    </UApp>
  </Suspense>
</template>
