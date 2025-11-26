<script setup lang="ts">
import { edit, show } from '@/actions/App/Web/Videos/Controllers/VideoController'
import { index } from '@/actions/App/Web/Videos/Controllers/VideoMediaController'
import { useVideo } from '@/composables/video'
import { Head } from '@inertiajs/vue3'
import type { ButtonProps, TabsItem } from '@nuxt/ui'
import { ref } from 'vue'

const { state, created, updated } = useVideo()

const links = ref<ButtonProps[]>([
  {
    label: 'View',
    icon: 'i-lucide-eye',
    to: show.url(state.value.id),
  },
])

const items = ref<TabsItem[]>([
  {
    label: 'General',
    to: edit.url(state.value.id),
  },
  {
    label: 'Media',
    to: index.url({ video: state.value.id }),
  },
])
</script>

<template>
  <Head :title="state.title" />

  <UPage>
    <UPageBody>
      <UContainer>
        <UPageHeader
          :title="state.title"
          :links="links"
          :ui="{ title: 'text-lg font-bold sm:text-xl', description: 'mt-0 text-sm' }"
        >
          <template #description>
            <span>{{ state.user?.name }} · Created {{ created }} · Updated {{ updated }}</span>
          </template>
        </UPageHeader>
      </UContainer>

      <UContainer>
        <UTabs
          variant="link"
          highlight
          highlight-color="primary"
          :content="false"
          :items="items"
          class="w-full"
        />

        <slot />
      </UContainer>
    </UPageBody>
  </UPage>
</template>
