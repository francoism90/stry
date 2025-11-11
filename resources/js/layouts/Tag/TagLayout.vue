<script setup lang="ts">
import { edit, show } from '@/actions/App/Web/Tags/Controllers/TagController'
import type { Tag } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { ButtonProps, TabsItem } from '@nuxt/ui'
import { formatDate, formatTimeAgoIntl } from '@vueuse/core'
import { computed, ref } from 'vue'

interface Props {
  tag: Tag
}

const props = defineProps<Props>()

const links = ref<ButtonProps[]>([
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

const createdAt = computed(() => formatDate(new Date(props.tag.created_at), 'YYYY-MM-DD'))
const updatedAt = computed(() => formatTimeAgoIntl(new Date(props.tag.updated_at)))
</script>

<template>
  <Head :title="tag.name" />

  <UPage>
    <UPageBody>
      <UContainer>
        <UPageHeader
          :title="tag.name"
          :links="links"
          :ui="{ title: 'text-lg font-bold sm:text-xl', description: 'mt-0 text-sm' }"
        >
          <template #description>
            <span>Created {{ createdAt }} · Updated {{ updatedAt }}</span>
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
