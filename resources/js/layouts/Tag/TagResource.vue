<script lang="ts" setup>
import { edit, show } from '@/actions/App/Web/Tags/Controllers/TagController'
import PageActions from '@/components/Ui/PageActions.vue'
import PageBody from '@/components/Ui/PageBody.vue'
import PageColumns from '@/components/Ui/PageColumns.vue'
import PageDetails from '@/components/Ui/PageDetails.vue'
import PageFeature from '@/components/Ui/PageFeature.vue'
import PageNavigation from '@/components/Ui/PageNavigation.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import type { Tag } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { ref } from 'vue'

interface Props {
  tag: Tag
}

const props = defineProps<Props>()

const details = ref<NavigationMenuItem[]>([
  { label: 'Type', value: props.tag.category },
  { label: 'Videos', value: props.tag.videos + ' videos' },
])

const actions = ref<NavigationMenuItem[]>([{ label: 'View', icon: 'i-lucide-file', to: show.url(props.tag.id) }])
const tabs = ref<NavigationMenuItem[]>([{ label: 'General', to: edit.url(props.tag.id) }])
</script>

<template>
  <Head :title="tag.name" />

  <PageBody>
    <PageSection>
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
    </PageSection>

    <slot />
  </PageBody>
</template>
