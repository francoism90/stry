<script setup lang="ts">
import TagOrderController from '@/actions/App/Admin/Tags/Controllers/TagOrderController'
import { router } from '@inertiajs/vue3'

const isOpen = defineModel<boolean>({ default: false })

const toast = useToast()

const reorderTags = async (close: () => void) => {
  router.post(
    TagOrderController.url(),
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        toast.add({
          title: 'Success',
          description: 'Tags have been reordered alphabetically.',
          icon: 'i-lucide-check',
          color: 'success',
        })
        close()
      },
      onError: () => {
        toast.add({
          title: 'Error',
          description: 'Failed to reorder tags.',
          icon: 'i-lucide-alert-circle',
          color: 'error',
        })
      },
    },
  )
}
</script>

<template>
  <UModal
    v-model="isOpen"
    title="Reorder Tags"
    :ui="{ footer: 'justify-end' }"
  >
    <UButton
      label="Reorder tags"
      color="neutral"
      variant="soft"
      icon="i-lucide-arrow-up-down"
    />

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
        @click.prevent="reorderTags(close)"
      />
    </template>
  </UModal>
</template>
