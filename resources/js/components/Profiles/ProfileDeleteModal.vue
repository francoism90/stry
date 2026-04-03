<script setup lang="ts">
import { destroy } from '@/actions/App/Web/Profiles/Controllers/ProfileController'
import type { Profile } from '@/types'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  item: Profile
}>()

const handle = async () => router.delete(destroy.url(props.item.id))
</script>

<template>
  <UModal
    :title="item.name"
    :ui="{ footer: 'justify-end' }"
  >
    <slot>
      <UButton
        icon="i-lucide-trash-2"
        color="error"
        variant="ghost"
        size="sm"
      />
    </slot>

    <template #body>
      <div class="flex h-24 flex-col gap-2">
        <h3>Are you sure you want to delete this profile?</h3>
        <p class="text-sm text-neutral-500">
          This action cannot be undone. All associated data will be permanently removed.
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
        label="Delete profile"
        color="error"
        variant="soft"
        loading-auto
        @click.prevent="handle"
      />
    </template>
  </UModal>
</template>
