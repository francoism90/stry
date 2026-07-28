<script setup lang="ts">
import FilterBar from '@/components/Ui/FilterBar.vue'

defineProps<{
  filter?: string
  sort?: string
  type: string
}>()
</script>

<template>
  <div>
    <FilterBar />
  </div>
</template>
