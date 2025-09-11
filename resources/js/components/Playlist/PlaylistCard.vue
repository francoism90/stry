<script setup lang="ts">
import type { Playlist } from '@/types'

interface Props {
  item: Playlist
}

defineProps<Props>()
</script>

<template>
  <UCard variant="soft">
    <template #header>
      <h2 class="text-sm capitalize">{{ item.type }}</h2>
    </template>

    <dl class="details text-xs font-light tracking-tight text-neutral-300">
      <dt class="sr-only">Percentage</dt>
      <dd>{{ item.percent?.toFixed(2) ?? '0.00' }}%</dd>
      <dt class="sr-only">State</dt>
      <dd>{{ item.state ?? 'N/A' }}</dd>
      <dt class="sr-only">Expires</dt>
      <dd>{{ item.expires_at ?? 'N/A' }}</dd>
    </dl>
  </UCard>
</template>
