<script setup lang="ts">
import { update } from '@/actions/Laravel/Fortify/Http/Controllers/ProfileInformationController'
import AppNavbar from '@/components/Ui/AppNavbar.vue'
import type { User } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
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

useEcho<User>(`users.${props.user.id}`, '.user.updated', () => router.reload({ only: ['user'] }))
</script>

<template>
  <Head title="Settings" />

  <UDashboardPanel id="settings">
    <template #header>
      <AppNavbar />
    </template>

    <template #body>
      <UPage>
        <UForm
          :state="form"
          class="mx-auto flex w-full flex-col gap-6 sm:gap-9 lg:max-w-3xl lg:py-3"
          loading-auto
          @submit="onSubmit"
        >
          <UPageCard
            title="Default Settings"
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
        </UForm>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
