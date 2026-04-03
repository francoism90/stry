<script setup lang="ts">
import AccountController from '@/actions/App/Web/Account/Controllers/AccountController'
import SecurityController from '@/actions/App/Web/Account/Controllers/SecurityController'
import SettingsController from '@/actions/App/Web/Account/Controllers/SettingsController'
import AppHeader from '@/components/Ui/AppHeader.vue'
import { useAuth } from '@/composables/auth'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import { computed } from 'vue'

const { user } = useAuth()

const joinedAt = computed(() => {
  if (!user.value?.created_at) {
    return null
  }

  return new Date(user.value.created_at).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
})

const tabs: NavigationMenuItem[] = [
  {
    label: 'Profile',
    icon: 'i-lucide-user',
    to: AccountController.url(),
    exact: true,
  },
  {
    label: 'Settings',
    icon: 'i-lucide-settings',
    to: SettingsController.url(),
    exact: true,
  },
  {
    label: 'Security',
    icon: 'i-lucide-lock',
    to: SecurityController.url(),
    exact: true,
  },
]

useEcho(`users.${user.value?.id}`, '.user.updated', () => router.reload({ only: ['auth', 'user'] }))
</script>

<template>
  <Head title="Account" />

  <UDashboardPanel id="account">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage class="mx-auto w-full max-w-6xl px-4 sm:px-6">
        <UPageHeader
          :title="user?.name || 'Account'"
          :description="joinedAt ? `Joined ${joinedAt}` : undefined"
        />

        <UNavigationMenu
          :items="tabs"
          variant="link"
          highlight
          :ui="{
            root: 'border-default w-full flex-1 border-b',
          }"
        />

        <slot />
      </UPage>
    </template>
  </UDashboardPanel>
</template>
