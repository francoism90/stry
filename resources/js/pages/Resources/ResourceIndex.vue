<script setup lang="ts">
defineProps<{
  filter?: string
  sort?: string
  type: string
}>()
</script>

<template>
  <div>{{ sort }}</div>
</template>
