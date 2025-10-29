<script setup lang="ts">
import TagController from '@/actions/App/Web/Tags/Controllers/TagController'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Tag } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem, RadioGroupItem } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  tag: Tag
  filter: string | undefined
  filters: RadioGroupItem[]
}

defineOptions({ layout: DashboardLayout })

const props = defineProps<Props>()

const links = ref<NavigationMenuItem[]>([{ to: TagController.edit(props.tag.id), label: 'Edit', icon: 'i-lucide-file-pen', size: 'sm', color: 'primary' }])

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
