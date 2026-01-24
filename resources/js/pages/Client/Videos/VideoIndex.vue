<script setup lang="ts">
import UserMenu from '@/components/Ui/UserMenu.vue'
import VideoList from '@/components/Videos/VideoList.vue'
import { useVideos } from '@/composables/videos'
import type { FilterOption, Tag, VideoCollection } from '@/types'
import { Head, InfiniteScroll, router } from '@inertiajs/vue3'
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

const toast = useToast()
const { clearGroup } = useVideos()

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

router.on('flash', (event) => {
  if (event.detail.flash) {
    toast.add({
      title: 'Videos',
      description: event.detail.flash.message as string,
      icon: 'i-lucide-info',
      color: 'success',
    })
  }
})

watchDebounced(
  () => form.search,
  () => onSubmit(),
  { debounce: 300, maxWait: 1000 },
)
</script>

<template>
  <Head :title="filter.label" />

  <UDashboardPanel id="library">
    <template #header>
      <UDashboardNavbar
        :ui="{ root: 'h-20 gap-3 border-0', left: 'w-full' }"
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
        id="video-header"
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
            label="Clear List"
            size="xs"
            variant="ghost"
            @click.prevent="clearGroup(filter.value)"
          />
        </template>
      </UDashboardToolbar>
    </template>

    <template #body>
      <InfiniteScroll
        data="items"
        start-element="#video-header"
        items-element="#video-list"
        :buffer="200"
      >
        <VideoList
          id="video-list"
          :items="items?.data"
        />
      </InfiniteScroll>
    </template>
  </UDashboardPanel>
</template>
