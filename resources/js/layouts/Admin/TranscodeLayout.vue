<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Transcodes/Controllers/TranscodeController'
import type { Transcode } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem } from '@nuxt/ui'

const props = defineProps<{
  transcode: Transcode
}>()

const links: NavigationMenuItem[][] = [
  [
    {
      label: 'General',
      icon: 'i-lucide-film',
      to: edit.url(props.transcode.id),
      exact: true,
    },
  ],
]

useEcho<Transcode>(`transcodes.${props.transcode.id}`, '.transcode.updated', () =>
  router.reload({ only: ['transcode'] }),
)
</script>

<template>
  <Head :title="transcode.id" />

  <UDashboardPanel id="transcode">
    <template #header>
      <UDashboardNavbar :title="transcode.id">
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
