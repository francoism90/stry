<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import type { DropdownMenuItem } from '@nuxt/ui'
import { computed, ref } from 'vue'

defineProps<{
  collapsed?: boolean
}>()

const page = usePage()

const user = ref({
  name: page.props.auth?.name || 'User',
  avatar: {
    alt: page.props.auth?.name,
  },
})

const items = computed<DropdownMenuItem[][]>(() => [
  [
    {
      label: 'Profile',
      icon: 'i-lucide-user',
    },
    {
      label: 'Settings',
      icon: 'i-lucide-settings',
      to: '/settings',
    },
  ],
  [
    {
      label: 'Horizon',
      icon: 'i-lucide-bar-chart-3',
      to: '/horizon',
      target: '_blank',
    },
    {
      label: 'Documentation',
      icon: 'i-lucide-book-open',
      to: 'https://ui.nuxt.com/docs/getting-started/installation/vue',
      target: '_blank',
    },
    {
      label: 'GitHub repository',
      icon: 'simple-icons:github',
      to: 'https://github.com/nuxt-ui-templates/dashboard-vue',
      target: '_blank',
    },
  ],
  [
    {
      label: 'Log out',
      icon: 'i-lucide-log-out',
    },
  ],
])
</script>

<template>
  <UDropdownMenu
    :items="items"
    :content="{ align: 'center', collisionPadding: 12 }"
    :ui="{ content: collapsed ? 'w-48' : 'w-(--reka-dropdown-menu-trigger-width)' }"
  >
    <UButton
      v-bind="{
        ...user,
        label: collapsed ? undefined : user?.name,
        trailingIcon: collapsed ? undefined : 'i-lucide-chevrons-up-down',
      }"
      color="neutral"
      variant="ghost"
      block
      :square="collapsed"
      class="data-[state=open]:bg-elevated"
      :ui="{
        trailingIcon: 'text-dimmed',
      }"
    />

    <template #chip-leading="{ item }">
      <div class="inline-flex size-5 shrink-0 items-center justify-center">
        <span
          class="size-2 rounded-full bg-(--chip-light) ring ring-bg dark:bg-(--chip-dark)"
          :style="{
            '--chip-light': `var(--color-${(item as any).chip}-500)`,
            '--chip-dark': `var(--color-${(item as any).chip}-400)`,
          }"
        />
      </div>
    </template>
  </UDropdownMenu>
</template>
