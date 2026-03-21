<script setup lang="ts">
import { useSettings } from '@/composables/settings'
import AccountLayout from '@/layouts/App/AccountLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { User } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import { reactive } from 'vue'

const props = defineProps<{
  user: User
}>()

defineOptions({ layout: [DefaultLayout, AccountLayout] })

const { settings: general, update: updateGeneral } = useSettings('general')
const { settings: appearance, update: updateAppearance } = useSettings('appearance')

const generalForm = reactive({
  timezone: general.value?.timezone,
  locale: general.value?.locale,
  language: general.value?.language,
  date_format: general.value?.date_format,
  time_format: general.value?.time_format,
})

const appearanceForm = reactive({
  theme: appearance.value?.theme,
  default_view: appearance.value?.default_view,
})

useEcho<User>(`users.${props.user.id}`, '.user.updated', () => router.reload({ only: ['user', 'settings'] }))
</script>

<template>
  <Head title="Settings" />

  <UPageBody>
    <UForm
      :state="generalForm"
      class="flex flex-col py-3"
      @submit="updateGeneral(generalForm)"
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
            <UInput v-model="generalForm.timezone" />
          </UFormField>

          <USeparator />

          <UFormField label="Language">
            <UInput v-model="generalForm.language" />
          </UFormField>

          <USeparator />

          <UFormField label="Locale">
            <UInput v-model="generalForm.locale" />
          </UFormField>

          <USeparator />

          <div class="grid grid-cols-2 gap-3">
            <UFormField label="Date format">
              <UInput v-model="generalForm.date_format" />
            </UFormField>

            <UFormField label="Time format">
              <UInput v-model="generalForm.time_format" />
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
      :state="appearanceForm"
      class="flex flex-col py-3"
      @submit="updateAppearance(appearanceForm)"
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
              v-model="appearanceForm.theme"
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
              v-model="appearanceForm.default_view"
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
