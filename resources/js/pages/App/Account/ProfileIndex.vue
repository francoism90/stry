<script setup lang="ts">
import { update } from '@/actions/Laravel/Fortify/Http/Controllers/ProfileInformationController'
import AccountLayout from '@/layouts/App/AccountLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { logout } from '@/routes'
import type { User } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useForm } from 'laravel-precognition-vue-inertia'
import { computed } from 'vue'

const props = defineProps<{
  user: User
}>()

defineOptions({ layout: [DefaultLayout, AccountLayout] })

const onLogout = () => router.post(logout.url())

const form = useForm('put', update.url(), {
  name: props.user.name || '',
  email: props.user.email || '',
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
  })

const memberSince = computed(() =>
  new Date(props.user.created_at).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' }),
)
</script>

<template>
  <Head title="Profile" />

  <UPageBody>
    <UPageCard
      variant="subtle"
      orientation="vertical"
    >
      <template #body>
        <div class="flex items-center gap-4">
          <UAvatar
            :src="user.avatar ?? undefined"
            :alt="user.name"
            size="xl"
          />
          <div class="flex flex-col gap-0.5">
            <p class="text-highlighted text-base font-semibold">{{ user.name }}</p>
            <p class="text-muted text-sm">{{ user.email }}</p>
          </div>
        </div>
      </template>
    </UPageCard>

    <UPageCard
      variant="subtle"
      orientation="vertical"
    >
      <template #body>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
          <div class="flex flex-col gap-1">
            <p class="text-muted text-xs font-medium tracking-wide uppercase">Member since</p>
            <p class="text-highlighted text-sm">{{ memberSince }}</p>
          </div>

          <div class="flex flex-col gap-1">
            <p class="text-muted text-xs font-medium tracking-wide uppercase">Email</p>
            <div class="flex items-center gap-1.5">
              <UIcon
                :name="user.email_verified_at ? 'i-lucide-circle-check' : 'i-lucide-circle-x'"
                :class="user.email_verified_at ? 'text-success' : 'text-error'"
                class="size-4 shrink-0"
              />
              <p class="text-highlighted text-sm">{{ user.email_verified_at ? 'Verified' : 'Unverified' }}</p>
            </div>
          </div>

          <div
            v-if="user.videos_count !== undefined"
            class="flex flex-col gap-1"
          >
            <p class="text-muted text-xs font-medium tracking-wide uppercase">Videos uploaded</p>
            <p class="text-highlighted text-sm">{{ user.videos_count.toLocaleString() }}</p>
          </div>
        </div>
      </template>
    </UPageCard>

    <UForm
      :state="form"
      class="flex flex-col py-3"
      loading-auto
      @submit="onSubmit"
    >
      <UPageCard
        title="Profile"
        description="Update your name and email address."
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
