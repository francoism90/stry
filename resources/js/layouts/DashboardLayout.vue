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
      <UTheme
        :ui="{
          dashboardPanel: {
            slots: {
              body: 'overflow-clip',
            },
          },
          dashboardNavbar: {
            slots: {
              root: 'bg-default sticky top-0 z-50 w-full',
            },
          },
        }"
      >
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
      </UTheme>
    </UApp>
  </Suspense>
</template>
