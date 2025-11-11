<script setup lang="ts">
import { edit } from '@/actions/App/Web/Tags/Controllers/TagController'
import DashboardToolbar from '@/components/Dashboard/DashboardToolbar.vue'
import VideoItems from '@/components/Video/VideoItems.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Tag, VideoCollection } from '@/types'
import { Head, InfiniteScroll, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { ButtonProps } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  tag: Tag
  items: VideoCollection
}

defineOptions({ layout: DashboardLayout })

const props = defineProps<Props>()

const links = ref<ButtonProps[]>([
  {
    label: 'Edit',
    icon: 'i-lucide-clipboard-pen',
    to: edit.url(props.tag.id),
  },
])

useEcho<Tag>(`tags.${props.tag.id}`, '.tag.updated', () => router.reload({ only: ['tag'] }))
</script>

<template>
  <Head :title="tag.name" />

  <UPage>
    <UPageBody>
      <UContainer>
        <UPageHeader
          :title="tag.name"
          :links="links"
        >
          <template #description>
            <div v-html="tag.summary" />
          </template>
        </UPageHeader>
      </UContainer>

      <DashboardToolbar default-filter="recommended" />

      <UContainer class="py-4">
        <InfiniteScroll
          items-element="#item-list"
          data="items"
          :buffer="200"
        >
          <VideoItems :items="items" />
        </InfiniteScroll>
      </UContainer>
    </UPageBody>
  </UPage>
</template>
