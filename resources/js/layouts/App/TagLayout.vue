<script setup lang="ts">
import { index, show } from '@/actions/App/Web/Tags/Controllers/TagController'
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { Tag } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import { computed } from 'vue'

const props = defineProps<{
  tag: Tag
}>()

const meta = computed(() => [props.tag.category, props.tag.created_at].filter(Boolean))

useEcho<Tag>(`tags.${props.tag.id}`, '.tag.updated', () => router.reload({ only: ['tag'] }))
useEcho<Tag>(`tags.${props.tag.id}`, '.tag.deleted', () => router.visit(index.url()))
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
          :links="[{ label: 'View tag', icon: 'i-lucide-eye', to: show.url(tag.id) }]"
        >
          <template #description>
            <div class="dot-separated flex flex-wrap items-center text-sm text-muted">
              <span
                v-for="(item, index) in meta"
                :key="index"
              >
                {{ item }}
              </span>
            </div>
          </template>
        </UPageHeader>

        <slot />
      </UPage>
    </template>
  </UDashboardPanel>
</template>
