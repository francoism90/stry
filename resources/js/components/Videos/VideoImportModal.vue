<script setup lang="ts">
import VideoImportController from '@/actions/App/Web/Videos/Controllers/VideoImportController'
import FormModal from '@/components/Ui/FormModal.vue'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

withDefaults(
  defineProps<{
    trigger?: boolean
  }>(),
  {
    trigger: true,
  },
)

const open = defineModel<boolean>('open')

const processing = ref(false)

const onSubmit = (close: () => void) => {
  processing.value = true

  router.post(
    VideoImportController.url(),
    {},
    {
      preserveScroll: true,
      onFinish: () => (processing.value = false),
      onSuccess: () => close(),
    },
  )
}
</script>

<template>
  <FormModal
    v-model:open="open"
    title="Import videos"
    submit-label="Import all"
    :processing="processing"
    @submit="onSubmit"
  >
    <template
      v-if="trigger"
      #default
    >
      <slot />
    </template>

    <template #body>
      <div class="flex flex-col gap-2">
        <h3>Are you sure you want to import all videos?</h3>
        <p class="text-sm text-muted">Files will be processed in the background.</p>
      </div>
    </template>
  </FormModal>
</template>
