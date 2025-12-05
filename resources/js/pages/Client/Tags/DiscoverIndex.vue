<script setup lang="ts">
import TagList from '@/components/Tags/TagList.vue'
import type { TagCollection } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { watchDebounced } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  items: TagCollection
  search: string | null
  type: string | null
  types: SelectMenuItem[]
}>()

const form = useForm('get', '', {
  search: props.search,
  type: props.type,
  page: 1,
})

const onSubmit = () => {
  form.submit({
    preserveState: true,
    replace: true,
    only: ['items', 'search', 'type'],
    reset: ['items'],
  })
}

watchDebounced(
  () => form.search,
  () => onSubmit(),
  { debounce: 100, maxWait: 1000 },
)
</script>

<template>
  <Head title="Discover" />

  <UPage>
    <UPageBody class="mt-4 space-y-6">
      <UForm
        id="general"
        :state="form"
        class="flex items-center gap-2 px-4 sm:px-6"
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
            placeholder="Search tags..."
            size="lg"
          />
        </UFormField>

        <UFormField
          :error="form.errors.type"
          class="flex-none"
        >
          <USelect
            v-model="form.type"
            :items="types"
            label-key="label"
            value-key="value"
            placeholder="Filter by"
            variant="soft"
            size="lg"
            @update:modelValue="onSubmit"
          />
        </UFormField>
      </UForm>

      <TagList :items="items" />
    </UPageBody>
  </UPage>
</template>
