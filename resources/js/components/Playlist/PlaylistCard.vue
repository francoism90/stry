<script setup lang="ts">
import type { Playlist } from '@/types'
import type { NavigationMenuItem } from '@nuxt/ui'
import { computed } from 'vue'
import PageDetails from '../Ui/PageDetails.vue'

interface Props {
  item: Playlist
}

const props = defineProps<Props>()

const details = computed<NavigationMenuItem[]>(() => [
  { label: 'Percentage', value: props.item.percent?.toFixed(2) ?? '0.00' },
  { label: 'State', value: props.item.state ?? 'N/A' },
  { label: 'Expires At', value: props.item.expires_at ?? 'N/A' },
])
</script>

<template>
  <UCard variant="soft">
    <template #header>
      <h2 class="text-sm text-muted capitalize">{{ item.type }}</h2>
    </template>

    <PageDetails :details />
  </UCard>
</template>
