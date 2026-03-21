<script setup lang="ts">
import AppLogo from '@/components/Ui/AppLogo.vue'
import { useAppearance } from '@/composables/appearance'
import { useAuth } from '@/composables/auth'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'

const { nonce } = useAppearance()
const { user } = useAuth()

useEcho(`users.${user.value?.id}`, '.user.updated', () => router.reload({ only: ['auth'] }))
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

  <Suspense>
    <UApp :nonce="nonce">
      <UContainer class="flex h-dvh max-h-dvh flex-col items-center justify-center gap-3 py-4 sm:py-6">
        <div class="py-2">
          <AppLogo />
        </div>

        <slot />
      </UContainer>
    </UApp>
  </Suspense>
</template>
