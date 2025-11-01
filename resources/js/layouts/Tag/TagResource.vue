<script setup lang="ts">
import { edit, show } from '@/actions/App/Web/Tags/Controllers/TagController'
import type { Tag } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { NavigationMenuItem, TabsItem } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  tag: Tag
}

const props = defineProps<Props>()

const links = ref<NavigationMenuItem[]>([
  {
    label: 'View',
    icon: 'i-lucide-eye',
    to: show.url(props.tag.id),
  },
])

const items = ref<TabsItem[]>([
  {
    label: 'General',
    to: edit.url(props.tag.id),
  },
  {
    label: 'Media',
  },
])
</script>

<template>
  <Head :title="tag.name" />

  <UPage>
    <UPageBody>
      <UContainer>
        <UPageHeader
          :title="tag.name"
          :links="links"
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
