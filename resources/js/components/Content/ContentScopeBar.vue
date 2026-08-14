<script setup lang="ts">
import { useQuery } from '@/composables/query'
import type { QueryValue } from '@/types'
import type { TabsItem } from '@nuxt/ui'

const props = defineProps<{
  active?: QueryValue
  items?: TabsItem[]
  title?: string
}>()

const { form, onSubmit } = useQuery({
  filters: () => props.items,
  filter: () => props.active,
})
</script>

<template>
  <UDashboardNavbar :title="title">
    <template #right>
      <UTabs
        default-value="all"
        :items="items"
        :content="false"
        label-key="label"
        value-key="value"
        size="sm"
      />
    </template>
  </UDashboardNavbar>
</template>
