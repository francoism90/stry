<script setup lang="ts">
import SettingsAccountForm from '@/components/Settings/SettingsAccountForm.vue'
import SettingsAppearanceForm from '@/components/Settings/SettingsAppearanceForm.vue'
import SettingsGeneralForm from '@/components/Settings/SettingsGeneralForm.vue'
import SettingsSecurityForm from '@/components/Settings/SettingsSecurityForm.vue'
import { computed, type Component } from 'vue'

const open = defineModel<boolean>('open', { default: false })
const section = defineModel<string>('section', { default: 'account' })

const sections: { value: string; label: string; icon: string; component: Component }[] = [
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

const activeComponent = computed(() => sections.find((item) => item.value === section.value)?.component)
</script>

<template>
  <UModal
    v-model:open="open"
    title="Settings"
    :ui="{
      content: 'sm:max-w-3xl max-sm:h-full max-sm:max-w-full max-sm:rounded-none h-[min(85vh,640px)]',
      body: 'flex-1 overflow-hidden p-0 sm:p-0',
    }"
  >
    <template #body>
      <div class="flex h-full flex-col gap-4 p-4 md:flex-row md:gap-6 md:p-6">
        <UNavigationMenu
          v-model="section"
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
          <component :is="activeComponent" />
        </div>
      </div>
    </template>
  </UModal>
</template>
