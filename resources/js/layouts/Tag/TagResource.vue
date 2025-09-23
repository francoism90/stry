<script lang="ts" setup>
import { edit, show } from '@/actions/App/Web/Tags/Controllers/TagController'
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

const links = ref<NavigationMenuItem[]>([{ label: 'View', icon: 'i-lucide-file', to: show.url(props.tag.id) }])
const tabs = ref<NavigationMenuItem[]>([{ label: 'General', to: edit.url(props.tag.id) }])

const details = computed(() => [props.tag.category, props.tag.videos + ' videos'].filter(Boolean).join(' • '))

useEcho<Tag>(`tags.${props.tag.id}`, '.tag.updated', () => router.reload())
</script>

<template>
  <Head :title="tag.name" />

  <UPageBody>
    <UPageHeader
      :ui="{ root: 'border-0 py-0 text-sm tracking-tight text-neutral-300', title: 'text-sm sm:text-xl', description: 'mt-0 text-sm' }"
      :title="tag.name"
      :description="details"
      :links="links"
    />

    <PageNavigation :tabs />

    <slot />
  </UPageBody>
</template>
