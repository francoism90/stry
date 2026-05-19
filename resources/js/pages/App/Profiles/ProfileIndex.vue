<script setup lang="ts">
import SwitchProfileController from '@/actions/App/Api/Profiles/Controllers/SwitchProfileController'
import ProfileCreateModal from '@/components/Profiles/ProfileCreateModal.vue'
import ProfileFilters from '@/components/Profiles/ProfileFilters.vue'
import ProfileList from '@/components/Profiles/ProfileList.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { Profile, ProfileCollection } from '@/types'
import { Head, InfiniteScroll, router } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineProps<{
  profile: Profile | null
  items: ProfileCollection
  sorters: SelectMenuItem[]
  sort?: string
}>()

const switchProfile = (item: Profile) =>
  router.visit(SwitchProfileController(item.id), {
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
        <UDashboardToolbar>
          <template #left>
            <ProfileFilters
              :results="Boolean(items?.data?.length)"
              :sorters="sorters"
              :sort="sort"
            />
          </template>

          <template #right>
            <ProfileCreateModal />
          </template>
        </UDashboardToolbar>

        <InfiniteScroll
          data="items"
          items-element="#infinite-items"
          :buffer="200"
        >
          <ProfileList
            id="infinite-items"
            :items="items?.data"
            :current="profile"
            @switch-profile="switchProfile"
          />
        </InfiniteScroll>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
