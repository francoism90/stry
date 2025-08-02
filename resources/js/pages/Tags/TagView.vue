<script setup lang="ts">
import { edit } from '@/actions/App/Web/Tags/Controllers/TagController'
import Page from '@/components/Ui/Page.vue'
import PageBody from '@/components/Ui/PageBody.vue'
import PageFeature from '@/components/Ui/PageFeature.vue'
import type { Playlist, Tag } from '@/types'
import { Deferred, Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  tag: Tag
  playlist: Playlist | null
  time?: number | null
  queue?: Tag[] | null
}

const props = defineProps<Props>()

const items = ref<NavigationMenuItem[][]>([
  [
    {
      label: '0',
      icon: 'i-lucide-thumbs-up',
      to: '/search',
    },
    {
      label: 'Edit',
      icon: 'i-lucide-clipboard-pen',
      to: edit.url(props.tag.id),
    },
    {
      label: 'Save',
      icon: 'i-lucide-bookmark',
      to: '/lists',
    },
  ],
])

useEcho<Tag>(`tags.${props.tag.id}`, '.tag.updated', () => router.reload({ only: ['tag'] }))
</script>

<template>
  <Head :title="tag.name" />

  <Page>
    <PageBody>
      <div class="flex flex-col gap-2 py-4">
        <PageFeature :title="tag.name" />

        <UNavigationMenu
          :items="items"
          :ui="{
            root: 'size-full items-center overflow-x-auto',
            list: 'inline-flex size-full items-center gap-2',
            link: 'rounded-full bg-neutral-800/40',
            linkLeadingIcon: 'size-3.5',
            linkLabel: 'text-xs text-neutral-400',
          }"
        />
      </div>

      <Deferred :data="['queue']">
        <template #fallback>
          <div class="sr-only">Loading sections...</div>
        </template>

        <!-- <TagCarousel
          label="Up Next"
          :items="queue"
          :actions="[{ label: 'Show All', href: '/', trailingIcon: 'i-lucide-chevron-right' }]"
        /> -->
      </Deferred>
    </PageBody>
  </Page>
</template>
