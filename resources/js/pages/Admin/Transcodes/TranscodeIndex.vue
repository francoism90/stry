<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Transcodes/Controllers/TranscodeController'
import TranscodeCreateModal from '@/components/Transcodes/TranscodeCreateModal.vue'
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

usePoll(5000, {
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

        <template #right>
          <div class="flex items-center gap-2">
            <TranscodeCreateModal />
          </div>
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar
        id="transcode-header"
        class="min-h-16"
      >
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
        start-element="#transcode-header"
        items-element="#transcode-list"
        :buffer="200"
      >
        <UPageList
          id="transcode-list"
          divide
        >
          <UPageCard
            v-for="item in items?.data"
            :key="item.id"
            :to="edit.url(item.id)"
            variant="naked"
            class="py-4 first:pt-0 last:pb-0"
          >
            <div class="flex items-center justify-between">
              <UUser
                :name="item.id"
                :description="`${item.encoder} • ${item.state.name}`"
                :avatar="{
                  alt: item.id,
                  class: 'rounded-sm size-14 me-1',
                }"
              />

              <div class="z-10 flex items-center gap-2">
                <TranscodeDeleteModal :item="item">
                  <UButton
                    icon="i-lucide-trash"
                    color="error"
                    variant="ghost"
                    size="sm"
                  />
                </TranscodeDeleteModal>
              </div>
            </div>
          </UPageCard>
        </UPageList>
      </InfiniteScroll>
    </template>
  </UDashboardPanel>
</template>
