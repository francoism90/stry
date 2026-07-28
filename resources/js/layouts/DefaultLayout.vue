<script setup lang="ts">
import { useAppearance } from '@/composables/appearance'
import { useFlash } from '@/composables/flash'
import { Head } from '@inertiajs/vue3'

const { nonce } = useAppearance()
useFlash()
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
            dropdownMenu: {
              content: 'z-50 min-w-32',
            },
            dashboardPanel: {
              body: 'mx-auto w-full max-w-(--ui-container) flex-1 self-center overflow-visible 2xl:px-0',
            },
            dashboardNavbar: {
              center: 'mx-auto flex w-full max-w-(--ui-container) justify-between',
              left: 'hidden',
              right: 'hidden',
            },
            dashboardToolbar: {
              left: 'hidden',
              right: 'hidden',
            },
            // pageHeader: {
            //   root: 'border-0 py-4 sm:py-6',
            //   container: 'flex flex-col gap-x-4 lg:block',
            //   wrapper: 'contents lg:flex lg:flex-row lg:items-center lg:justify-between lg:gap-4',
            //   title: 'wrap-anywhere capitalize',
            //   description: 'mt-0.5',
            //   links: 'order-last mt-3 flex-nowrap lg:mt-0',
            // },
            // pageBody: {
            //   base: 'mt-3 space-y-6 pb-0',
            // },
          }"
        >
          <slot />
        </UTheme>
      </UDashboardGroup>
    </UApp>
  </Suspense>
</template>
