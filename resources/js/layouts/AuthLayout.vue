<script setup lang="ts">
import { useAppearance } from '@/composables/appearance'
import { useFlash } from '@/composables/flash'
import { Head } from '@inertiajs/vue3'

const { nonce } = useAppearance()
useFlash()
</script>

<template>
  <Head>
    <title>Account</title>
    <meta
      head-key="description"
      name="description"
      content="Manage your account and access your dashboard."
    />
  </Head>

  <UApp :nonce="nonce">
    <UDashboardGroup
      unit="rem"
      storage="local"
      storage-key="auth"
      class="relative overflow-clip"
    >
      <slot />
    </UDashboardGroup>
  </UApp>
</template>
