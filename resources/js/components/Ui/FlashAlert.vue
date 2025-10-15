<script setup lang="ts">
import { useFlash } from '@/composables/flash'

const { title, color } = useFlash()
</script>

<template>
  <UAlert
    v-if="title"
    :title="title as string"
    :color="color"
    :ui="{ root: 'sr-only' }"
  />
</template>
