<script setup lang="ts">
import { destroy } from '@/actions/Modules/Web/Tags/Controllers/TagController'
import type { Tag } from '@/types'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  item: Tag
}>()

const handle = async () => router.delete(destroy.url(props.item.id))
</script>

<template>
  <UModal
    title="Delete this tag?"
    :ui="{ footer: 'justify-end' }"
  >
    <slot>
      <UButton
        icon="i-lucide-trash"
        aria-label="Delete tag"
        color="error"
        variant="ghost"
        size="sm"
      />
    </slot>

    <template #body>
      <p class="text-sm text-muted">
        <span class="text-highlighted">{{ item.name }}</span>
        and all its data will be permanently removed. This can't be undone.
      </p>
    </template>

    <template #footer="{ close }">
      <UButton
        label="Cancel"
        color="neutral"
        variant="soft"
        @click.prevent="close"
      />

      <UButton
        label="Delete tag"
        variant="solid"
        color="error"
        loading-auto
        @click.prevent="handle"
      />
    </template>
  </UModal>
</template>
