<script setup lang="ts">
import type { DropdownMenuItem } from '@nuxt/ui'
import { computed, ref } from 'vue'

defineProps<{
  collapsed?: boolean
}>()

const tenants = ref([
  {
    label: 'Vue',
    avatar: {
      src: 'https://github.com/vuejs.png',
      alt: 'Vue',
    },
  },
  {
    label: 'Vite',
    avatar: {
      src: 'https://github.com/vitejs.png',
      alt: 'Vite',
    },
  },
])

const selectedTenant = ref(tenants.value[0])

const items = computed<DropdownMenuItem[][]>(() => {
  return [
    tenants.value.map((team) => ({
      ...team,
      onSelect() {
        selectedTenant.value = team
      },
    })),
    [
      {
        label: 'Create tenant',
        icon: 'i-lucide-circle-plus',
      },
      {
        label: 'Manage tenants',
        icon: 'i-lucide-cog',
      },
    ],
  ]
})
</script>

<template>
  <UDropdownMenu
    :items="items"
    :content="{ align: 'center', collisionPadding: 12 }"
    :ui="{ content: collapsed ? 'w-40' : 'w-(--reka-dropdown-menu-trigger-width)' }"
  >
    <UButton
      v-bind="{
        ...selectedTenant,
        label: collapsed ? undefined : selectedTenant?.label,
        trailingIcon: collapsed ? undefined : 'i-lucide-chevrons-up-down',
      }"
      color="neutral"
      variant="ghost"
      block
      :square="collapsed"
      class="data-[state=open]:bg-elevated"
      :class="[!collapsed && 'py-2']"
      :ui="{
        trailingIcon: 'text-dimmed',
      }"
    />
  </UDropdownMenu>
</template>
