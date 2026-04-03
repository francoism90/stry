<script setup lang="ts">
import { destroy, store, update } from '@/actions/App/Web/Profiles/Controllers/ProfileController'
import SwitchProfileController from '@/actions/App/Web/Profiles/Controllers/SwitchProfileController'
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { Profile, ProfileCollection } from '@/types'
import { Head, InfiniteScroll, router, useForm } from '@inertiajs/vue3'

defineProps<{
  profiles: ProfileCollection
}>()

const createForm = useForm(store(), {
  name: '',
  is_kids: false,
  is_primary: false,
})

const createProfile = (close: () => void) =>
  createForm.submit({
    preserveScroll: true,
    onSuccess: () => {
      createForm.reset()
      close()
    },
  })

const editProfile = (profile: Profile) => {
  const name = window.prompt('Profile name', profile.name)?.trim()

  if (!name) {
    return
  }

  router.visit(update(profile.id), {
    preserveScroll: true,
    data: {
      name,
      is_kids: profile.is_kids,
      is_primary: profile.is_primary,
    },
  })
}

const deleteProfile = (profile: Profile) => {
  if (!window.confirm(`Delete profile "${profile.name}"?`)) {
    return
  }

  router.visit(destroy(profile.id), {
    preserveScroll: true,
  })
}

const switchProfile = (profile: Profile) =>
  router.visit(SwitchProfileController(profile.id), {
    preserveScroll: true,
  })
</script>

<template>
  <Head title="Profiles" />

  <UDashboardPanel id="profiles">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage>
        <UPageHeader
          title="Profiles"
          description="Choose who is watching."
        >
          <template #links>
            <UModal
              title="Create profile"
              :ui="{ footer: 'justify-end' }"
            >
              <UButton
                label="Create profile"
                icon="i-lucide-plus"
                color="neutral"
                variant="soft"
              />

              <template #body>
                <UForm
                  :state="createForm"
                  class="flex flex-col gap-4"
                >
                  <UFormField
                    label="Name"
                    required
                    :error="createForm.errors.name"
                  >
                    <UInput
                      v-model="createForm.name"
                      :model-modifiers="{ string: true, trim: true }"
                      autofocus
                    />
                  </UFormField>

                  <UFormField
                    label="Kids profile"
                    :error="createForm.errors.is_kids"
                  >
                    <USwitch v-model="createForm.is_kids" />
                  </UFormField>

                  <UFormField
                    label="Primary profile"
                    :error="createForm.errors.is_primary"
                  >
                    <USwitch v-model="createForm.is_primary" />
                  </UFormField>
                </UForm>
              </template>

              <template #footer="{ close }">
                <UButton
                  label="Cancel"
                  color="neutral"
                  variant="soft"
                  @click.prevent="close"
                />

                <UButton
                  label="Create"
                  color="primary"
                  variant="soft"
                  loading-auto
                  @click.prevent="createProfile(close)"
                />
              </template>
            </UModal>
          </template>
        </UPageHeader>

        <UPageBody>
          <div
            v-if="!profiles?.data?.length"
            class="flex flex-col items-center justify-center gap-3 py-24 text-center"
          >
            <UIcon
              name="i-lucide-users"
              class="text-muted size-10"
            />
            <p class="font-semibold">No profiles yet</p>
            <p class="text-muted text-sm">Create a profile to personalize watch history and recommendations.</p>
          </div>

          <InfiniteScroll
            v-else
            data="profiles"
            :buffer="200"
          >
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <UPageCard
                v-for="profile in profiles?.data"
                :key="profile.id"
                variant="subtle"
                :ui="{ body: 'flex items-center gap-3', footer: 'flex items-center justify-between' }"
              >
                <template #body>
                  <UAvatar
                    :src="profile.avatar ?? undefined"
                    :alt="profile.name"
                    size="lg"
                  />

                  <div class="flex min-w-0 flex-col gap-1">
                    <p class="truncate font-semibold">{{ profile.name }}</p>

                    <div class="flex items-center gap-2">
                      <UBadge
                        color="neutral"
                        variant="soft"
                        size="sm"
                      >
                        {{ profile.state.label }}
                      </UBadge>

                      <UBadge
                        v-if="profile.is_primary"
                        color="primary"
                        variant="soft"
                        size="sm"
                      >
                        Primary
                      </UBadge>

                      <UBadge
                        v-if="profile.is_kids"
                        color="warning"
                        variant="soft"
                        size="sm"
                      >
                        Kids
                      </UBadge>
                    </div>
                  </div>
                </template>

                <template #footer>
                  <div class="flex items-center gap-2">
                    <UButton
                      icon="i-lucide-pencil"
                      color="neutral"
                      variant="ghost"
                      size="sm"
                      @click="editProfile(profile)"
                    />

                    <UButton
                      icon="i-lucide-trash-2"
                      color="error"
                      variant="ghost"
                      size="sm"
                      @click="deleteProfile(profile)"
                    />
                  </div>

                  <UButton
                    :label="profile.is_current ? 'Current' : 'Switch'"
                    color="neutral"
                    variant="soft"
                    size="sm"
                    :disabled="profile.is_current"
                    @click="switchProfile(profile)"
                  />
                </template>
              </UPageCard>
            </div>
          </InfiniteScroll>
        </UPageBody>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
