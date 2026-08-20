<script setup lang="ts">
import AppearanceController from '@/actions/App/Web/Account/Controllers/AppearanceController'
import SettingsController from '@/actions/App/Web/Account/Controllers/SettingsController'
import UserSettingsController from '@/actions/App/Web/Users/Controllers/UserSettingsController'
import type { User, UserSettings } from '@/types'
import { Head, setLayoutProps, useForm } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'

const props = defineProps<{
  user: User
}>()

setLayoutProps({
  title: 'Settings',
  tabs: [
    {
      label: 'General',
      icon: 'i-lucide-settings-2',
      to: SettingsController.url(),
    },
    {
      label: 'Appearance',
      icon: 'i-lucide-palette',
      to: AppearanceController.url(),
    },
  ] satisfies NavigationMenuItem[],
})

const form = useForm(UserSettingsController(), {
  appearance: (props.user.settings as UserSettings).appearance,
})

const onSubmit = () =>
  form.submit({
    preserveScroll: true,
    preserveState: true,
  })

const fieldClass = 'flex max-sm:flex-col justify-between items-start gap-4'
</script>

<template>
  <Head title="Appearance" />

  <UPageBody>
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
  </UPageBody>
</template>
