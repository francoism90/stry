<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Tags/Controllers/TagController'
import type { Tag } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'

const props = defineProps<{
  tag: Tag
}>()

const links: NavigationMenuItem[] = [
  {
    label: 'General',
    icon: 'i-lucide-tag',
    to: edit.url(props.tag.id),
    exact: true,
  },
]
</script>

<template>
  <Head :title="tag.name" />

  <UDashboardPanel id="tag">
    <template #header>
      <UDashboardNavbar :title="tag.name">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar>
        <UNavigationMenu
          :items="links"
          highlight
          class="-mx-1 flex-1"
        />
      </UDashboardToolbar>
    </template>

    <template #body>
      <slot />
    </template>
  </UDashboardPanel>
</template>
