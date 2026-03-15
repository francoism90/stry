<script setup lang="ts">
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
        storage-key="app"
        class="relative w-full overflow-clip"
      >
        <UTheme
          :ui="{
            dashboardPanel: {
              body: 'w-full max-w-(--ui-container) flex-1 self-center overflow-visible py-0 sm:py-0',
            },
            dashboardNavbar: {
              root: 'min-h-14 overflow-x-auto border-0',
            },
            dashboardToolbar: {
              root: 'min-h-14 overflow-x-auto border-0 px-0 sm:px-0',
            },
            pageHeader: {
              root: 'border-0 py-4 sm:py-6',
              container: 'flex flex-col gap-x-4 lg:block',
              wrapper: 'contents lg:flex lg:flex-row lg:items-center lg:justify-between lg:gap-4',
              description: 'mt-0.5',
              links: 'order-last mt-3 lg:mt-0',
            },
            pageBody: {
              base: 'mt-3 space-y-6',
            },
            tabs: {
              root: 'mt-3',
            },
          }"
        >
          <slot />
        </UTheme>
      </UDashboardGroup>
    </UApp>
  </Suspense>
</template>
