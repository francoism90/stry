<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Transcodes/Controllers/TranscodeController'
import TranscodeDeleteModal from '@/components/Transcodes/TranscodeDeleteModal.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { TranscodeCollection } from '@/types'
import { Head, InfiniteScroll, usePoll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: TranscodeCollection
  encoders: SelectMenuItem[]
  encoder?: string | undefined
}>()

defineOptions({ layout: DashboardLayout })

usePoll(30000, {
  only: ['items'],
  reset: ['items'],
})

const form = useForm('get', '', {
  encoder: props.encoder,
  page: 1,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'encoder'],
    reset: ['items'],
  })
</script>

<template>
  <Head title="Transcodes" />

  <UDashboardPanel id="transcodes">
    <template #header>
      <UDashboardNavbar title="Transcodes">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar class="min-h-16">
        <template #right>
          <UFormField :error="form.errors.encoder">
            <USelect
              v-model="form.encoder"
              :items="encoders"
              placeholder="Select encoder"
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
                :description="`${item.file_size} • ${item.state.label}`"
                :avatar="{
                  alt: item.resource?.name || item.id,
                  class: 'rounded-sm size-14 me-1',
                }"
              />

              <div class="z-10 flex items-center gap-2">
                <TranscodeDeleteModal :item="item" />
              </div>
            </div>
          </UPageCard>
        </UPageList>
      </InfiniteScroll>
    </template>
  </UDashboardPanel>
</template>
