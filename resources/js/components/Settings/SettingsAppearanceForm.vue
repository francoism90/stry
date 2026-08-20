<script setup lang="ts">
import UserSettingsController from '@/actions/App/Web/Users/Controllers/UserSettingsController'
import { useSettings } from '@/composables/settings'
import { useForm } from '@inertiajs/vue3'

const { settings } = useSettings('appearance')

const form = useForm(UserSettingsController(), {
  appearance: settings.value ?? {
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
  get recentlySuccessful() {
    return form.recentlySuccessful
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
      orientation="vertical"
      :ui="{
        body: 'flex w-full flex-col gap-3',
      }"
    >
      <template #body>
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
      </template>
    </UPageCard>
  </UForm>
</template>
