<script setup lang="ts">
import { useSearch } from '@/composables/search'
import { router, useForm } from '@inertiajs/vue3'
import { watchDebounced } from '@vueuse/core'
import { useTemplateRef } from 'vue'

const { search } = useSearch()
const input = useTemplateRef('input')

const form = useForm({
  search: search.value ?? '',
})

watchDebounced(
  () => form.search,
  (value) => {
    router.visit(`/search/${encodeURIComponent(value)}`, {
      preserveState: true,
    })
  },
  { debounce: 350, maxWait: 1000 },
)

defineShortcuts({
  '/': () => {
    input.value?.inputRef?.focus()
  },
})
</script>

<template>
  <div class="flex items-center gap-1">
    <UInput
      ref="input"
      v-model="form.search"
      :model-modifiers="{ string: true, trim: true }"
      icon="i-lucide-search"
      placeholder="Search"
      variant="soft"
      size="lg"
      :ui="{
        root: 'w-full max-w-fit md:min-w-sm lg:min-w-lg',
        base: 'rounded-full',
        trailing: 'hidden md:inline-flex',
      }"
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
      class="hidden md:inline-flex"
    />
  </div>
</template>
