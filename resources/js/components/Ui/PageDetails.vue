<script setup lang="ts">
import type { DetailListItem } from '@/types'

interface Props {
  items: DetailListItem[] | undefined
}

defineProps<Props>()
</script>

<template>
  <dl
    v-if="items?.length"
    class="details text-sm tracking-tight text-neutral-300"
  >
    <template
      v-for="(item, index) in items"
      :key="index"
    >
      <dt class="sr-only">{{ item.label }}</dt>
      <dd>{{ item.value }}</dd>
    </template>
  </dl>
</template>
