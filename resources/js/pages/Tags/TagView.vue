<script setup lang="ts">
import PageFilters from '@/components/Ui/PageFilters.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import VideoCollection from '@/layouts/Video/VideoCollection.vue'
import type { Tag } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { RadioGroupItem } from '@nuxt/ui'

interface Props {
  tag: Tag
  type: string | undefined
  types: RadioGroupItem[]
}

defineOptions({ layout: [DefaultLayout, VideoCollection] })

const props = defineProps<Props>()

useEcho<Tag>(`tags.${props.tag.id}`, '.tag.updated', () => router.reload({ only: ['tag'] }))
</script>

<template>
  <Head :title="tag.name" />

  <PageFilters
    :title="tag.name"
    headline="Tags"
    :types="types"
    :type="type"
  />
</template>
