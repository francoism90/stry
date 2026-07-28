<script setup lang="ts">
defineProps<{
  sort?: string
  title?: string
}>()
</script>

<template>
  <div>{{ sort }}</div>
</template>
