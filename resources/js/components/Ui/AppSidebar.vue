<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

defineProps<{
  mode: 'drawer' | 'slideover' | 'modal'
}>()

const items: NavigationMenuItem[] = [
  {
    label: 'Home',
    icon: 'i-lucide-house',
    active: true,
  },
  {
    label: 'Inbox',
    icon: 'i-lucide-inbox',
  },
  {
    label: 'Contacts',
    icon: 'i-lucide-users',
  },
]
</script>

<template>
  <UDashboardSidebar
    :mode="mode"
    :ui="{
      root: 'lg:hidden',
      content: 'lg:flex',
      overlay: 'lg:block',
    }"
  >
    <template #header="{ collapsed }">
      <Logo
        v-if="!collapsed"
        class="h-5 w-auto"
      />
      <UIcon
        v-else
        name="i-simple-icons-nuxtdotjs"
        class="mx-auto size-5 text-primary"
      />
    </template>

    <UNavigationMenu
      :items="items"
      orientation="vertical"
    />
  </UDashboardSidebar>
</template>
