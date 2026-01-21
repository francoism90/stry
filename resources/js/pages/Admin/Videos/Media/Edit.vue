<script setup lang="ts">
import { destroy, update } from '@/actions/App/Admin/Videos/Controllers/VideoMediaController'
import VideoLayout from '@/layouts/Admin/VideoLayout.vue'
import type { Media, Video } from '@/types'
import { router } from '@inertiajs/vue3'
import { useForm } from 'laravel-precognition-vue-inertia'
import { ref } from 'vue'

const props = defineProps<{
  video: Video
  media: Media
}>()

defineOptions({ layout: VideoLayout, name: 'VideoMediaEditPage' })

const toast = useToast()
const isDeleting = ref(false)

const form = useForm('put', update.url([props.video.id, props.media.id]), {
  name: props.media.name,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
    onSuccess: () =>
      toast.add({
        title: 'Success',
        description: 'The media has been updated.',
        icon: 'i-lucide-check',
        color: 'success',
      }),
  })

const onDelete = async () => {
  if (confirm('Are you sure you want to delete this media?')) {
    isDeleting.value = true
    router.delete(destroy.url([props.video.id, props.media.id]), {
      onFinish: () => {
        isDeleting.value = false
      },
    })
  }
}
</script>

<template>
  <UForm
    id="general"
    :state="form"
    class="mx-auto flex w-full flex-col gap-6 sm:gap-9 lg:max-w-3xl lg:py-3"
    loading-auto
    @submit="onSubmit"
  >
    <UPageCard
      :title="media.name"
      :description="`${media.mime_type} • ${media.file_size}`"
      variant="naked"
      orientation="horizontal"
    >
      <div class="ms-auto">
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
        required
        :error="form.errors.name"
      >
        <UInput
          v-model="form.name"
          :model-modifiers="{ string: true, trim: true }"
          autofocus
        />
      </UFormField>
    </UPageCard>

    <UPageCard
      title="Delete Media"
      description="This will delete the media file. There is no going back. Please be certain."
      class="from-error/10 to-default bg-linear-to-tl from-5%"
    >
      <template #footer>
        <UButton
          label="Delete Media"
          color="error"
          variant="soft"
          :loading="isDeleting"
          @click="onDelete"
        />
      </template>
    </UPageCard>
  </UForm>
</template>
