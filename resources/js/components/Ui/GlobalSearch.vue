<script setup lang="ts">
import { index as videoIndex } from '@/actions/App/Web/Videos/Controllers/VideoController'
import { useSearch } from '@/composables/search'
import { useForm, usePage } from '@inertiajs/vue3'
import { computed, useTemplateRef, watch } from 'vue'

const searchTargets = {
  'Videos/VideoIndex': { placeholder: 'Search videos' },
  'Videos/VideoView': { placeholder: 'Search videos', route: videoIndex['/'].url() },
  'Tags/TagIndex': { placeholder: 'Search tags' },
  'Tags/TagView': { placeholder: 'Search videos' },
  'Groups/GroupIndex': { placeholder: 'Search collections' },
  'Groups/GroupView': { placeholder: 'Search videos' },
  'Transcodes/TranscodeIndex': { placeholder: 'Search transcodes' },
  'Users/UserIndex': { placeholder: 'Search users' },
} as const

const { search } = useSearch()
const page = usePage()
const input = useTemplateRef('input')

const target = computed(() => searchTargets[page.component as keyof typeof searchTargets] ?? null)

const form = useForm({
  query: search.value ?? '',
})

// Layouts persist across visits, so re-sync the query whenever the
// page-provided search changes instead of only on first mount.
watch(search, (value) => {
  form.query = value ?? ''
})

const onSearch = () => {
  if (!target.value) {
    return
  }

  form.get('route' in target.value ? target.value.route : '', {
    preserveState: true,
  })
}

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
      v-model="form.query"
      :model-modifiers="{ string: true, trim: true }"
      icon="i-lucide-search"
      :placeholder="target.placeholder"
      variant="soft"
      size="lg"
      @keydown.enter="onSearch"
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
