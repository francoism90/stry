<script setup lang="ts">
import GroupClearController from '@/actions/Modules/Web/Groups/Controllers/GroupClearController'
import type { Group } from '@/types'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  item: Group
}>()

const handle = async () => router.post(GroupClearController.url(props.item.id))
</script>

<template>
  <UModal
    title="Clear this collection?"
    :ui="{ footer: 'justify-end' }"
  >
    <slot>
      <UButton
        icon="i-lucide-eraser"
        aria-label="Clear collection"
        color="error"
        variant="ghost"
        size="sm"
      />
    </slot>

    <template #body>
      <p class="text-sm text-muted">
        All videos will be removed from
        <span class="text-highlighted capitalize">{{ item.title ?? item.id }}</span
        >. They stay in your library.
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
        label="Clear collection"
        variant="solid"
        color="error"
        loading-auto
        @click.prevent="handle"
      />
    </template>
  </UModal>
</template>
