<script setup lang="ts">
import { useSearch } from '@/composables/search'
import { index as collectionsIndex } from '@/routes/collections'
import { index as tagsIndex } from '@/routes/tags'
import { index as videosIndex } from '@/routes/videos'
import { router, useForm, usePage } from '@inertiajs/vue3'
import { watchDebounced } from '@vueuse/core'
import { computed, useTemplateRef } from 'vue'

const searchTargets = {
  'Videos/VideoIndex': { route: videosIndex, placeholder: 'Search videos' },
  'Tags/TagIndex': { route: tagsIndex, placeholder: 'Search tags' },
  'Groups/GroupIndex': { route: collectionsIndex, placeholder: 'Search collections' },
} as const

const { search } = useSearch()
const page = usePage()
const input = useTemplateRef('input')

const target = computed(() => searchTargets[page.component as keyof typeof searchTargets] ?? null)

const form = useForm({
  search: search.value ?? '',
})

watchDebounced(
  () => form.search,
  (value) => {
    if (!target.value) {
      return
    }

    router.visit(target.value.route.url({ query: { query: value } }), {
      preserveState: true,
    })
  },
  { debounce: 350, maxWait: 1000 },
)

defineShortcuts({
  '/': () => {
    input.value?.inputRef?.focus()
  },
})
</script>

<template>
  <div
    v-if="target"
    class="flex items-center gap-1"
  >
    <UInput
      ref="input"
      v-model="form.search"
      :model-modifiers="{ string: true, trim: true }"
      icon="i-lucide-search"
      :placeholder="target.placeholder"
      variant="soft"
      size="lg"
      :ui="{
        root: 'w-full max-w-fit md:min-w-sm lg:min-w-lg',
        base: 'rounded-full',
        trailing: 'hidden md:inline-flex',
      }"
    >
      <template #trailing>
        <UKbd value="/" />
      </template>
    </UInput>

    <UButton
      size="sm"
      icon="i-lucide-dices"
      color="neutral"
      variant="link"
      class="hidden md:inline-flex"
    />
  </div>
</template>
