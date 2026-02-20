<script setup lang="ts">
import { update } from '@/actions/App/Admin/Transcodes/Controllers/TranscodeController'
import TranscodeDeleteModal from '@/components/Transcodes/TranscodeDeleteModal.vue'
import TranscodeImportModal from '@/components/Transcodes/TranscodeImportModal.vue'
import TranscodeLayout from '@/layouts/Admin/TranscodeLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Transcode } from '@/types'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  transcode: Transcode
}>()

defineOptions({ layout: [DashboardLayout, TranscodeLayout] })

const form = useForm('put', update.url(props.transcode.id), {
  transcodable_type: props.transcode.resource?.type,
  transcodable_id: props.transcode.resource?.id,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
  })
</script>

<template>
  <UForm
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
        label="Transcodable Type"
        :error="form.errors.transcodable_type"
      >
        <UInput
          v-model="form.transcodable_type"
          :disabled="true"
        />
      </UFormField>

      <UFormField
        label="Transcodable ID"
        :error="form.errors.transcodable_id"
      >
        <UInput
          v-model="form.transcodable_id"
          :disabled="true"
        />
      </UFormField>
    </UPageCard>

    <UPageCard
      title="Import transcode"
      :description="`Current state: ${transcode.state.label}`"
    >
      <template #footer>
        <TranscodeImportModal :item="transcode">
          <UButton
            label="Import transcode"
            :disabled="!transcode.completed"
            color="primary"
            variant="soft"
          />
        </TranscodeImportModal>
      </template>
    </UPageCard>

    <UPageCard
      title="Delete transcode"
      description="Once you delete a transcode, there is no going back. Please be certain."
      class="from-error/10 to-default bg-linear-to-tl from-5%"
    >
      <template #footer>
        <TranscodeDeleteModal :item="transcode">
          <UButton
            label="Delete transcode"
            color="error"
            variant="soft"
          />
        </TranscodeDeleteModal>
      </template>
    </UPageCard>
  </UForm>
</template>
