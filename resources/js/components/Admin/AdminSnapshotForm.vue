<script setup lang="ts">
import { show, update } from '@/actions/Modules/Web/Settings/Controllers/SnapshotSettingsController'
import type { SnapshotSettings } from '@/types'
import { useForm, useHttp } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'

const loaded = ref(false)
const http = useHttp<object, SnapshotSettings>({})

const frequencies = [
  { label: 'Never', value: 'never' },
  { label: 'Daily', value: 'daily' },
  { label: 'Weekly', value: 'weekly' },
  { label: 'Monthly', value: 'monthly' },
]

const form = useForm(update(), {
  frequency: 'never' as SnapshotSettings['frequency'],
  keep: 7,
})

onMounted(() =>
  http.get(show.url(), {
    onSuccess: (data) => {
      form.defaults({ ...data })
      form.reset()
      loaded.value = true
    },
  }),
)

const onSubmit = () => {
  if (!loaded.value) return

  form.submit({
    preserveScroll: true,
    preserveState: true,
  })
}

defineExpose({
  submit: onSubmit,
  get processing() {
    return form.processing
  },
  get recentlySuccessful() {
    return form.recentlySuccessful
  },
})

const fieldClass = 'flex max-sm:flex-col justify-between items-start gap-4'
</script>

<template>
  <div
    v-if="!loaded"
    class="flex flex-col gap-3"
  >
    <USkeleton
      v-for="i in 2"
      :key="i"
      class="h-10 w-full rounded-md"
    />
  </div>

  <UForm
    v-else
    :state="form"
    class="flex flex-col gap-4"
    loading-auto
    @submit="onSubmit"
  >
    <UPageCard
      title="Snapshots"
      description="Compressed dumps of the database, saved on the default disk for snapshots (see Storage). Restore one with snapshot:load."
      variant="naked"
      orientation="vertical"
      :ui="{
        body: 'flex w-full flex-col gap-3',
      }"
    >
      <template #body>
        <UFormField
          label="Frequency"
          description="How often a snapshot is created, at 04:30 (on Mondays for weekly, on the first of the month for monthly)."
          name="frequency"
          :error="form.errors.frequency"
          :class="fieldClass"
        >
          <USelect
            v-model="form.frequency"
            class="w-56"
            :items="frequencies"
          />
        </UFormField>

        <USeparator />

        <UFormField
          label="Snapshots to keep"
          description="Older snapshots are deleted after a new one is created."
          name="keep"
          :error="form.errors.keep"
          :class="fieldClass"
        >
          <UInputNumber
            v-model="form.keep"
            class="w-32"
            :min="1"
            :max="100"
          />
        </UFormField>
      </template>
    </UPageCard>
  </UForm>
</template>
