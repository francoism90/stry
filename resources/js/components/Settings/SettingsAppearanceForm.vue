<script setup lang="ts">
import UserSettingsController from '@/actions/App/Web/Users/Controllers/UserSettingsController'
import { useAuth } from '@/composables/auth'
import type { UserSettings } from '@/types'
import { useForm } from '@inertiajs/vue3'

const { user } = useAuth()

const form = useForm(UserSettingsController(), {
  appearance: (user.value?.settings as UserSettings | undefined)?.appearance ?? {
    theme: 'system',
    default_view: 'grid',
  },
})

const onSubmit = () =>
  form.submit({
    preserveScroll: true,
    preserveState: true,
  })

defineExpose({
  submit: onSubmit,
  get processing() {
    return form.processing
  },
})

const fieldClass = 'flex max-sm:flex-col justify-between items-start gap-4'
</script>

<template>
  <UForm
    :state="form"
    loading-auto
    @submit="onSubmit"
  >
    <UPageCard
      title="Appearance"
      description="Customize how the application looks and feels."
      variant="naked"
      orientation="horizontal"
      class="mb-4"
    />

    <UPageCard variant="subtle">
      <UFormField
        label="Theme"
        description="Choose how the interface looks."
        name="appearance.theme"
        :error="form.errors['appearance.theme']"
        :class="fieldClass"
      >
        <USelect
          v-model="form.appearance.theme"
          class="w-56"
          :items="[
            { label: 'Dark', value: 'dark' },
            { label: 'Light', value: 'light' },
            { label: 'System', value: 'system' },
          ]"
        />
      </UFormField>

      <USeparator />

      <UFormField
        label="Default view"
        description="The default layout used for browsing your library."
        name="appearance.default_view"
        :error="form.errors['appearance.default_view']"
        :class="fieldClass"
      >
        <USelect
          v-model="form.appearance.default_view"
          class="w-56"
          :items="[
            { label: 'Vertical', value: 'vertical' },
            { label: 'Horizontal', value: 'horizontal' },
            { label: 'Grid', value: 'grid' },
          ]"
        />
      </UFormField>
    </UPageCard>
  </UForm>
</template>
