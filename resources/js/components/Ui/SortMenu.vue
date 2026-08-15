<script setup lang="ts">
import type { QueryValue } from '@/types'
import type { SelectItem } from '@nuxt/ui'

defineProps<{
  items?: SelectItem[]
  active?: QueryValue
}>()
</script>

<template>
  <USelect
    :items="items"
    :model-value="active"
    class="w-36"
    placeholder="Sort by"
  />
</template>
