<script setup lang="ts">
import UserMenu from '@/components/Ui/UserMenu.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import { useGroups } from '@/composables/groups'
import type { FilterOption, Tag, VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: VideoCollection
  orders: SelectMenuItem[]
  filter: FilterOption
  tag?: Tag | undefined
  order?: string | undefined
  search?: string | null
}>()

const { clearGroup } = useGroups()

const form = useForm('get', '', {
  search: props.search,
  order: props.order,
  tag: props.tag?.id,
  page: 1,
})

const onSubmit = () => {
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'search', 'order', 'tag'],
    reset: ['items'],
  })
}

const clearTag = () => {
  form.tag = undefined
  onSubmit()
}

watchDebounced(
  () => form.search,
  () => onSubmit(),
  { debounce: 300, maxWait: 1000 },
)
</script>

<template>
  <Head :title="filter.label" />

  <UDashboardPanel id="feed">
    <template #header>
      <UDashboardNavbar
        :ui="{ root: 'gap-3 border-0', left: 'w-full' }"
        :toggle="{ variant: 'link', class: 'ps-0' }"
      >
        <template #left>
          <UFormField
            :error="form.errors.search"
            class="flex-1"
          >
            <UInput
              v-model="form.search"
              :model-modifiers="{ string: true, trim: true }"
              :placeholder="`Search ${filter.label}...`"
              variant="soft"
              size="xl"
              color="neutral"
              icon="i-lucide-search"
            />
          </UFormField>
        </template>

        <template #right>
          <UserMenu />
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar
        :ui="{
          root: 'min-h-4 border-0',
          left: 'gap-3 *:inline-flex *:items-center',
        }"
      >
        <template #left>
          <UFormField
            v-show="items.data?.length"
            orientation="horizontal"
            label="Sort by"
            :ui="{ label: 'text-secondary-400 text-xs' }"
            :error="form.errors.order"
          >
            <USelect
              v-model="form.order"
              :items="orders"
              :ui="{ content: 'min-w-36' }"
              label-key="label"
              value-key="value"
              variant="soft"
              size="sm"
              @update:modelValue="onSubmit"
            />
          </UFormField>

          <UFormField
            v-if="tag"
            orientation="horizontal"
            label="Tagged"
            :ui="{ label: 'text-secondary-400 text-xs' }"
            :error="form.errors.tag"
          >
            <UButton
              :label="tag.name"
              color="primary"
              size="xs"
              trailing-icon="i-lucide-x"
              @click.prevent="clearTag"
            />
          </UFormField>
        </template>

        <template #right>
          <UButton
            v-if="items.data?.length && filter.value !== 'all'"
            label="Clear List"
            size="xs"
            variant="link"
            @click.prevent="clearGroup(filter.value)"
          />
        </template>
      </UDashboardToolbar>
    </template>

    <template #body>
      <InfiniteScroll
        data="items"
        :buffer="200"
      >
        <VideoList :items="items?.data" />
      </InfiniteScroll>
    </template>
  </UDashboardPanel>
</template>
