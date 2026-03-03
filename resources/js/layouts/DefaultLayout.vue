<script setup lang="ts">
import AppNotifications from '@/components/Ui/AppNotifications.vue'
import AppSidebar from '@/components/Ui/AppSidebar.vue'
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
          storage-key="app"
          class="relative mx-auto w-full max-w-(--ui-container) overflow-clip"
        >
          <AppNotifications />
          <AppSidebar />

          <slot />
        </UDashboardGroup>
      </UTheme>
    </UApp>
  </Suspense>
</template>
