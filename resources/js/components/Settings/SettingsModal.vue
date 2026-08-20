<script setup lang="ts">
import SettingsAccountForm from '@/components/Settings/SettingsAccountForm.vue'
import SettingsAppearanceForm from '@/components/Settings/SettingsAppearanceForm.vue'
import SettingsGeneralForm from '@/components/Settings/SettingsGeneralForm.vue'
import SettingsSecurityForm from '@/components/Settings/SettingsSecurityForm.vue'
import { computed, ref, type Component } from 'vue'

type SettingsFormInstance = {
  submit: () => void
  processing: boolean
}

const open = defineModel<boolean>('open', { default: false })
const section = defineModel<string>('section', { default: 'account' })

const definitions: { value: string; label: string; icon: string; component: Component }[] = [
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
]

const sections = computed(() =>
  definitions.map((item) => ({
    ...item,
    active: item.value === section.value,
    onSelect: () => (section.value = item.value),
  })),
)

const activeComponent = computed(() => definitions.find((item) => item.value === section.value)?.component)

const formRef = ref<SettingsFormInstance | null>(null)
const saving = computed(() => formRef.value?.processing ?? false)
const save = () => formRef.value?.submit()
</script>

<template>
  <UModal
    v-model:open="open"
    title="Settings"
    :ui="{
      content: 'sm:max-w-3xl max-sm:h-full max-sm:max-w-full max-sm:rounded-none h-[min(85vh,640px)]',
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
        label="Save changes"
        color="primary"
        variant="soft"
        :loading="saving"
        @click="save"
      />
    </template>
  </UModal>
</template>
