<script setup lang="ts">
import AccountLayout from '@/layouts/App/AccountLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { update } from '@/routes/user-profile-information'
import type { AppearanceSettings, GeneralSettings, User } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  user: User
}>()

defineOptions({ layout: [DefaultLayout, AccountLayout] })

const form = useForm('put', update.url(), {
  general: props.user.settings?.general as GeneralSettings,
  appearance: props.user.settings?.appearance as AppearanceSettings,
})

const onSubmit = () =>
  form.submit({
    preserveScroll: true,
    preserveState: true,
  })

useEcho<User>(`users.${props.user.id}`, '.user.updated', () => router.reload({ only: ['user'] }))
</script>

<template>
  <Head title="Settings" />

  <UPageBody>
    <UForm
      :state="form"
      class="flex flex-col gap-6 py-3"
      loading-auto
      @submit="onSubmit"
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
            <UInput v-model="form.general.timezone" />
          </UFormField>

          <USeparator />

          <UFormField label="Language">
            <UInput v-model="form.general.language" />
          </UFormField>

          <USeparator />

          <UFormField label="Locale">
            <UInput v-model="form.general.locale" />
          </UFormField>

          <USeparator />

          <div class="grid grid-cols-2 gap-3">
            <UFormField label="Date format">
              <UInput v-model="form.general.date_format" />
            </UFormField>

            <UFormField label="Time format">
              <UInput v-model="form.general.time_format" />
            </UFormField>
          </div>
        </template>
      </UPageCard>

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
              v-model="form.appearance.theme"
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
              v-model="form.appearance.default_view"
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
            loading-auto
          />
        </template>
      </UPageCard>
    </UForm>
  </UPageBody>
</template>
