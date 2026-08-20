<script setup lang="ts">
import UserSettingsController from '@/actions/App/Web/Users/Controllers/UserSettingsController'
import { useEcho } from '@/composables/echo'
import type { User, UserSettings } from '@/types'
import { Head, router, useForm } from '@inertiajs/vue3'
import type { TabsItem } from '@nuxt/ui'

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

const tabs: TabsItem[] = [
  { label: 'General', icon: 'i-lucide-settings-2', slot: 'general' },
  { label: 'Appearance', icon: 'i-lucide-palette', slot: 'appearance' },
]

const fieldClass = 'flex max-sm:flex-col justify-between items-start gap-4'

privateChannel(`users.${props.user.id}`).listen('.user.updated', () => router.reload({ only: ['user'] }))
</script>

<template>
  <Head title="Settings" />

  <UPage>
    <UPageBody>
      <UForm
        id="settings"
        :state="form"
        @submit="onSubmit"
      >
        <UPageCard
          title="Settings"
          description="Manage your general preferences and appearance."
          variant="naked"
          orientation="horizontal"
          class="mb-4"
        >
          <UButton
            form="settings"
            label="Save changes"
            color="primary"
            variant="soft"
            type="submit"
            loading-auto
            class="w-fit lg:ms-auto"
          />
        </UPageCard>

        <UTabs
          :items="tabs"
          class="w-full"
        >
          <template #general>
            <UPageCard variant="subtle">
              <UFormField
                label="Timezone"
                description="Used to display dates and times throughout the app."
                name="general.timezone"
                :error="form.errors['general.timezone']"
                :class="fieldClass"
              >
                <USelect
                  v-model="form.general.timezone"
                  class="w-56"
                  :items="[
                    { label: 'UTC', value: 'UTC' },
                    { label: 'Europe/Amsterdam', value: 'Europe/Amsterdam' },
                  ]"
                />
              </UFormField>

              <USeparator />

              <UFormField
                label="Language"
                description="The language used across the interface."
                name="general.language"
                :error="form.errors['general.language']"
                :class="fieldClass"
              >
                <USelect
                  v-model="form.general.language"
                  class="w-56"
                  :items="[{ label: 'English', value: 'en' }]"
                />
              </UFormField>

              <USeparator />

              <UFormField
                label="Locale"
                description="Used to format numbers, dates, and currencies."
                name="general.locale"
                :error="form.errors['general.locale']"
                :class="fieldClass"
              >
                <USelect
                  v-model="form.general.locale"
                  class="w-56"
                  :items="[
                    { label: 'English (US)', value: 'en-US' },
                    { label: 'Dutch (Netherlands)', value: 'nl-NL' },
                  ]"
                />
              </UFormField>

              <USeparator />

              <UFormField
                label="Date format"
                description="How dates are displayed throughout the app."
                name="general.date_format"
                :error="form.errors['general.date_format']"
                :class="fieldClass"
              >
                <USelect
                  v-model="form.general.date_format"
                  class="w-56"
                  :items="[
                    { label: 'YYYY-MM-DD', value: 'YYYY-MM-DD' },
                    { label: 'MM/DD/YYYY', value: 'MM/DD/YYYY' },
                    { label: 'DD/MM/YYYY', value: 'DD/MM/YYYY' },
                    { label: 'DD.MM.YYYY', value: 'DD.MM.YYYY' },
                    { label: 'MMM D, YYYY', value: 'MMM D, YYYY' },
                  ]"
                />
              </UFormField>

              <USeparator />

              <UFormField
                label="Time format"
                description="How times are displayed throughout the app."
                name="general.time_format"
                :error="form.errors['general.time_format']"
                :class="fieldClass"
              >
                <USelect
                  v-model="form.general.time_format"
                  class="w-56"
                  :items="[
                    { label: '24-hour (HH:mm)', value: 'HH:mm' },
                    { label: '12-hour (h:mm A)', value: 'h:mm A' },
                    { label: '24-hour with seconds', value: 'HH:mm:ss' },
                    { label: '12-hour with seconds', value: 'h:mm:ss A' },
                  ]"
                />
              </UFormField>
            </UPageCard>
          </template>

          <template #appearance>
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
          </template>
        </UTabs>
      </UForm>
    </UPageBody>
  </UPage>
</template>
