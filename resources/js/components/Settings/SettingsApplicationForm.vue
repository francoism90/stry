<script setup lang="ts">
import ApplicationSettingsController from '@/actions/App/Web/Settings/Controllers/ApplicationSettingsController'
import type { ApplicationSettings } from '@/types'
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const settings = computed(() => usePage().props.settings)

const form = useForm<ApplicationSettings>(
  ApplicationSettingsController(),
  settings.value ?? {
    site_name: 'My Site',
    timezone: 'Europe/Amsterdam',
    default_locale: 'en',
    allow_registration: false,
    max_profiles_per_user: null,
    maintenance_message: null,
  },
)

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
      title="Application"
      description="Instance-wide settings that apply to everyone using this app."
      variant="naked"
      orientation="vertical"
      :ui="{
        body: 'flex w-full flex-col gap-3',
      }"
    >
      <template #body>
        <UFormField
          label="Site name"
          description="Shown in the browser tab and throughout the interface."
          name="site_name"
          :error="form.errors.site_name"
          :class="fieldClass"
        >
          <UInput
            v-model="form.site_name"
            class="w-56"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Timezone"
          description="The default timezone used when a user hasn't set their own."
          name="timezone"
          :error="form.errors.timezone"
          :class="fieldClass"
        >
          <USelect
            v-model="form.timezone"
            class="w-56"
            :items="[
              { label: 'UTC', value: 'UTC' },
              { label: 'Europe/Amsterdam', value: 'Europe/Amsterdam' },
            ]"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Default locale"
          description="The fallback locale used for new users and guests."
          name="default_locale"
          :error="form.errors.default_locale"
          :class="fieldClass"
        >
          <USelect
            v-model="form.default_locale"
            class="w-56"
            :items="[{ label: 'English', value: 'en' }]"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Allow registration"
          description="Let new users create an account themselves."
          name="allow_registration"
          :error="form.errors.allow_registration"
          :class="fieldClass"
        >
          <USwitch v-model="form.allow_registration" />
        </UFormField>

        <USeparator />

        <UFormField
          label="Max profiles per user"
          description="Leave empty for no limit."
          name="max_profiles_per_user"
          :error="form.errors.max_profiles_per_user"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.max_profiles_per_user"
            class="w-56"
            :min="1"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Maintenance message"
          description="Shown to users when the app is in maintenance mode."
          name="maintenance_message"
          :error="form.errors.maintenance_message"
          :class="fieldClass"
        >
          <UTextarea
            v-model="form.maintenance_message"
            :model-modifiers="{ nullable: true, string: true, trim: true }"
            class="w-56"
            :rows="3"
          />
        </UFormField>
      </template>
    </UPageCard>
  </UForm>
</template>
