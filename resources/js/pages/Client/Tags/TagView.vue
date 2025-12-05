<script setup lang="ts">
import { edit } from '@/actions/App/Admin/Tags/Controllers/TagController'
import VideoList from '@/components/Videos/VideoList.vue'
import type { Tag, VideoCollection } from '@/types'
import { Head, InfiniteScroll } from '@inertiajs/vue3'
import type { ButtonProps, SelectMenuItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'
import { ref } from 'vue'

const props = defineProps<{
  tag: Tag
  items: VideoCollection
  search: string | null
  sort: string | null
  sorters: SelectMenuItem[]
}>()

const form = useForm('get', '', {
  search: props.search,
  sort: props.sort,
  page: 1,
})

const onSubmit = () => {
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'search', 'sort'],
    reset: ['items'],
  })
}

const links = ref<ButtonProps[]>([
  {
    label: 'Edit',
    icon: 'i-lucide-clipboard-pen',
    to: edit.url(props.tag.id),
  },
])

watchDebounced(
  () => form.search,
  () => onSubmit(),
  { debounce: 300, maxWait: 1000 },
)
</script>

<template>
  <Head :title="tag.name" />

  <UPage>
    <UPageHeader
      :title="tag.name"
      :links="links"
      :ui="{
        root: 'px-4 py-4 sm:px-6',
        title: 'font-serif text-2xl font-semibold sm:text-3xl',
        description: 'text-base',
      }"
    >
      <template #description>
        <p
          v-if="tag.summary?.length"
          v-html="tag.summary"
        />
      </template>
    </UPageHeader>

    <UPageBody class="mt-4 space-y-6 px-4 sm:px-6">
      <UForm
        id="general"
        :state="form"
        class="flex items-center gap-2"
        loading-auto
        @submit="onSubmit"
      >
        <UFormField
          :error="form.errors.search"
          class="flex-1"
        >
          <UInput
            v-model="form.search"
            :model-modifiers="{ nullable: true, string: true, trim: true }"
            placeholder="Filter videos..."
            size="lg"
          />
        </UFormField>

        <UFormField
          :error="form.errors.sort"
          class="flex-none"
        >
          <USelect
            v-model="form.sort"
            :items="sorters"
            label-key="label"
            value-key="value"
            placeholder="Filter by"
            variant="soft"
            size="lg"
            @update:modelValue="onSubmit"
          />
        </UFormField>
      </UForm>

      <InfiniteScroll data="items">
        <VideoList :items="items" />
      </InfiniteScroll>
    </UPageBody>
  </UPage>
</template>
