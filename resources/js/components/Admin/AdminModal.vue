<script setup lang="ts">
import AdminApplicationForm from '@/components/Admin/AdminApplicationForm.vue'
import AdminChapterForm from '@/components/Admin/AdminChapterForm.vue'
import AdminPlaylistForm from '@/components/Admin/AdminPlaylistForm.vue'
import { computed, ref, type Component } from 'vue'

type AdminFormInstance = {
  submit: () => void
  processing: boolean
  recentlySuccessful: boolean
}

type AdminSectionDefinition = {
  value: string
  label: string
  icon: string
  component: Component
}

const open = defineModel<boolean>('open', { default: false })
const section = defineModel<string>('section', { default: 'application' })

const definitions: AdminSectionDefinition[] = [
  {
    value: 'application',
    label: 'Application',
    icon: 'i-lucide-server',
    component: AdminApplicationForm,
  },
  {
    value: 'playlist',
    label: 'Playlist',
    icon: 'i-lucide-clapperboard',
    component: AdminPlaylistForm,
  },
  {
    value: 'chapters',
    label: 'Chapters',
    icon: 'i-lucide-list-video',
    component: AdminChapterForm,
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

const formRef = ref<AdminFormInstance | null>(null)
const saving = computed(() => formRef.value?.processing ?? false)
const saved = computed(() => formRef.value?.recentlySuccessful ?? false)
const save = () => formRef.value?.submit()
</script>

<template>
  <UModal
    v-model:open="open"
    title="Admin"
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
