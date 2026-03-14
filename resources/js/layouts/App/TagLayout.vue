<script setup lang="ts">
import { show } from '@/actions/App/Web/Tags/Controllers/TagController'
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { Tag } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'

const props = defineProps<{
  tag: Tag
}>()

useEcho<Tag>(`tags.${props.tag.id}`, '.tag.updated', () => router.reload({ only: ['tag'] }))
</script>

<template>
  <Head :title="tag.name" />

  <UDashboardPanel id="tag">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage class="mx-auto w-full max-w-6xl px-4 sm:px-6">
        <UPageHeader
          :title="tag.name"
          :description="tag.category"
          :links="[{ label: 'View tag', icon: 'i-lucide-eye', to: show.url(tag.id) }]"
        />

        <slot />
      </UPage>
    </template>
  </UDashboardPanel>
</template>
