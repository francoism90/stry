<script setup lang="ts">
import AppHeader from '@/components/Ui/AppHeader.vue'
import { useHeaderCollapse } from '@/composables/scroll'

defineProps<{
  collapsible?: boolean
}>()

const { isHeaderCollapsed } = useHeaderCollapse()
</script>

<template>
  <div class="sticky top-0 z-50">
    <div
      aria-hidden="true"
      class="pointer-events-none absolute inset-0 -z-10 bg-default/75 backdrop-blur transition-opacity duration-300"
      :class="isHeaderCollapsed ? 'opacity-100' : 'opacity-0'"
    />

    <AppHeader />

    <div
      v-if="collapsible"
      class="grid transition-[grid-template-rows] duration-300 ease-in-out"
      :class="isHeaderCollapsed ? 'grid-rows-[0fr]' : 'grid-rows-[1fr]'"
    >
      <div class="overflow-hidden">
        <slot />
      </div>
    </div>

    <slot name="toolbar" />
  </div>
</template>
