<script setup lang="ts">
import { update } from '@/actions/Modules/Web/Groups/Controllers/GroupController'
import GroupClearModal from '@/components/Groups/GroupClearModal.vue'
import GroupDeleteModal from '@/components/Groups/GroupDeleteModal.vue'
import FormModal from '@/components/Ui/FormModal.vue'
import type { Group } from '@/types'
import { useForm } from '@inertiajs/vue3'
import type { TabsItem } from '@nuxt/ui'
import { computed } from 'vue'

const props = defineProps<{
  group: Group
}>()

const open = defineModel<boolean>('open')

const tabs: TabsItem[] = [
  { label: 'Details', slot: 'details' },
  { label: 'Danger zone', slot: 'danger' },
]

const videoCount = computed(() => props.group.videos ?? 0)

const keptVideosLabel = computed(() => {
  if (videoCount.value === 0) {
    return ''
  }

  return videoCount.value === 1
    ? 'Its video stays in your library.'
    : `Its ${Intl.NumberFormat().format(videoCount.value)} videos stay in your library.`
})

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
    title="Edit collection"
    submit-label="Save"
    :processing="form.processing"
    :tabs="tabs"
    @submit="onSubmit"
  >
    <template #details>
      <UForm
        :state="form"
        class="flex flex-col gap-4"
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
            class="w-full"
          />
        </UFormField>

        <UFormField
          label="Description"
          :error="form.errors.content"
        >
          <UTextarea
            v-model="form.content"
            :model-modifiers="{ nullable: true, string: true, trim: true }"
            :rows="3"
            autoresize
            placeholder="What is this collection for?"
            class="w-full"
          />
        </UFormField>
      </UForm>
    </template>

    <template #danger>
      <div class="flex flex-col gap-3">
        <div
          class="flex flex-wrap items-center justify-between gap-x-4 gap-y-3 rounded-lg border border-error/35 bg-error/8 p-4"
        >
          <div class="flex min-w-0 flex-[1_1_15rem] flex-col gap-0.5 text-sm">
            <span class="font-medium text-highlighted">Clear this collection</span>
            <span class="text-muted">Removes all videos from {{ group.title }}.</span>
          </div>

          <GroupClearModal :item="group">
            <UButton
              label="Clear"
              icon="i-lucide-eraser"
              color="error"
              variant="outline"
              size="sm"
              class="shrink-0 ring-error/50"
            />
          </GroupClearModal>
        </div>

        <div
          class="flex flex-wrap items-center justify-between gap-x-4 gap-y-3 rounded-lg border border-error/35 bg-error/8 p-4"
        >
          <div class="flex min-w-0 flex-[1_1_15rem] flex-col gap-0.5 text-sm">
            <span class="font-medium text-highlighted">Delete this collection</span>
            <span class="text-muted"> {{ group.title }} will be removed. {{ keptVideosLabel }} </span>
          </div>

          <GroupDeleteModal :item="group">
            <UButton
              label="Delete"
              icon="i-lucide-trash-2"
              color="error"
              variant="outline"
              size="sm"
              class="shrink-0 ring-error/50"
            />
          </GroupDeleteModal>
        </div>
      </div>
    </template>
  </FormModal>
</template>
