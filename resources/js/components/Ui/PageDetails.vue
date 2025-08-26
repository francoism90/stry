<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

interface Props {
  details?: NavigationMenuItem[]
}

defineProps<Props>()
</script>

<template>
  <dl
    v-if="details?.length"
    class="details text-sm tracking-tight text-neutral-300"
  >
    <template
      v-for="(item, index) in details"
      :key="index"
    >
      <dt class="sr-only">{{ item.label }}</dt>
      <dd>{{ item.value }}</dd>
    </template>
  </dl>
</template>
