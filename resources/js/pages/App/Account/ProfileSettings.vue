<script setup lang="ts">
import UserSettingsController from '@/actions/App/Api/Users/Controllers/UserSettingsController'
import AccountLayout from '@/layouts/App/AccountLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { AppearanceSettings, GeneralSettings, User } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  user: User
  general: GeneralSettings
  appearance: AppearanceSettings
}>()

defineOptions({ layout: [DefaultLayout, AccountLayout] })

const generalForm = useForm('patch', UserSettingsController.url(), {
  general: props.general,
})

const appearanceForm = useForm('patch', UserSettingsController.url(), {
  appearance: props.appearance,
})

useEcho<User>(`users.${props.user.id}`, '.user.updated', () =>
  router.reload({ only: ['user', 'general', 'appearance'] }),
)
</script>

<template>
  <Head title="Settings" />

  <UPageBody>
    <UForm
      :state="generalForm.general"
      class="flex flex-col py-3"
      @submit="generalForm.submit({ preserveScroll: true })"
    >
      <UPageCard
        title="General"
        description="Set your timezone, language, and date preferences."
        variant="subtle"
        orientation="vertical"
        :ui="{
          body: 'flex w-full flex-col gap-3',
        }"
      >
        <template #body>
          <UFormField label="Timezone">
            <UInput v-model="generalForm.general.timezone" />
          </UFormField>

          <USeparator />

          <UFormField label="Language">
            <UInput v-model="generalForm.general.language" />
          </UFormField>

          <USeparator />

          <UFormField label="Locale">
            <UInput v-model="generalForm.general.locale" />
          </UFormField>

          <USeparator />

          <div class="grid grid-cols-2 gap-3">
            <UFormField label="Date format">
              <UInput v-model="generalForm.general.date_format" />
            </UFormField>

            <UFormField label="Time format">
              <UInput v-model="generalForm.general.time_format" />
            </UFormField>
          </div>
        </template>

        <template #footer>
          <UButton
            label="Save changes"
            type="submit"
            color="primary"
            variant="soft"
          />
        </template>
      </UPageCard>
    </UForm>

    <UForm
      :state="appearanceForm.appearance"
      class="flex flex-col py-3"
      @submit="appearanceForm.submit({ preserveScroll: true })"
    >
      <UPageCard
        title="Appearance"
        description="Customise how the application looks and feels."
        variant="subtle"
        orientation="vertical"
        :ui="{
          body: 'flex w-full flex-col gap-3',
        }"
      >
        <template #body>
          <UFormField label="Theme">
            <USelect
              v-model="appearanceForm.appearance.theme"
              :options="[
                { label: 'Dark', value: 'dark' },
                { label: 'Light', value: 'light' },
                { label: 'System', value: 'system' },
              ]"
            />
          </UFormField>

          <USeparator />

          <UFormField label="Default view">
            <USelect
              v-model="appearanceForm.appearance.default_view"
              :options="[
                { label: 'Vertical', value: 'vertical' },
                { label: 'Horizontal', value: 'horizontal' },
                { label: 'Grid', value: 'grid' },
              ]"
            />
          </UFormField>
        </template>

        <template #footer>
          <UButton
            label="Save changes"
            type="submit"
            color="primary"
            variant="soft"
          />
        </template>
      </UPageCard>
    </UForm>
  </UPageBody>
</template>
