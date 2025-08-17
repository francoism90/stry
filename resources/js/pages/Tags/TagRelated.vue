<script setup lang="ts">
import TagRelatedModal from '@/components/Tag/TagRelatedModal.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import TagResource from '@/layouts/Tag/TagResource.vue'
import type { Tag, Tags } from '@/types'
import { Deferred } from '@inertiajs/vue3'
import type { TableColumn } from '@nuxt/ui'

interface Props {
  tag: Tag
  items?: Tags
  types: string[]
}

defineOptions({ layout: [DefaultLayout, TagResource] })
defineProps<Props>()

const columns: TableColumn<Tag>[] = [
  {
    accessorKey: 'name',
    header: 'Name',
  },
  {
    accessorKey: 'category',
    header: 'Type',
  },
]
</script>

<template>
  <PageSection>
    <Deferred data="items">
      <template #fallback>
        <div class="sr-only">Loading items...</div>
      </template>

      <div class="self-end pt-6">
        <TagRelatedModal :item="tag" />
      </div>

      <UTable
        v-if="items?.data?.length"
        :data="items.data"
        :columns="columns"
        class="flex-1"
        sticky
      />
    </Deferred>
  </PageSection>
</template>
