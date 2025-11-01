<script setup lang="ts">
import { edit, show } from '@/actions/App/Web/Videos/Controllers/VideoController'
import type { Video } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { NavigationMenuItem, TabsItem } from '@nuxt/ui'
import { formatTimeAgoIntl } from '@vueuse/core'
import { computed, ref } from 'vue'

interface Props {
  video: Video
}

const props = defineProps<Props>()

const links = ref<NavigationMenuItem[]>([
  {
    label: 'View',
    icon: 'i-lucide-eye',
    to: show.url(props.video.id),
  },
])

const items = ref<TabsItem[]>([
  {
    label: 'General',
    to: edit.url(props.video.id),
  },
  {
    label: 'Media',
  },
])

const updatedAt = computed(() => formatTimeAgoIntl(new Date(props.video.updated_at)))
</script>

<template>
  <Head :title="video.title" />

  <UPage>
    <UPageBody>
      <UContainer>
        <UPageHeader
          :title="video.title"
          :description="`Updated ${updatedAt}`"
          :links="links"
          :ui="{ description: 'mt-2 text-sm' }"
        />
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
