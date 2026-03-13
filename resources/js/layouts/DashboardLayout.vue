<script setup lang="ts">
import DashboardSidebar from '@/components/Admin/DashboardSidebar.vue'
import { useAppearance } from '@/composables/appearance'
import { useNotifications } from '@/composables/notifications'
import { Head } from '@inertiajs/vue3'

const { nonce } = useAppearance()
useNotifications()
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
        <DashboardSidebar />
        <slot />
      </UDashboardGroup>
    </UApp>
  </Suspense>
</template>
