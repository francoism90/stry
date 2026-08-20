<script setup lang="ts">
import UserSettingsController from '@/actions/App/Web/Users/Controllers/UserSettingsController'
import { settingsTabs } from '@/constants/settings'
import type { User, UserSettings } from '@/types'
import { Head, setLayoutProps, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  user: User
}>()

setLayoutProps({
  title: 'Settings',
  tabs: settingsTabs,
})

const form = useForm(UserSettingsController(), {
  general: (props.user.settings as UserSettings).general,
})

const onSubmit = () =>
  form.submit({
    preserveScroll: true,
    preserveState: true,
  })

const fieldClass = 'flex max-sm:flex-col justify-between items-start gap-4'
</script>

<template>
  <Head title="Settings" />

  <UPageBody>
    <UForm
      :state="form"
      loading-auto
      @submit="onSubmit"
    >
      <UPageCard
        title="General"
        description="Set your timezone, language, and date preferences."
        variant="naked"
        orientation="horizontal"
        class="mb-4"
      >
        <UButton
          label="Save changes"
          type="submit"
          color="primary"
          variant="soft"
          loading-auto
          class="w-fit lg:ms-auto"
        />
      </UPageCard>

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
    </UForm>
  </UPageBody>
</template>
