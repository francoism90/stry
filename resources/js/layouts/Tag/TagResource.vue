<script lang="ts" setup>
import { edit, show } from '@/actions/App/Web/Tags/Controllers/TagController'
import PageActions from '@/components/Ui/PageActions.vue'
import PageColumns from '@/components/Ui/PageColumns.vue'
import PageDetails from '@/components/Ui/PageDetails.vue'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageNavigation from '@/components/Ui/PageNavigation.vue'
import type { Tag } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useEcho } from '@laravel/echo-vue'
import type { NavigationMenuItem } from '@nuxt/ui'
import { computed, ref } from 'vue'

interface Props {
  tag: Tag
}

const props = defineProps<Props>()

const actions = ref<NavigationMenuItem[]>([{ label: 'View', icon: 'i-lucide-file', to: show.url(props.tag.id) }])

const tabs = ref<NavigationMenuItem[]>([{ label: 'General', to: edit.url(props.tag.id) }])

const details = computed<NavigationMenuItem[]>(() => [
  { label: 'Category', value: props.tag.category ?? 'N/A' },
  { label: 'Videos', value: props.tag.videos?.toFixed() + ' videos' },
])

useEcho<Tag>(`tags.${props.tag.id}`, '.tag.updated', () => router.reload())
</script>

<template>
  <Head :title="tag.name" />

  <UPageBody>
    <PageColumns>
      <template #left>
        <PageFeature :title="tag.name" />
        <PageDetails :details />
      </template>

      <template #right>
        <PageActions :actions />
      </template>
    </PageColumns>

    <PageNavigation :tabs />

    <slot />
  </UPageBody>
</template>
