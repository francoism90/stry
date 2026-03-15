<script setup lang="ts">
import { update } from '@/actions/Laravel/Fortify/Http/Controllers/ProfileInformationController'
import AccountLayout from '@/layouts/App/AccountLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { logout } from '@/routes'
import type { User } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  user: User
}>()

defineOptions({ layout: [DefaultLayout, AccountLayout] })

const form = useForm('put', update.url(), {
  name: props.user.name || '',
  email: props.user.email || '',
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
  })

const onLogout = () => router.post(logout.url())
</script>

<template>
  <Head title="Profile" />

  <UPageBody>
    <UForm
      :state="form"
      class="flex flex-col gap-4 py-3"
      loading-auto
      @submit="onSubmit"
    >
      <UPageCard
        variant="subtle"
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

    <UPageCard
      title="Session"
      description="Log out of your account."
      variant="subtle"
      orientation="vertical"
    >
      <template #footer>
        <UButton
          label="Logout"
          color="primary"
          variant="soft"
          @click="onLogout"
        />
      </template>
    </UPageCard>
  </UPageBody>
</template>
