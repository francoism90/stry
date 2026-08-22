<script setup lang="ts">
import SwitchProfileController from '@/actions/App/Web/Profiles/Controllers/SwitchProfileController'
import ProfileList from '@/components/Profiles/ProfileList.vue'
import ContentLayout from '@/layouts/App/ContentLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { OptionItem, Profile, ProfileCollection, QueryFilter, QueryValue } from '@/types'
import { Head, InfiniteScroll, router, setLayoutProps } from '@inertiajs/vue3'

const props = defineProps<{
  profile: Profile | null
  items: ProfileCollection
  scopes?: OptionItem[]
  sorters?: OptionItem[]
  filter?: QueryFilter
  sort?: QueryValue
  query?: QueryValue
}>()

defineOptions({
  layout: [AppLayout, ContentLayout],
})

setLayoutProps({
  id: 'profiles.index',
  scopes: props.scopes,
  sorters: props.sorters,
  filter: props.filter,
  sort: props.sort,
  query: props.query,
})

const switchProfile = (item: Profile) =>
  router.visit(SwitchProfileController(item.id), {
    preserveScroll: true,
  })
</script>

<template>
  <Head title="Profiles" />

  <UPage>
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
