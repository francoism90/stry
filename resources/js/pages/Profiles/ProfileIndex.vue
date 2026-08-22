<script setup lang="ts">
import SwitchProfileController from '@/actions/App/Web/Profiles/Controllers/SwitchProfileController'
import ProfileFilterBar from '@/components/Profiles/ProfileFilterBar.vue'
import ProfileList from '@/components/Profiles/ProfileList.vue'
import ResourceLayout from '@/layouts/App/ResourceLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { Profile, ProfileCollection } from '@/types'
import { Head, InfiniteScroll, router, setLayoutProps } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineProps<{
  profile: Profile | null
  items: ProfileCollection
  sorters: SelectMenuItem[]
  sort?: string | null
}>()

defineOptions({
  layout: [AppLayout, ResourceLayout],
})

setLayoutProps({
  id: 'profiles',
  fluid: true,
})

const switchProfile = (item: Profile) =>
  router.visit(SwitchProfileController(item.id), {
    preserveScroll: true,
  })
</script>

<template>
  <Head title="Profiles" />

  <UPage>
    <UDashboardToolbar>
      <template #left>
        <ProfileFilterBar
          :results="Boolean(items?.data?.length)"
          :sorters="sorters"
          :sort="sort"
        />
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
