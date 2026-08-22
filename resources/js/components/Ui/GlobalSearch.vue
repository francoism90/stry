<script setup lang="ts">
import { QueryInjectionKey } from '@/composables/query'
import { home } from '@/routes'
import { router, usePage } from '@inertiajs/vue3'
import { computed, inject, useTemplateRef } from 'vue'

const searchTargets = {
  'Videos/VideoIndex': { placeholder: 'Search videos' },
  'Videos/VideoLibrary': { placeholder: 'Search videos' },
  'Videos/VideoView': { placeholder: 'Search videos', route: home.url() },
  'Tags/TagIndex': { placeholder: 'Search tags' },
  'Tags/TagView': { placeholder: 'Search videos' },
  'Groups/GroupIndex': { placeholder: 'Search collections' },
  'Groups/GroupView': { placeholder: 'Search videos' },
  'Transcodes/TranscodeIndex': { placeholder: 'Search transcodes' },
  'Users/UserIndex': { placeholder: 'Search users' },
} as const

const page = usePage()
const input = useTemplateRef('input')
const query = inject(QueryInjectionKey)!

const target = computed(() => searchTargets[page.component as keyof typeof searchTargets] ?? null)

const searchText = computed({
  get: () => (query.form.query ?? '').toString(),
  set: (value: string) => {
    query.form.query = value
  },
})

const onSearch = () => {
  if (!target.value) {
    return
  }

  // Leaving to a different resource (e.g. a group's videos) drops the
  // current filter/sort scope instead of carrying it into the new context.
  if ('route' in target.value) {
    router.get(target.value.route, { query: query.form.query }, { preserveState: true })
    return
  }

  query.onSubmit()
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
      v-model="searchText"
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
