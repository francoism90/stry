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
              root: 'relative flex min-h-svh w-full flex-col',
              header: 'bg-default sticky top-0 z-50 w-full',
              body: 'flex w-full max-w-(--ui-container) flex-1 flex-col gap-4 self-center sm:gap-6',
            },
            dashboardNavbar: {
              root: 'flex min-h-4 w-full max-w-(--ui-container) shrink-0 items-center justify-between gap-1.5 self-center overflow-x-auto border-0 px-4 pt-4 sm:px-6 sm:pt-6',
            },
            dashboardToolbar: {
              root: 'flex min-h-4 w-full max-w-(--ui-container) shrink-0 items-center justify-between gap-1.5 self-center overflow-x-auto border-0 px-4 pt-4 sm:px-6 sm:pt-6',
              left: 'flex items-center gap-3 *:inline-flex *:items-center',
            },
            pageHeader: {
              root: 'border-0 py-0',
              description: 'mt-2',
            },
          }"
        >
          <slot />
        </UTheme>
      </UDashboardGroup>
    </UApp>
  </Suspense>
</template>
