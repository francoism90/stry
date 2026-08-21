<script setup lang="ts">
import { update } from '@/actions/App/Web/Groups/Controllers/GroupController'
import GroupDeleteModal from '@/components/Groups/GroupDeleteModal.vue'
import FormModal from '@/components/Ui/FormModal.vue'
import type { Group } from '@/types'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  group: Group
}>()

const open = defineModel<boolean>('open')

const form = useForm(update(props.group.id), {
  name: props.group.name ?? props.group.title,
  content: props.group.content || null,
})

const onSubmit = (close: () => void) =>
  form.submit({
    preserveState: true,
    onSuccess: () => close(),
  })
</script>

<template>
  <FormModal
    v-model:open="open"
    :title="`Edit ${group.title}`"
    :processing="form.processing"
    @submit="onSubmit"
  >
    <template #body>
      <div class="flex flex-col gap-4">
        <UForm
          :state="form"
          class="flex flex-col gap-3"
        >
          <UFormField
            label="Name"
            required
            :error="form.errors.name"
          >
            <UInput
              v-model="form.name"
              :model-modifiers="{ string: true, trim: true }"
              autofocus
              autocapitalize="words"
            />
          </UFormField>

          <USeparator />

          <UFormField
            label="Content"
            :error="form.errors.content"
          >
            <UTextarea
              v-model="form.content"
              :model-modifiers="{ nullable: true, string: true, trim: true }"
              :rows="5"
              autoresize
              placeholder="Enter markdown"
              class="w-full"
            />
          </UFormField>
        </UForm>

        <USeparator />

        <div class="flex flex-col gap-2">
          <p class="text-sm font-semibold text-error">Delete collection</p>
          <p class="text-sm text-muted">Permanently remove this collection and all associated data.</p>

          <GroupDeleteModal :item="group">
            <UButton
              label="Delete collection"
              icon="i-lucide-trash"
              color="error"
              variant="soft"
              size="sm"
              class="w-fit"
            />
          </GroupDeleteModal>
        </div>
      </div>
    </template>
  </FormModal>
</template>
