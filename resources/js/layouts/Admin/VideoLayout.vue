<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Videos/Controllers/VideoController'
import type { Video } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'

const props = defineProps<{
  video: Video
}>()

const links: NavigationMenuItem[] = [
  {
    label: 'General',
    icon: 'i-lucide-user',
    to: edit.url({ video: props.video.id }),
    exact: true,
  },
  {
    label: 'Media',
    icon: 'i-lucide-users',
  },
]
</script>

<template>
  <Head :title="video.title" />

  <UDashboardPanel id="video">
    <template #header>
      <UDashboardNavbar title="Video">
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
      <div class="mx-auto flex w-full flex-col gap-4 sm:gap-6 lg:max-w-2xl lg:gap-12 lg:py-6">
        <slot />
      </div>
    </template>
  </UDashboardPanel>
</template>
