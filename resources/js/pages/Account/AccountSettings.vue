<script setup lang="ts">
import UserSettingsController from '@/actions/App/Web/Users/Controllers/UserSettingsController'
import { useEcho } from '@/composables/echo'
import type { User, UserSettings } from '@/types'
import { Head, router, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  user: User
}>()

const { privateChannel } = useEcho()

const form = useForm({
  ...(props.user.settings as UserSettings),
})

const onSubmit = () =>
  form.patch(UserSettingsController.url(), {
    preserveScroll: true,
    preserveState: true,
  })

privateChannel(`users.${props.user.id}`).listen('.user.updated', () => router.reload({ only: ['user'] }))
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
        title="Account Settings"
        description="Manage your general preferences and appearance."
        variant="subtle"
        orientation="vertical"
        :ui="{
          body: 'flex w-full flex-col gap-6',
        }"
      >
        <template #body>
          <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold text-highlighted">General</p>
            <p class="text-sm text-muted">Set your timezone, language, and date preferences.</p>
          </div>

          <div class="flex flex-col gap-4">
            <div class="grid grid-cols-3 gap-4">
              <UFormField
                label="Timezone"
                name="general.timezone"
                :error="form.errors['general.timezone']"
              >
                <USelect
                  v-model="form.general.timezone"
                  class="w-full"
                  :items="[
                    { label: 'UTC', value: 'UTC' },
                    { label: 'Europe/Amsterdam', value: 'Europe/Amsterdam' },
                  ]"
                />
              </UFormField>

              <UFormField
                label="Language"
                name="general.language"
                :error="form.errors['general.language']"
              >
                <USelect
                  v-model="form.general.language"
                  class="w-full"
                  :items="[{ label: 'English', value: 'en' }]"
                />
              </UFormField>

              <UFormField
                label="Locale"
                name="general.locale"
                :error="form.errors['general.locale']"
              >
                <USelect
                  v-model="form.general.locale"
                  class="w-full"
                  :items="[
                    { label: 'English (US)', value: 'en-US' },
                    { label: 'Dutch (Netherlands)', value: 'nl-NL' },
                  ]"
                />
              </UFormField>
            </div>

            <USeparator />

            <div class="grid grid-cols-2 gap-4">
              <UFormField
                label="Date format"
                name="general.date_format"
                :error="form.errors['general.date_format']"
              >
                <USelect
                  v-model="form.general.date_format"
                  class="w-full"
                  :items="[
                    { label: 'YYYY-MM-DD', value: 'YYYY-MM-DD' },
                    { label: 'MM/DD/YYYY', value: 'MM/DD/YYYY' },
                    { label: 'DD/MM/YYYY', value: 'DD/MM/YYYY' },
                    { label: 'DD.MM.YYYY', value: 'DD.MM.YYYY' },
                    { label: 'MMM D, YYYY', value: 'MMM D, YYYY' },
                  ]"
                />
              </UFormField>

              <UFormField
                label="Time format"
                name="general.time_format"
                :error="form.errors['general.time_format']"
              >
                <USelect
                  v-model="form.general.time_format"
                  class="w-full"
                  :items="[
                    { label: '24-hour (HH:mm)', value: 'HH:mm' },
                    { label: '12-hour (h:mm A)', value: 'h:mm A' },
                    { label: '24-hour with seconds', value: 'HH:mm:ss' },
                    { label: '12-hour with seconds', value: 'h:mm:ss A' },
                  ]"
                />
              </UFormField>
            </div>
          </div>

          <USeparator />

          <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold text-highlighted">Appearance</p>
            <p class="text-sm text-muted">Customize how the application looks and feels.</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <UFormField
              label="Theme"
              name="appearance.theme"
              :error="form.errors['appearance.theme']"
            >
              <USelect
                v-model="form.appearance.theme"
                class="w-full"
                :items="[
                  { label: 'Dark', value: 'dark' },
                  { label: 'Light', value: 'light' },
                  { label: 'System', value: 'system' },
                ]"
              />
            </UFormField>

            <UFormField
              label="Default view"
              name="appearance.default_view"
              :error="form.errors['appearance.default_view']"
            >
              <USelect
                v-model="form.appearance.default_view"
                class="w-full"
                :items="[
                  { label: 'Vertical', value: 'vertical' },
                  { label: 'Horizontal', value: 'horizontal' },
                  { label: 'Grid', value: 'grid' },
                ]"
              />
            </UFormField>
          </div>
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
