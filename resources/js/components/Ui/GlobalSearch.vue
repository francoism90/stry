<script setup lang="ts">
import { useSearch } from '@/composables/search'
import { useTemplateRef } from 'vue'

const { search } = useSearch()

const input = useTemplateRef('input')

defineShortcuts({
  '/': () => {
    input.value?.inputRef?.focus()
  },
})
</script>

<template>
  <div class="mx-3 flex items-center gap-1">
    <UInput
      ref="input"
      icon="i-lucide-search"
      placeholder="Search..."
      class="lg:min-w-lg"
      :default-value="search"
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
