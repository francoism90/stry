<script setup lang="ts">
import { update } from '@/actions/App/Admin/Users/Controllers/UserController'
import UserDeleteModal from '@/components/Users/UserDeleteModal.vue'
import UserLayout from '@/layouts/Admin/UserLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { User } from '@/types'
import { router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  user: User
}>()

defineOptions({ layout: [DashboardLayout, UserLayout] })

const form = useForm('put', update.url(props.user.id), {
  name: props.user.name,
  email: props.user.email,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
  })

useEcho<User>(`users.${props.user.id}`, '.user.updated', () => router.reload({ only: ['user'] }))
</script>

<template>
  <UForm
    id="general"
    :state="form"
    class="mx-auto flex w-full flex-col gap-6 sm:gap-9 lg:max-w-4xl lg:py-3"
    loading-auto
    @submit="onSubmit"
  >
    <UPageCard
      title="General Details"
      description="General information about the user"
      variant="naked"
      orientation="horizontal"
    >
      <div class="flex items-center gap-2 lg:ms-auto">
        <UButton
          form="general"
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
          type="email"
        />
      </UFormField>
    </UPageCard>

    <UPageCard
      title="Delete User"
      description="This will delete the user account. There is no going back. Please be certain."
      class="from-error/10 to-default bg-linear-to-tl from-5%"
    >
      <template #footer>
        <UserDeleteModal :item="user" />
      </template>
    </UPageCard>
  </UForm>
</template>
