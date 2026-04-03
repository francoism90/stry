<script setup lang="ts">
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { ModelState } from '@/types'
import { Head, router } from '@inertiajs/vue3'

type ProfileItem = {
  id: string
  name: string
  avatar: string | null
  is_kids: boolean
  is_primary: boolean
  is_current: boolean
  state: ModelState
}

defineProps<{
  profiles: ProfileItem[]
}>()
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
        />

        <UPageBody>
          <div
            v-if="profiles.length === 0"
            class="flex flex-col items-center justify-center gap-3 py-24 text-center"
          >
            <UIcon
              name="i-lucide-users"
              class="text-muted size-10"
            />
            <p class="font-semibold">No profiles yet</p>
            <p class="text-muted text-sm">Create a profile to personalize watch history and recommendations.</p>
          </div>

          <div
            v-else
            class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
          >
            <UPageCard
              v-for="profile in profiles"
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
                <span class="text-muted text-sm">Switcher ready</span>
                <UButton
                  :label="profile.is_current ? 'Current' : 'Switch'"
                  color="neutral"
                  variant="soft"
                  size="sm"
                  :disabled="profile.is_current"
                  @click="router.post(`/profiles/${profile.id}/switch`)"
                />
              </template>
            </UPageCard>
          </div>
        </UPageBody>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
