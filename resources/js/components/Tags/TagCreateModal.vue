<script setup lang="ts">
import { store } from '@/actions/App/Web/Tags/Controllers/TagController'
import FormModal from '@/components/Ui/FormModal.vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

withDefaults(
  defineProps<{
    trigger?: boolean
  }>(),
  {
    trigger: true,
  },
)

const open = defineModel<boolean>('open')

const types = computed(() => usePage().props.tags)

const form = useForm(store(), {
  name: '',
  type: null,
  description: null,
})

const onSubmit = (close: () => void) =>
  form.submit({
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      close()
    },
  })
</script>

<template>
  <FormModal
    v-model:open="open"
    title="Create Tag"
    submit-label="Create tag"
    :processing="form.processing"
    @submit="onSubmit"
  >
    <template
      v-if="trigger"
      #default
    >
      <slot>
        <UButton
          label="Create tag"
          color="neutral"
          variant="link"
          size="sm"
          icon="i-lucide-plus"
          class="px-0"
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
            placeholder="Enter tag name"
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
          label="Description"
          :error="form.errors.description"
        >
          <UTextarea
            v-model="form.description"
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
