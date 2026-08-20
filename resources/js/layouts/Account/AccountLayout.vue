<script setup lang="ts">
import AccountController from '@/actions/App/Web/Account/Controllers/AccountController'
import NotificationsController from '@/actions/App/Web/Account/Controllers/NotificationsController'
import SecurityController from '@/actions/App/Web/Account/Controllers/SecurityController'
import SettingsController from '@/actions/App/Web/Account/Controllers/SettingsController'
import AppLogo from '@/components/Ui/AppLogo.vue'
import { useAppearance } from '@/composables/appearance'
import { useFlash } from '@/composables/flash'
import type { NavigationMenuItem } from '@nuxt/ui'

defineProps<{
  title?: string
  tabs?: NavigationMenuItem[]
}>()

const { nonce } = useAppearance()
useFlash()

const items: NavigationMenuItem[] = [
  {
    label: 'Account',
    icon: 'i-lucide-user',
    to: AccountController.url(),
  },
  {
    label: 'Security',
    icon: 'i-lucide-shield',
    to: SecurityController.url(),
  },
  {
    label: 'Settings',
    icon: 'i-lucide-settings',
    to: SettingsController.url(),
  },
  {
    label: 'Notifications',
    icon: 'i-lucide-bell',
    to: NotificationsController.index.url(),
  },
]
</script>

<template>
  <UApp :nonce="nonce">
    <UDashboardGroup
      unit="rem"
      storage-key="account"
      class="relative overflow-clip"
    >
      <UDashboardSidebar
        :default-size="18"
        :resizable="false"
        class="bg-elevated/25"
      >
        <template #header>
          <AppLogo />
        </template>

        <UNavigationMenu
          :items="items"
          orientation="vertical"
          :ui="{
            link: 'py-3',
          }"
        />
      </UDashboardSidebar>

      <UDashboardPanel id="account">
        <template #header>
          <UDashboardNavbar :title="title" />

          <UDashboardToolbar v-if="tabs">
            <UNavigationMenu
              :items="tabs"
              highlight
              class="-mx-1 flex-1"
            />
          </UDashboardToolbar>
        </template>

        <template #body>
          <div class="mx-auto flex w-full flex-col gap-4 sm:gap-6 lg:max-w-3xl lg:gap-12">
            <slot />
          </div>
        </template>
      </UDashboardPanel>
    </UDashboardGroup>
  </UApp>
</template>
