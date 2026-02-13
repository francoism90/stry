<script setup lang="ts">
import { update } from '@/actions/App/Admin/Playlists/Controllers/PlaylistController'
import PlaylistDeleteModal from '@/components/Playlists/PlaylistDeleteModal.vue'
import PlaylistLayout from '@/layouts/Admin/PlaylistLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Playlist } from '@/types'
import type { SelectMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  playlist: Playlist
  types: SelectMenuItem[]
}>()

defineOptions({ layout: [DashboardLayout, PlaylistLayout] })

const form = useForm('put', update.url(props.playlist.id), {
  type: props.playlist.type,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
  })
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
      description="General information about the playlist"
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
        label="Type"
        :error="form.errors.type"
      >
        <USelectMenu
          v-model="form.type"
          value-key="value"
          :items="types"
          class="w-full"
        />
      </UFormField>
    </UPageCard>

    <UPageCard
      title="Delete playlist"
      description="Once you delete a playlist, there is no going back. Please be certain."
      class="from-error/10 to-default bg-linear-to-tl from-5%"
    >
      <template #footer>
        <PlaylistDeleteModal :item="playlist" />
      </template>
    </UPageCard>
  </UForm>
</template>
