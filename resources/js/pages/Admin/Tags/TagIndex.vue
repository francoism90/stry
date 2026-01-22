<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Tags/Controllers/TagController'
import TagCreateModal from '@/components/Tags/TagCreateModal.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { TagCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: TagCollection
  types: SelectMenuItem[] | undefined
  search: string | undefined
  type: string | undefined
}>()

defineOptions({ layout: DashboardLayout })

const form = useForm('get', '', {
  search: props.search,
  type: props.type,
  page: 1,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'search', 'type'],
    reset: ['items'],
  })

watchDebounced(
  () => form.search,
  () => onSubmit(),
  { debounce: 300, maxWait: 1000 },
)
</script>

<template>
  <Head title="Tags" />

  <UDashboardPanel id="tags">
    <template #header>
      <UDashboardNavbar title="Tags">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>

        <template #right>
          <TagCreateModal :types="types" />
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar id="tag-header">
        <template #left>
          <UFormField :error="form.errors.search">
            <UInput
              v-model="form.search"
              :model-modifiers="{ string: true, trim: true }"
              color="neutral"
              placeholder="Search..."
              icon="i-lucide-search"
            />
          </UFormField>
        </template>

        <template #right>
          <UFormField :error="form.errors.type">
            <USelect
              v-model="form.type"
              :items="types"
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
        start-element="#tag-header"
        items-element="#tag-list"
        :buffer="200"
      >
        <UPageList
          id="tag-list"
          divide
        >
          <UPageCard
            v-for="item in items?.data"
            :key="item.id"
            :to="edit.url(item.id)"
            variant="naked"
            class="py-4 first:pt-0 last:pb-0"
          >
            <UUser
              :name="item.name"
              :description="`${item.category} • ${item.videos} videos`"
              :avatar="{
                alt: item.name,
                class: 'rounded-sm size-14 me-1',
              }"
            />
          </UPageCard>
        </UPageList>
      </InfiniteScroll>
    </template>
  </UDashboardPanel>
</template>
