<script setup lang="ts">
import { destroy } from '@/actions/App/Web/Tags/Controllers/TagController'
import type { Tag } from '@/types'
import { router } from '@inertiajs/vue3'

interface Props {
  item: Tag
}

const props = defineProps<Props>()

const remove = () =>
  router.delete(destroy.url({ tag: props.item.id }), {
    onSuccess: () => router.visit('/'),
  })
</script>

<template>
  <UModal
    :title="item.name"
    :description="item.type"
    :ui="{ footer: 'justify-end' }"
  >
    <UButton
      label="Delete tag"
      color="warning"
      variant="soft"
    />

    <template #body>
      <div class="flex h-24 flex-col gap-2">
        <h3>Are you sure you want to delete this tag?</h3>
        <p class="text-sm text-neutral-500">This action cannot be undone. All associated data will be permanently removed.</p>
      </div>
    </template>

    <template #footer="{ close }">
      <UButton
        label="Cancel"
        color="neutral"
        variant="soft"
        @click="close"
      />

      <UButton
        label="Delete tag"
        variant="soft"
        color="primary"
        @click="remove"
      />
    </template>
  </UModal>
</template>
