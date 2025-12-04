<script setup lang="ts">
import AppHeader from '@/components/Ui/AppHeader.vue'
import { Head } from '@inertiajs/vue3'
</script>

<template>
  <Head>
    <title>Stream videos</title>
    <meta
      head-key="description"
      name="description"
      content="Stream and watch your favorite videos."
    />
  </Head>

  <Suspense>
    <UApp>
      <div class="mx-auto w-full max-w-(--ui-container)">
        <AppHeader />

        <slot />
      </div>
    </UApp>
  </Suspense>
</template>
