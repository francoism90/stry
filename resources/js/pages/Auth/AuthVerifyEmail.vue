<script setup lang="ts">
import { useAuth } from '@/composables/auth'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  action: string
  status?: string
}>()

const { logOut } = useAuth()

const form = useForm('post', props.action, {})

const onSubmit = () => form.submit()
</script>

<template>
  <Head title="Verify email" />

  <UPageCard
    title="Verify your email"
    description="We've sent a verification link to your email address. Click it to continue."
    class="sm:min-w-md"
  >
    <UAlert
      v-if="status === 'verification-link-sent'"
      title="A new verification link has been sent to your email address."
      color="success"
      variant="subtle"
    />

    <div class="flex flex-col gap-2">
      <UButton
        label="Resend verification email"
        class="justify-center"
        :loading="form.processing"
        @click="onSubmit"
      />

      <UButton
        label="Log out"
        color="neutral"
        variant="ghost"
        class="justify-center"
        @click="logOut"
      />
    </div>
  </UPageCard>
</template>
