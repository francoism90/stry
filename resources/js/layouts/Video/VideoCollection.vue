<script setup lang="ts">
import VideoCard from '@/components/Video/VideoCard.vue'
import type { VideoCollection } from '@/types'
import { InfiniteScroll } from '@inertiajs/vue3'
import type { SelectItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'
import { computed } from 'vue'

interface Props {
  items: VideoCollection
  search?: string | undefined
  filter?: string | undefined
  filters?: SelectItem[] | undefined
}

const props = defineProps<Props>()

const form = useForm('get', '', {
  filter: props.filter || '',
  search: props.search || '',
})

const onReset = () => {
  form.defaults({
    filter: undefined,
    search: undefined,
  })

  form.resetAndClearErrors()
}

const onSubmit = () => {
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'filter', 'search'],
    reset: ['items'],
  })
}

const resetable = computed(() => form.search?.length || form.filter?.length)

watchDebounced(
  () => form.search,
  () => onSubmit(),
  { debounce: 300, maxWait: 1000 },
)
</script>

<template>
  <UPage>
    <slot />

    <UPageBody orientation="horizontal">
      <template #right>
        <UForm
          class="flex items-center justify-end gap-2 py-4"
          :state="form"
          @submit="onSubmit"
        >
          <UButton
            v-if="resetable"
            variant="outline"
            title="Reset filters"
            icon="i-lucide-delete"
            size="sm"
            class="px-2"
            @click="onReset"
          />

          <UInput
            v-model="form.search"
            class="w-52 sm:w-64"
            placeholder="Search..."
          />

          <USelect
            v-if="filters?.length"
            v-model="form.filter"
            value-key="value"
            :items="filters"
            placeholder="Filter by"
            class="w-32 sm:w-36"
            @update:modelValue="onSubmit"
          />
        </UForm>
      </template>
    </UPageBody>

    <InfiniteScroll
      data="items"
      items-element="#page-grid"
      :buffer="200"
    >
      <UPageGrid
        id="page-grid"
        class="gap-x-4 gap-y-6 py-2"
      >
        <VideoCard
          v-for="item in items.data"
          :key="item.id"
          :item
        />
      </UPageGrid>
    </InfiniteScroll>
  </UPage>
</template>
