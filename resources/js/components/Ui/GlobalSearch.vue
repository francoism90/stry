<script setup lang="ts">
import { useTemplateRef } from 'vue'

const input = useTemplateRef('input')

defineShortcuts({
  '/': () => {
    input.value?.inputRef?.focus()
  },
})
</script>

<template>
  <UInput
    ref="input"
    icon="i-lucide-search"
    placeholder="Search..."
    class="min-w-lg"
  >
    <template #trailing>
      <UKbd value="/" />
    </template>
  </UInput>
</template>
