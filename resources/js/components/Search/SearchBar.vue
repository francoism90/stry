<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';

const props = defineProps<{
  search: string
  placeholder?: string
  suffix: string
  backHref: string
}>()

const form = useForm({
  search: props.search,
})

watchDebounced(
  () => form.search,
  (value) => {
    router.visit(`/search/${encodeURIComponent(value)}${props.suffix}`, {
      preserveState: true,
      only: ['search', 'items'],
      reset: ['items'],
    })
  },
  { debounce: 350, maxWait: 1000 },
)
</script>

<template>
  <UDashboardToolbar
    :ui="{
      root: 'border-default min-h-16 border-b',
      left: 'flex-1',
    }"
  >
    <template #left>
      <div class="mx-auto flex w-full max-w-(--ui-container) items-center gap-4 px-4 sm:px-6 lg:px-8">
        <UFormField
          :error="form.errors.search"
          class="min-w-0 flex-1"
        >
          <UInput
            v-model="form.search"
            :model-modifiers="{ string: true, trim: true }"
            :placeholder="placeholder ?? 'Search...'"
            variant="soft"
            size="xl"
            color="neutral"
            class="w-full"
            icon="i-lucide-search"
            autofocus
          />
        </UFormField>

        <UButton
          variant="ghost"
          color="neutral"
          size="sm"
          leading-icon="i-lucide-arrow-left"
          :to="backHref"
        >
          All results
        </UButton>
      </div>
    </template>
  </UDashboardToolbar>
</template>
