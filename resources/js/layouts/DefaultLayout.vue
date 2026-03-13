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
        <slot />
      </UDashboardGroup>
    </UApp>
  </Suspense>
</template>
