<script setup lang="ts">
import VideoItems from '@/components/Library/VideoItems.vue'
import type { VideoCollection } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { SelectMenuItem, TabsItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: VideoCollection
  search: string | null
  sort: string | null
  sorters: SelectMenuItem[]
}>()

const tabs: TabsItem[] = [
  {
    label: 'Videos',
  },
  {
    label: 'Tags',
  },
  {
    label: 'Collections',
  },
]

const form = useForm('get', '', {
  search: props.search,
  sort: props.sort,
  page: 1,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
  })
</script>

<template>
  <Head title="Home" />

  <UPage>
    <UTabs
      :items="tabs"
      variant="link"
      :ui="{
        trigger: 'grow py-2',
      }"
    />

    <UForm
      id="general"
      :state="form"
      class="flex items-center gap-2 px-4 py-4 sm:px-6"
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
          placeholder="Search videos..."
          size="lg"
        />
      </UFormField>

      <UFormField
        :error="form.errors.sort"
        class="flex-none"
      >
        <USelect
          v-if="sorters.length"
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

    <VideoItems :items="items" />
  </UPage>
</template>
