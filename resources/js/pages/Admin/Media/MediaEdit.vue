<script setup lang="ts">
import { update } from '@/actions/App/Admin/Media/Controllers/MediaController'
import MediaDeleteModal from '@/components/Media/MediaDeleteModal.vue'
import MediaLayout from '@/layouts/Admin/MediaLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Media } from '@/types'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  media: Media
}>()

defineOptions({ layout: [DashboardLayout, MediaLayout] })

const form = useForm('put', update.url(props.media.id), {
  name: props.media.name,
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
      description="General information about the transcode"
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
        label="Name"
        :error="form.errors.name"
      >
        <UInput
          v-model="form.name"
          placeholder="Name of the media file"
        />
      </UFormField>
    </UPageCard>

    <UPageCard
      title="Delete media"
      description="Once you delete a media, there is no going back. Please be certain."
      class="from-error/10 to-default bg-linear-to-tl from-5%"
    >
      <template #footer>
        <MediaDeleteModal :item="media">
          <UButton
            label="Delete media"
            color="error"
            variant="soft"
          />
        </MediaDeleteModal>
      </template>
    </UPageCard>
  </UForm>
</template>
