<script setup lang="ts">
import GroupClearController from '@/actions/App/Web/Groups/Controllers/GroupClearController'
import type { CollectionItem } from '@/types'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  item: CollectionItem
}>()

const handle = async () => {
  if (!props.item.id) return

  router.post(GroupClearController.url(props.item.id))
}
</script>

<template>
  <UModal
    :title="item.title ?? item.id"
    :ui="{ footer: 'justify-end' }"
  >
    <slot>
      <UButton
        icon="i-lucide-eraser"
        color="error"
        variant="ghost"
        size="sm"
      />
    </slot>

    <template #body>
      <div class="flex h-24 flex-col gap-2">
        <h3>Are you sure you want to clear this collection?</h3>
        <p class="text-sm text-neutral-500">
          All videos will be removed from this collection. This action cannot be undone.
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
        label="Clear collection"
        variant="soft"
        color="error"
        loading-auto
        @click.prevent="handle"
      />
    </template>
  </UModal>
</template>
