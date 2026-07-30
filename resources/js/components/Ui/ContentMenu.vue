<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import type { DropdownMenuItem } from '@nuxt/ui'
import { useElementHover } from '@vueuse/core'
import { computed, ref, useTemplateRef } from 'vue'

const page = usePage()

const element = useTemplateRef('contentMenu')
const hovered = useElementHover(element, { delayLeave: 150 })

const items = ref<DropdownMenuItem[]>([
  {
    label: 'Videos',
    icon: 'i-lucide-file-video',
    to: '/',
    exact: true,
  },
  {
    label: 'Tags',
    icon: 'i-lucide-tag-plus',
    to: '/tags',
  },
  {
    label: 'Collections',
    icon: 'i-lucide-folder',
    to: '/collections',
  },
])

const currentItem = computed(() =>
  (items.value as { to?: string; label?: string }[]).find((item) =>
    item.to === '/' ? page.url === '/' : page.url.startsWith(item.to ?? ''),
  ),
)
</script>

<template>
  <div ref="contentMenu">
    <UDropdownMenu
      v-model:open="hovered"
      :modal="false"
      :portal="false"
      :items="items"
    >
      <UButton
        :label="currentItem?.label ?? 'Videos'"
        variant="soft"
        trailing-icon="i-lucide-chevron-down"
        :class="['ms-2', hovered && 'bg-primary/15']"
        :ui="{
          trailingIcon: ['transition-transform duration-200', hovered ? 'rotate-180' : undefined]
            .filter(Boolean)
            .join(' '),
        }"
      />
    </UDropdownMenu>
  </div>
</template>
