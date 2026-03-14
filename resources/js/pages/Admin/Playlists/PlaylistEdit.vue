<script setup lang="ts">
import { update } from '@/actions/App/Web/Playlists/Controllers/PlaylistController'
import PlaylistDeleteModal from '@/components/Playlists/PlaylistDeleteModal.vue'
import { useDateTime } from '@/composables/datetime'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Playlist } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { CalendarDateTime } from '@internationalized/date'
import type { SelectMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'
import { computed } from 'vue'

const props = defineProps<{
  playlist: Playlist
  types: SelectMenuItem[]
}>()

defineOptions({ layout: DashboardLayout })

const { toDateTime, fromDateTime } = useDateTime()

const form = useForm('put', update.url(props.playlist.id), {
  type: props.playlist.type || null,
  expires_at: props.playlist.expires_at || null,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
  })

const expiresAt = computed({
  get: () => toDateTime(form.expires_at),
  set: (value: CalendarDateTime | null) => {
    form.expires_at = fromDateTime(value)
  },
})
</script>

<template>
  <Head title="Edit playlist" />

  <UPageBody>
    <UForm
      :state="form"
      class="flex flex-col py-3"
      loading-auto
      @submit="onSubmit"
    >
      <UPageCard
        variant="subtle"
        orientation="vertical"
        :ui="{
          body: 'flex w-full flex-col gap-3',
        }"
      >
        <template #body>
          <UFormField
            label="Type"
            :error="form.errors.type"
          >
            <USelect
              v-model="form.type"
              :items="types"
              label-key="label"
              value-key="value"
            />
          </UFormField>

          <USeparator />

          <UFormField
            label="Expires at"
            :error="form.errors.expires_at"
          >
            <UInputDate
              v-model="expiresAt"
              granularity="second"
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
      variant="subtle"
      orientation="vertical"
      :ui="{
        root: 'ring-error/25 from-error/5 bg-linear-to-r to-transparent',
        body: 'flex flex-col gap-3',
      }"
    >
      <template #body>
        <div class="flex flex-col gap-2">
          <p class="text-error text-sm font-semibold">Delete playlist</p>
          <p class="text-muted text-sm">Permanently remove this playlist and all associated data.</p>

          <PlaylistDeleteModal :item="playlist">
            <UButton
              label="Delete playlist"
              icon="i-lucide-trash"
              color="error"
              variant="soft"
              size="sm"
              class="w-fit"
            />
          </PlaylistDeleteModal>
        </div>
      </template>
    </UPageCard>
  </UPageBody>
</template>
