<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Playlists/Controllers/PlaylistController'
import PlaylistDeleteModal from '@/components/Playlists/PlaylistDeleteModal.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { PlaylistCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: PlaylistCollection
  types: SelectMenuItem[]
  type?: string | undefined
}>()

defineOptions({ layout: DashboardLayout })

const form = useForm('get', '', {
  type: props.type,
  page: 1,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'type'],
    reset: ['items'],
  })
</script>

<template>
  <Head title="Playlists" />

  <UDashboardPanel id="playlists">
    <template #header>
      <UDashboardNavbar title="Playlists">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar class="min-h-16">
        <template #right>
          <UFormField :error="form.errors.type">
            <USelect
              v-model="form.type"
              :items="types"
              placeholder="Select type"
              label-key="label"
              value-key="value"
              class="min-w-36"
              @update:modelValue="onSubmit"
            />
          </UFormField>
        </template>
      </UDashboardToolbar>
    </template>

    <template #body>
      <InfiniteScroll
        data="items"
        :buffer="200"
      >
        <UPageList divide>
          <UPageCard
            v-for="item in items?.data"
            :key="item.id"
            :to="edit.url(item.id)"
            variant="naked"
            class="py-4 first:pt-0 last:pb-0"
          >
            <div class="flex items-center justify-between">
              <UUser
                :name="item.resource?.name || item.resource?.label"
                :description="`${item.expires_at ? `Expires ${item.expires_at}` : 'No expiration'} • ${item.state.label}`"
                :avatar="{
                  alt: item.resource?.name || item.id,
                  class: 'rounded-sm size-14 me-1',
                }"
              />

              <div class="z-10 flex items-center gap-2">
                <PlaylistDeleteModal :item="item" />
              </div>
            </div>
          </UPageCard>
        </UPageList>
      </InfiniteScroll>
    </template>
  </UDashboardPanel>
</template>
