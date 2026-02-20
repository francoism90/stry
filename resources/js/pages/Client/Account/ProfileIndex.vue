<script setup lang="ts">
import { update } from '@/actions/Laravel/Fortify/Http/Controllers/ProfileInformationController'
import AppNavbar from '@/components/Ui/AppNavbar.vue'
import { logout } from '@/routes'
import type { User } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  user: User
}>()

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

  <UDashboardPanel id="profile">
    <template #header>
      <AppNavbar />
    </template>

    <template #body>
      <UForm
        :state="form"
        class="mx-auto flex w-full flex-col gap-6 sm:gap-9 lg:max-w-3xl lg:py-3"
        loading-auto
        @submit="onSubmit"
      >
        <UPageCard
          title="Your Profile"
          variant="naked"
          orientation="horizontal"
        >
          <div class="flex items-center gap-2 lg:ms-auto">
            <UButton
              label="Save changes"
              type="submit"
              color="primary"
              variant="soft"
              loading-auto
            />
          </div>
        </UPageCard>

        <UPageCard variant="subtle">
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
        </UPageCard>

        <UPageCard
          title="Administration"
          description="Access the admin panel to manage videos, users, and settings."
          variant="subtle"
        >
          <template #footer>
            <UButton
              label="Administration"
              to="/admin"
              color="primary"
              variant="soft"
            />
          </template>
        </UPageCard>

        <UPageCard
          title="Session"
          description="Log out of your account."
          variant="subtle"
        >
          <template #footer>
            <UButton
              label="Logout"
              @click="onLogout"
              color="primary"
              variant="soft"
            />
          </template>
        </UPageCard>
      </UForm>
    </template>
  </UDashboardPanel>
</template>
