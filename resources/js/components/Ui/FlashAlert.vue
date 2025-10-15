<script setup lang="ts">
import { useFlash } from '@/composables/flash'

const { title, color } = useFlash()
</script>

<template>
  <div
    v-if="title"
    class="sr-only"
  >
    <UAlert
      :title="title as string"
      :color="color"
    />
  </div>
</template>
