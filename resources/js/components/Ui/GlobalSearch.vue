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
  <div class="flex items-center gap-2">
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

    <UButton
      size="sm"
      icon="i-lucide-dices"
      color="neutral"
      variant="link"
    />
  </div>
</template>
