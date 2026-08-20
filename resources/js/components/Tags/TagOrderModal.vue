<script setup lang="ts">
import TagOrderController from '@/actions/App/Web/Tags/Controllers/TagOrderController'
import { router } from '@inertiajs/vue3'

const handle = async (close: () => void) =>
  router.post(
    TagOrderController.url(),
    {},
    {
      preserveScroll: true,
      onSuccess: () => close(),
    },
  )
</script>

<template>
  <UModal
    title="Reorder Tags"
    :ui="{ footer: 'justify-end' }"
  >
    <slot>
      <UButton
        label="Reorder tags"
        color="neutral"
        variant="soft"
        icon="i-lucide-arrow-up-down"
      />
    </slot>

    <template #body>
      <div class="flex flex-col gap-2">
        <p>This will reorder all tags alphabetically by type and name.</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Tags will be sorted naturally (e.g., "Tag 2" before "Tag 10").
        </p>
      </div>
    </template>

    <template #footer="{ close }">
      <UButton
        label="Cancel"
        color="neutral"
        variant="soft"
        @click.prevent="close"
      />

      <UButton
        label="Reorder tags"
        variant="soft"
        color="primary"
        icon="i-lucide-arrow-up-down"
        loading-auto
        @click.prevent="handle(close)"
      />
    </template>
  </UModal>
</template>
