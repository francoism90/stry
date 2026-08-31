<script setup lang="ts">
import SettingsAccountForm from '@/components/Settings/SettingsAccountForm.vue'
import SettingsApplicationForm from '@/components/Settings/SettingsApplicationForm.vue'
import SettingsAppearanceForm from '@/components/Settings/SettingsAppearanceForm.vue'
import SettingsGeneralForm from '@/components/Settings/SettingsGeneralForm.vue'
import SettingsSecurityForm from '@/components/Settings/SettingsSecurityForm.vue'
import { useAuth } from '@/composables/auth'
import { computed, ref, type Component } from 'vue'

const { hasRole } = useAuth()

type SettingsFormInstance = {
  submit: () => void
  processing: boolean
  recentlySuccessful: boolean
}

type SettingsSectionDefinition = {
  value: string
  label: string
  icon: string
  component: Component
}

const open = defineModel<boolean>('open', { default: false })
const section = defineModel<string>('section', { default: 'account' })

const definitions = computed<SettingsSectionDefinition[]>(() => [
  {
    value: 'account',
    label: 'Account',
    icon: 'i-lucide-user',
    component: SettingsAccountForm,
  },
  {
    value: 'security',
    label: 'Security',
    icon: 'i-lucide-shield',
    component: SettingsSecurityForm,
  },
  {
    value: 'general',
    label: 'General',
    icon: 'i-lucide-settings-2',
    component: SettingsGeneralForm,
  },
  {
    value: 'appearance',
    label: 'Appearance',
    icon: 'i-lucide-palette',
    component: SettingsAppearanceForm,
  },
  ...(hasRole('super-admin')
    ? [
        {
          value: 'application',
          label: 'Application',
          icon: 'i-lucide-server',
          component: SettingsApplicationForm,
        },
      ]
    : []),
])

const sections = computed(() =>
  definitions.value.map((item) => ({
    ...item,
    active: item.value === section.value,
    onSelect: () => (section.value = item.value),
  })),
)

const activeComponent = computed(() => definitions.value.find((item) => item.value === section.value)?.component)

const formRef = ref<SettingsFormInstance | null>(null)
const saving = computed(() => formRef.value?.processing ?? false)
const saved = computed(() => formRef.value?.recentlySuccessful ?? false)
const save = () => formRef.value?.submit()
</script>

<template>
  <UModal
    v-model:open="open"
    title="Settings"
    :ui="{
      content: 'h-[min(85vh,640px)] max-sm:h-full max-sm:max-w-full max-sm:rounded-none sm:max-w-3xl',
      body: 'flex-1 overflow-hidden p-0 sm:p-0',
      footer: 'justify-end',
    }"
  >
    <template #body>
      <div class="flex h-full flex-col gap-4 p-4 md:flex-row md:gap-6 md:p-6">
        <UNavigationMenu
          type="single"
          orientation="vertical"
          :items="sections"
          class="hidden w-56 shrink-0 md:block"
        />

        <USelectMenu
          v-model="section"
          :items="sections"
          value-key="value"
          label-key="label"
          class="w-full md:hidden"
        />

        <div class="min-w-0 flex-1 overflow-y-auto">
          <component
            :is="activeComponent"
            ref="formRef"
          />
        </div>
      </div>
    </template>

    <template #footer="{ close }">
      <UButton
        label="Cancel"
        color="neutral"
        variant="soft"
        @click="close"
      />

      <UButton
        :label="saved ? 'Saved' : 'Save changes'"
        :icon="saved ? 'i-lucide-check' : undefined"
        :color="saved ? 'success' : 'primary'"
        variant="soft"
        :loading="saving"
        @click="save"
      />
    </template>
  </UModal>
</template>
