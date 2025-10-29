<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Tag } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { RadioGroupItem } from '@nuxt/ui'

interface Props {
  tag: Tag
  filter: string | undefined
  filters: RadioGroupItem[]
}

defineOptions({ layout: DashboardLayout })

const props = defineProps<Props>()

useEcho<Tag>(`tags.${props.tag.id}`, '.tag.updated', () => router.reload({ only: ['tag'] }))
</script>

<template>
  <Head :title="tag.name" />

  <UDashboardPanel id="tag-view">
    <template #header>
      <UDashboardNavbar />
    </template>
  </UDashboardPanel>
</template>
