<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Media/Controllers/MediaController'
import type { Media } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem } from '@nuxt/ui'

const props = defineProps<{
  media: Media
}>()

const links: NavigationMenuItem[][] = [
  [
    {
      label: 'General',
      icon: 'i-lucide-film',
      to: edit.url(props.media.id),
      exact: true,
    },
  ],
]

useEcho<Media>(`medias.${props.media.id}`, '.media.updated', () => router.reload({ only: ['media'] }))
</script>

<template>
  <Head :title="media.id" />

  <UDashboardPanel id="media">
    <template #header>
      <UDashboardNavbar :title="media.id">
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
