<script setup lang="ts">
import GroupClearModal from '@/components/Groups/GroupClearModal.vue'
import { useAuth } from '@/composables/auth'
import { update } from '@/routes/user-profile-information'
import type { CollectionItem } from '@/types'
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const { user, logOut } = useAuth()

const form = useForm(update(), {
  name: user.value?.name || '',
  email: user.value?.email || '',
})

const viewedHistory = computed<CollectionItem>(
  () => (usePage().props.history as CollectionItem | null | undefined) ?? { id: '', name: 'viewed', title: 'Viewed', type: 'viewed' },
)

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
    only: ['auth', 'user'],
  })

defineExpose({
  submit: onSubmit,
  get processing() {
    return form.processing
  },
  get recentlySuccessful() {
    return form.recentlySuccessful
  },
})
</script>

<template>
  <div class="flex flex-col gap-4">
    <UForm
      :state="form"
      class="flex flex-col py-3"
      loading-auto
      @submit="onSubmit"
    >
      <UPageCard
        title="Account"
        description="Update your name and email address."
        variant="naked"
        orientation="vertical"
        :ui="{
          body: 'flex w-full flex-col gap-3',
        }"
      >
        <template #body>
          <UFormField
            label="Name"
            required
            :error="form.errors.name"
          >
            <UInput
              v-model="form.name"
              :model-modifiers="{ string: true, trim: true }"
              autofocus
              autocapitalize="words"
            />
          </UFormField>

          <USeparator />

          <UFormField
            label="Email"
            required
            :error="form.errors.email"
          >
            <UInput
              v-model="form.email"
              :model-modifiers="{ string: true, trim: true }"
            />
          </UFormField>
        </template>
      </UPageCard>
    </UForm>

    <USeparator />

    <UPageCard
      title="Session"
      description="Log out of your account."
      variant="naked"
      orientation="vertical"
    >
      <template #footer>
        <UButton
          label="Logout"
          color="primary"
          variant="soft"
          @click="logOut"
        />
      </template>
    </UPageCard>

    <USeparator />

    <UPageCard
      title="Viewed history"
      description="Clear your watched videos and resume progress."
      variant="naked"
      orientation="vertical"
    >
      <template #footer>
        <GroupClearModal :item="viewedHistory">
          <UButton
            label="Clear history"
            icon="i-lucide-eraser"
            color="error"
            variant="soft"
          />
        </GroupClearModal>
      </template>
    </UPageCard>
  </div>
</template>
