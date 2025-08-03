<script lang="ts" setup>
import { edit } from '@/actions/App/Web/Tags/Controllers/TagController'
import Page from '@/components/Ui/Page.vue'
import PageBody from '@/components/Ui/PageBody.vue'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageNavigation from '@/components/Ui/PageNavigation.vue'
import type { Tag } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  tag: Tag
}

const props = defineProps<Props>()

const items = ref<NavigationMenuItem[][]>([
  [
    {
      label: 'General',
      to: edit.url({ tag: props.tag.id }),
    },
  ],
])
</script>

<template>
  <Head :title="tag.name" />

  <Page>
    <PageBody>
      <PageFeature :title="tag.name" />
      <PageNavigation :items />
      <slot />
    </PageBody>
  </Page>
</template>
