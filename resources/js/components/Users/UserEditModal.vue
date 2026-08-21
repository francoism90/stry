<script setup lang="ts">
import { update } from '@/actions/App/Web/Users/Controllers/UserController'
import FormModal from '@/components/Ui/FormModal.vue'
import type { User } from '@/types'
import { useForm } from '@inertiajs/vue3'
import type { TabsItem } from '@nuxt/ui'

const props = defineProps<{
  item: User
}>()

const open = defineModel<boolean>('open')

const tabs: TabsItem[] = [{ label: 'General', icon: 'i-lucide-user', slot: 'general' }]

const form = useForm(update(props.item.id), {
  name: props.item.name,
  email: props.item.email ?? '',
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
    :tabs="tabs"
    @submit="onSubmit"
  >
    <template #default>
      <slot>
        <UButton
          icon="i-lucide-pencil"
          color="neutral"
          variant="ghost"
          size="sm"
        />
      </slot>
    </template>

    <template #general>
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
          label="Email"
          required
          :error="form.errors.email"
        >
          <UInput
            v-model="form.email"
            :model-modifiers="{ string: true, trim: true }"
            type="email"
          />
        </UFormField>
      </UForm>
    </template>
  </FormModal>
</template>
