<script setup lang="ts">
import { update } from '@/actions/App/Admin/Transcodes/Controllers/TranscodeController'
import TranscodeDeleteModal from '@/components/Transcodes/TranscodeDeleteModal.vue'
import TranscodeLayout from '@/layouts/Admin/TranscodeLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Transcode } from '@/types'
import type { SelectMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  transcode: Transcode
  encoders: SelectMenuItem[]
}>()

defineOptions({ layout: [DashboardLayout, TranscodeLayout] })

const form = useForm('put', update.url(props.transcode.id), {
  encoder: props.transcode.encoder,
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
        label="Encoder"
        :error="form.errors.encoder"
      >
        <USelectMenu
          v-model="form.encoder"
          value-key="value"
          :items="encoders"
          class="w-full"
        />
      </UFormField>
    </UPageCard>

    <UPageCard
      title="Delete transcode"
      description="Once you delete a transcode, there is no going back. Please be certain."
      class="from-error/10 to-default bg-linear-to-tl from-5%"
    >
      <template #footer>
        <TranscodeDeleteModal :item="transcode" />
      </template>
    </UPageCard>
  </UForm>
</template>
