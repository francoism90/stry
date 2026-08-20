<script setup lang="ts">
import { update } from '@/actions/App/Web/Tags/Controllers/TagController'
import FormModal from '@/components/Ui/FormModal.vue'
import { useTags } from '@/composables/tags'
import type { Tag, TagMenuItem } from '@/types'
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    item: Tag
    trigger?: boolean
  }>(),
  {
    trigger: true,
  },
)

const open = defineModel<boolean>('open')

const types = computed(() => usePage().props.tagTypes)
const { items, filter } = useTags(props.item.related || [])

const form = useForm(update(props.item.id), {
  name: props.item.name,
  type: props.item.type,
  related: props.item.related || [],
  description: props.item.description || null,
})

const onSubmit = (close: () => void) =>
  form.submit({
    preserveScroll: true,
    onSuccess: () => close(),
  })
</script>

<template>
  <FormModal
    v-model:open="open"
    :title="`Edit ${item.name}`"
    :processing="form.processing"
    @submit="onSubmit"
  >
    <template v-if="trigger" #default>
      <slot>
        <UButton
          icon="i-lucide-pencil"
          color="neutral"
          variant="ghost"
          size="sm"
        />
      </slot>
    </template>

    <template #body>
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
          />
        </UFormField>

        <UFormField
          label="Type"
          required
          :error="form.errors.type"
        >
          <USelectMenu
            v-model="form.type"
            value-key="value"
            :items="types"
            placeholder="Select a type"
            class="w-full"
          />
        </UFormField>

        <UFormField
          label="Related tags"
          :error="form.errors.related"
        >
          <USelectMenu
            v-model="form.related as TagMenuItem[]"
            :items="items as TagMenuItem[]"
            :ignore-filter="true"
            label-key="name"
            multiple
            class="w-full"
            placeholder="Add related tags"
            @update:search-term="(value: string) => filter({ query: { query: value } })"
          >
            <template #item-label="{ item }">
              {{ item.name }}

              <span class="text-muted">
                {{ item.category }}
              </span>
            </template>
          </USelectMenu>
        </UFormField>

        <UFormField
          label="Description"
          :error="form.errors.description"
        >
          <UTextarea
            v-model="form.description"
            :model-modifiers="{ nullable: true, string: true, trim: true }"
            :rows="3"
            autoresize
            placeholder="Enter markdown (optional)"
            class="w-full"
          />
        </UFormField>
      </UForm>
    </template>
  </FormModal>
</template>
